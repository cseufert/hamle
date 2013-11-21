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
    return self::$hamle->load($path)->output();
  }
  
  /**
   * Called from template by $() to find a specific model
   * @param array $typeTags array of tags with types as key eg ['page'=>[]] or ['product'=>['featured]]
   * @param int $sortDir Sort Direction see hamle::SORT_NATURAL...
   * @param string $sortBy Field name to sort by
   * @param int $limit Limit results to this number
   * @param int $offset
   * @return hamleModel
   */  
  static function modelTypeTags($typeTags, $sortDir = 0, $sortField = "", $limit = 0, $offset = 0) {
    return self::$hamle->setup->getModelTypeTags($typeTags,
                                  $sortDir, $sortField, $limit, $offset);
  }
  /**
   * Called from template by $() to find a specific model
   * @param string $id id to search for
   * @return hamleModel
   */  
  static function modelId($id, $sortDir = 0, $sortField = "", $limit = 0, $offset = 0) {
    $o = self::$hamle->setup->getModelDefault($id,
                                  $sortDir, $sortField, $limit, $offset);
    if(!$o instanceOf hamleModel) throw new hamleEx_RunTime("Application must return instance of hamleModel");
    return $o;
  }

  /**
   * Called from template by $() to find a specific model
   * @param string $type type to filter by
   * @param string $id id to search for
   * @return hamleModel
   */  
  static function modelTypeID($typeId, $sortDir = 0, $sortField = "", $limit = 0, $offset = 0) {
    return self::$hamle->setup->getModelTypeId($typeId, 
                                $sortDir, $sortField, $limit, $offset);
  }
  
  
}
