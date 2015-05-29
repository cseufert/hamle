<?php
/*
This project is Licenced under The MIT License (MIT)

Copyright (c) 2014 Christopher Seufert

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

 */
namespace Seufert\Hamle;
/**
 * HAMLE - HAML inspired template, with (E)nhancements
 *
 *
 * @author Chris Seufert <chris@seufert.id.au>
 */
class Hamle {
  /**
   * @var Setup instance of hamleSetup Object
   */
  public $setup;
  /**
   * @var Hamle Instance of the 'current' hamle Engine
   */
  static protected $me;
  /**
   * @var Parse Parser Instance
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

  public $baseModel;

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
   * @param Model $baseModel
   * @param Setup $setup
   * @throws Exception\Unsupported
   * @throws Exception\NotFound
   */
  function __construct($baseModel, $setup = NULL) {
    self::$me = $this;
    if(!$setup)
      $setup = new Setup();
    $this->parse = new Parse();
    if(!$setup instanceOf Setup)
      throw new Exception\Unsupported("Unsupported Setup Helper was passed, it must extends hamleSetup");
    if(!$baseModel instanceOf Model)
      throw new Exception\Unsupported("Unsupported Model Type was passed, it must implement hamleModel");
    $this->setup = $setup;
    $this->baseModel = $baseModel;
    $this->snipFiles = $this->setup->snippetFiles();
    foreach($this->snipFiles as $f)
      if(!file_exists($f)) throw new Exception\NotFound("Unable to find Snippet File ($f)");
  }
  /**
   * Parse a HAMLE Template File
   * @param string $hamleFile Template File Name (will have path gathered from hamleSetup->templatePath
   * @throws Exception\NotFound If tempalte file cannot be found
   * @return Hamle Returns instance for chaining commands
   */
  function load($hamleFile, \Closure $parseFunc = null) {
    $template = $this->setup->templatePath($hamleFile);
      if(!file_exists($template)) 
        throw new Exception\NotFound("Unable to find HAMLE Template ($template)");
    $this->cacheFile = $this->setup->cachePath(
                  str_replace("/","-",$hamleFile).".php");
    $this->setup->debugLog("Set cache file path to ({$this->cacheFile})");
    $cacheFileAge = is_file($this->cacheFile)?filemtime($this->cacheFile):0;
    $cacheDirty = false;
    foreach(array_merge(array($template),$this->snipFiles) as $f)
      if((!$this->cache) || $cacheFileAge < filemtime($f))
        $cacheDirty = true;
    if($cacheDirty) {
      $this->setup->debugLog("Parsing File ($template to {$this->cacheFile})");
      $this->parse($parseFunc?"":file_get_contents($template), $parseFunc);
    } else
      $this->setup->debugLog("Using Cached file ({$this->cacheFile})");
    return $this;
  }
  /**
   * Parse a HAMLE tempalte from a string 
   * _WARNING_ Template Sting will *NOT* be cached, it will be parsed every time
   *
   * @internal Not for general use, use string($h) instead
   * @param string $hamleCode Hamle Template as string
   * @throws Exception\ParseError if unable to write to the cache file
   */
  function parse($hamleCode, \Closure $parseFunc = null) {
    if(!$this->cacheFile)
        $this->cacheFile = $this->setup->cachePath("string.hamle.php");
    if($parseFunc)
      $parseFunc($this->parse);
    else
      $this->parse->str($hamleCode);
    $this->setup->debugLog("Loading Snippet Files");
    foreach($this->snipFiles as $snip)
      $this->parse->parseSnip(file_get_contents($snip));
    $this->setup->debugLog("Applying Snippet Files");
    $this->parse->applySnip();
    $this->setup->debugLog("Executing Parse Filters");
    foreach($this->setup->getFilters() as $filter)
      $this->parse->parseFilter($filter);
    $this->setup->debugLog("Updating Cache File ({$this->cacheFile})");
    if(FALSE === file_put_contents($this->cacheFile, $this->parse->output()))
      throw new Exception\ParseError(
                      "Unable to write to cache file ({$this->cacheFile})");
  }

  /**
   * Parse a HAMLE String, and cache output
   * @param $hamleString string Hamle
   */
  function string($hamleString) {
    $md5 = md5($hamleString);
    $stringId = substr($md5,0,12).substr($md5,24,8);
    $this->cacheFile = $this->setup->cachePath("string.$stringId.hamle.php");
    if(!is_file($this->cacheFile))
      $this->parse($hamleString);
  }

  /**
   * Produce HTML Output from hamle Template file
   * @return string HTML Output as String
   * @throws Exception
   */
  function output() {
    try {
      ob_start();
      Run::addInstance($this);
      $baseModel = $this->baseModel;
      $this->baseModel = null;
      $currentModel = $baseModel == Scope::getTopScope();
      if(!$currentModel && $baseModel) Scope::add($baseModel);
      require $this->cacheFile;
      if(!$currentModel && $baseModel) Scope::done();
      $this->baseModel = $baseModel;
      $out = ob_get_contents();
      ob_end_clean();
    } catch (\Exception $e) {
      ob_end_clean();
      throw $e;
    }
    Run::popInstance();
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

