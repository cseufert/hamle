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
  protected $setup;
  /**
   * @var hamle Instance of the 'current' hamle Engine 
   */
  static protected $me;
  /**
   * @var hamleParse Parser Instance
   */
  protected $parse;
  /**
   * Create new HAMLE Parser
   * 
   * @param hamleModel $baseModel
   * @param hamleSetup $setup
   * @throws hamleEx_Unsupported
   */
  function __construct($baseModel, $setup = NULL) {
    if(!$setup)
      $setup = new hamleSetup();
    $this->parse = new hamleParse();
    if(!$setup instanceOf hamleSetup)
      throw new hamleEx_Unsupported("Unsupported Setup Helper was passed, it must extends hamleSetup");
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
    file_put_contents("$dir/string.hamle.php", $this->parse->str($s));
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
    $f = $this->setup->themePath($f);
    $dir = $this->setup->getCacheDir();
    $tpl = file_get_contents($f);
    if(!$tpl) throw new hamleEx("Unable to open file [$f]");
    $of = $dir."/".str_replace("/","-",$f).".php";
    file_put_contents($of, $this->parse->str($tpl));
    return $this->output($of);
  }
  
  /**
   * Helper for hamle |include command
   * @param string $path Path to file to include
   * @return string HTML Code
   */
  static function includeFile($path) {
    return self::$me->outputFile($path);
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
      require $f;
      $out = ob_get_contents();
      ob_end_clean();
    } catch (hamleEx $e) {
      ob_end_clean();
      throw $e;
    }
    return $out;
  }
  
  /**
   * Called from template by $() to find a specific model
   * @param string $s name,id,class string from $() parameter
   * @return hamleModel
   */
  static function modelFind($s) {
    return self::$me->_modelFind($s);
  }
  
  /**
   * Real model find method
   * @see modelFind
   * @param string $s name,id,class string
   * @return hamleModel
   * @throws hamleEx
   */
  protected function _modelFind($s) {
    $idclass = array();
    $type = hamleStr::parseIDClass($s, $idclass);
    if(!count($idclass))
      if($type)
        return $this->setup->getNamedModel($type);
      else
        throw new hamleEx("Unable to parse ($s)");
    if(!isset($idclass['class']) && isset($idclass['id']))
      if($type)
        return $this->setup->getNamedModel($type, $idclass['id']);
      else
        return $this->setup->getDefaultModel($idclass['id']);
    if($type && isset($idclass['class']))
      if(isset($idclass['id']))
        return $this->setup->getSearchedModel($type, 
                                  $idclass['class'], $idclass['id']);
      else
        return $this->setup->getSearchedModel($type, $idclass['class']);
    throw new hamleEx("Unable to determine filter method for ($s)");
  }
    
}
