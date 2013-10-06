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
  
  const REL_CHILD = 0x01;
  const REL_PARENT = 0x02;
  const REL_ANY = 0x03;
  
  const SORT_NATURAL = 0x00;
  const SORT_ASCENDING = 0x02;
  const SORT_DESCENDING = 0x03;
  const SORT_RANDOM = 0x04;
  /**
   * Create new HAMLE Parser
   * 
   * @param hamleModel $baseModel
   * @param hamleSetup $setup
   * @throws hamleEx_Unsupported
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
    hamleScope::add($baseModel);
  }
  
  /**
   * Return HTML Output from HAMLE string
   * @param string $s HAMLE Template in string form
   * @return string HTML Code
   */
  function outputStr($s) {
    $out = "";
    self::$me = $this;
    $dir = $this->setup->getCacheDir();
    $this->parse->str($s);
    $snips = $this->setup->getSnippets();
    foreach($snips as $snip) {
      $this->parse->parseSnip(file_get_contents($snip));
    }
    file_put_contents("$dir/string.hamle.php", $this->parse->output());
    return $this->output("$dir/string.hamle.php");
  }
  
  /**
   * Return HTML Output from HAMLE File
   * @see hamleSetup
   * @param string $f Path to HAMLE File (Excluding 'base path')
   * @return string HTML Code
   * @throws hamleEx
   */
  function outputFile($f) {
    self::$me = $this;
    $inFile = $this->setup->themePath($f);
    $cacheDir = $this->setup->getCacheDir();
    $tpl = file_get_contents($inFile);
    if(!$tpl) throw new hamleEx("Unable to open file [$inFile]");
    $outFile = $cacheDir."/".str_replace("/","-",$f).".php";
    $this->parse->str($tpl);
    file_put_contents($outFile, $this->parse->output());
    return $this->output($outFile);
  }
  
  /**
   * Capture output from compiled HAMLE Template
   * @param string $f File Patch of compiled template
   * @return string HTML Output
   * @throws hamleEx
   */
  protected function output($f) {
    try {
      ob_start();
      hamleRun::addInstance($this);
      require $f;
      $out = ob_get_contents();
      ob_end_clean();
    } catch (hamleEx $e) {
      ob_end_clean();
      throw $e;
    }
    hamleRun::popInstance();
    return $out;
  }
  static function getLineNo() {
    if(!isset(self::$me))
      return 0;
    return self::$me->parse->getLineNo();
  }
  
}
