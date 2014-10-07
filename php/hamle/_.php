<?php
/**
 * Description of HAMLE
 *
 * @author Chris Seufert <chris@seufert.id.au>
 */
class hamle {
  /**
   * @var hamleSetup instance of hamleSetup Object
   */
  public $setup;
  /**
   * @var hamle Instance of the 'current' hamle Engine 
   */
  static protected $me;
  /**
   * @var hamleParse Parser Instance
   */
  public $parse;
  /**
   * @var string Filename for Cache file 
   */
  protected $cacheFile;
  /**
   * @var bool Enable cacheing of templates
   */
  protected $cache = true;
  /**
   * @var array Array of Files required $files[0] is the template file
   *            The rest of the files are Snippets 
   */
  protected $snipFiles;

  protected $baseModel = null;

  const REL_CHILD = 0x01;  /* Child Relation */
  const REL_PARENT = 0x02; /* Parent Relation */
  const REL_ANY = 0x03;    /* Unspecified or any relation */
  
  const SORT_NATURAL = 0x00;    /* Sort in what ever order is 'default' */
  const SORT_ASCENDING = 0x02;  /* Sort Ascending */
  const SORT_DESCENDING = 0x03; /* Sort Decending */
  const SORT_RANDOM = 0x04;     /* Sort Randomly */
  /**
   * Create new HAMLE Parser
   * 
   * @param hamleModel $baseModel
   * @param hamleSetup $setup
   * @throws hamleEx_Unsupported
   * @throws hamleEx_NotFound
   */
  function __construct($baseModel, $setup = NULL) {
    self::$me = $this;
    if(!$setup)
      $setup = new hamleSetup();
    $this->parse = new hamleParse();
    if(!$setup instanceOf hamleSetup)
      throw new hamleEx_Unsupported("Unsupported Setup Helper was passed, it must extends hamleSetup");
    if(!$baseModel instanceOf hamleModel)
      throw new hamleEx_Unsupported("Unsupported Model Type was passed, it must implement hamleModel");
    $this->setup = $setup;
    $this->baseModel = $baseModel;
    $this->cacheFile = $this->setup->cachePath("string.hamle.php");
    $this->snipFiles = $this->setup->snippetFiles();
    foreach($this->snipFiles as $f)
      if(!file_exists($f)) throw new hamleEx_NotFound("Unable to find Snippet File ($f)");
  }
  /**
   * Parse a HAMLE Template File
   * @param string $hamleFile Template File Name (will have path gathered from hamleSetup->templatePath
   * @throws hamleEx_NotFound If tempalte file cannot be found
   * @return hamle Returns instance for chaining commands
   */
  function load($hamleFile) {
    $template = $this->setup->templatePath($hamleFile);
      if(!file_exists($template)) 
        throw new hamleEx_NotFound("Unable to find HAMLE Template ($template)");
    $this->cacheFile = $this->setup->cachePath(
                  str_replace("/","-",$hamleFile).".php");
    $this->setup->debugLog("Set cache file path to ({$this->cacheFile})");
    $cacheFileAge = @filemtime($this->cacheFile);
    $cacheDirty = false;
    foreach(array_merge(array($template),$this->snipFiles) as $f)
      if((!$this->cache) || $cacheFileAge < filemtime($f))
        $cacheDirty = true;
    if($cacheDirty) {
      $this->setup->debugLog("Parsing File ($template to {$this->cacheFile})");
      $this->parse(file_get_contents($template));
    } else
      $this->setup->debugLog("Using Cached file ({$this->cacheFile})");
    return $this;
  }
  /**
   * Parse a HAMLE tempalte from a string 
   * _WARNING_ Template Sting will *NOT* be cached, it will be parsed every time
   * 
   * @param string $hamleCode Hamle Template as string
   * @throws hamleEx_ParseError if unable to write to the cache file
   */
  function parse($hamleCode) {
    $this->parse->str($hamleCode);
    foreach($this->snipFiles as $snip)
      $this->parse->parseSnip(file_get_contents($snip));
    $this->setup->debugLog("Updating Cache File ({$this->cacheFile})");
    if(FALSE === file_put_contents($this->cacheFile, $this->parse->output()))
      throw new hamleEx_ParseError(
                      "Unable to write to cache file ({$this->cacheFile})");
  }
  
  /**
   * Deprected Function, do not use
   * @deprecated since version 2013-10-03
   */
  function outputFile($f) {
    throw new hamleEx_Unsupported("Please use load($f); and output() methods rather than outputFile");
  }
  
  /**
   * Produce HTML Output from hamle Template file
   * @return string HTML Output as String
   * @throws hamleEx
   */
  function output() {
    try {
      ob_start();
      hamleRun::addInstance($this);
      $baseModel = $this->baseModel;
      $this->baseModel = null;
      if($baseModel) hamleScope::add($baseModel);
      require $this->cacheFile;
      if($baseModel) hamleScope::done();
      $this->baseModel = $baseModel;
      $out = ob_get_contents();
      ob_end_clean();
    } catch (hamleEx $e) {
      ob_end_clean();
      throw $e;
    }
    hamleRun::popInstance();
    return $out;
  }

  /**
   * Get the current line number
   * @return int The line number being passed by the parser
   */
  static function getLineNo() {
    if(!isset(self::$me))
      return 0;
    return self::$me->parse->getLineNo();
  }

  /**
   * Disable the caching of hamle templates
   */
  function disableCache() {
    $this->cache = false;
  }
  
}
