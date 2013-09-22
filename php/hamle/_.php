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
   * Create new HAMLE Parser
   * 
   * @param hamleModel $baseModel
   * @param hamleSetup $setup
   * @throws hamleEx_Unsupported
   */
  function __construct($baseModel, $setup = NULL) {
    if(!$setup)
      $setup = new hamleSetup();
    if(!$setup instanceOf hamleSetup)
      throw new hamleEx_Unsupported("Unsupported Setup Helper was passed, it must extends hamleSetup");
    $this->setup = $setup;
    hamleScope::add($baseModel);
  }
  
  function outputStr($s) {
    self::$me = $this;
    $dir = $this->setup->getCacheDir();
    file_put_contents("$dir/string.hamle.php", hamleParse::str($s));
    require "$dir/string.hamle.php";
  }
  
  function outputFile($f) {
    self::$me = $this;
    $dir = $this->setup->getCacheDir();
    $tpl = file_get_contents($f);
    file_put_contents("$dir/string.hamle.php", hamleParse::str($$tpl));
    require "$dir/string.hamle.php";
  }
  
  static function modelFind($s) {
    return self::$me->_modelFind($s);
  }
  protected function _modelFind($s) {
    $idclass = array();
    $type = hamleStr::parseIDClass($s, $idclass);
    if(!count($idclass))
      if($type)
        return $this->setup->getNamedModel($type);
      else
        throw new hamleEx("Unable to parse ($s)");
    var_dump($idclass);
    exit();
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
    throw new hamle("Unable to determine filter method for ($s)");
  }
  
  
  
}
