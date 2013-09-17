<?php
/**
 * HAMLE Controller/Model Scope Handler
 *
 * @author Chris Seufert <chris@seufert.id.au>
 */
class hamleScope {
  static $scopes;
  
  static function add($model) {
    if(!$model instanceOf hamleModel)
      throw new hamleEx_Unsupported("Unsupported Model, Needs to implement hamleModel Interface");
    self::$scopes[] = $model;
  }
  
  static function done() {
    if(!isset(self::$scopes)) self::$scopes = array();
    array_pop (self::$scopes);
  }
  static function get($id = 0) {
    if(!isset(self::$scopes)) self::$scopes = array();
    if($id > 0) $key = $id;
    if($id < 0) $key = count(self::$scope) + $id - 1;
    if($id = 0) $key = count(self::$scopes) - 1;
    if(!isset(self::$scopes[$key]))
      throw new hamleEx_OutOfScope("Unable to find Scope ($id) or $key");
    return self::$scope[$key];
  }
}