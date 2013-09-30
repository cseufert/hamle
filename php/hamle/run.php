<?php
/**
 * HAMLE Runtime
 *
 * @author Chris Seufert <chris@seufert.id.au>
 */

class hamleRun {
  /**
   * @var hamle Current HAMLE instance 
   */
  static protected $hamle;
  static protected $hamleList = array();
  
  static function addInstance(hamle $hamle) {
    self::$hamleList[] = $hamle;
    self::$hamle = $hamle;
  }
  
  static function popInstance() {
    array_pop(self::$hamleList);
    if(self::$hamleList)
      self::$hamle = self::$hamleList[count(self::$hamleList) - 1];
    else
      self::$hamle = NULL;
  }
  
  
  static function filter($name, $data) {
    return strrev($data);
  }
  
  /**
   * Helper for hamle |include command
   * @param string $path Path to file to include
   * @return string HTML Code
   */
  static function includeFile($path) {
    return self::$hamle->outputFile($path);
  }
  
  /**
   * Called from template by $() to find a specific model
   * @param string $type type to filter by
   * @return hamleModel
   */  
  static function modelType($type) {
    return self::$hamle->setup->getNamedModel($type);
  }
  /**
   * Called from template by $() to find a specific model
   * @param string $id id to search for
   * @return hamleModel
   */  
  static function modelID($id) {
    return self::$hamle->setup->getDefaultModel ($id);
  }

  /**
   * Called from template by $() to find a specific model
   * @param string $type type to filter by
   * @param string $id id to search for
   * @return hamleModel
   */  
  static function modelTypeID($type, $id) {
    return self::$hamle->setup->getNamedModel($type, $id);
  }
  
  
}
