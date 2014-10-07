<?php
/**
 * HAMLE Controller/Model Scope Handler
 *
 * @author Chris Seufert <chris@seufert.id.au>
 */
class hamleScope {
  static $scopes = array();
  
  static function add($model) {
    if(!$model instanceOf hamleModel)
      throw new hamleEx_Unsupported("Unsupported Model, Needs to implement hamleModel Interface");
    self::$scopes[] = $model;
  }
  
  static function done() {
    array_pop (self::$scopes);
  }
  /**
   * Get arbitary scope
   * 0 = current
   * negative vals = back form here, -1 = last, -2 one before last, etc
   * positive vals = absolute position, 1 = first, 2 = second, etc
   * @param int $id ID of scope to get
   * @return hamleModel
   * @throws hamleEx_OutOfScope
   */
  static function get($id = 0) {
    if(0 == $id) {
      if($scope = end(self::$scopes))
        return $scope;
      throw new hamleEx_OutOfScope("Unable to find Scope ($id) or $key");
    }
    $key = $id - 1;
    if($id < 0) $key = count(self::$scopes) + $id - 1;
    if($id == 0) $key = count(self::$scopes) - 1;
    if(!isset(self::$scopes[$key]))
      throw new hamleEx_OutOfScope("Unable to find Scope ($id) or $key");
    return self::$scopes[$key];
  }

}