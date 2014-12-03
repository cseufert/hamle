<?php
/**
 * HAMLE Controller/Model Scope Handler
 *
 * @author Chris Seufert <chris@seufert.id.au>
 */
class hamleScope {
  /** @var hamleModel[] Array of Models by Scope Order  */
  static $scopes = array();
  /** @var hamleModel[] Assoc array of Models by Scope Name  */
  static $namedScopes = array();

  static function add($model, $name = null) {
    if(!$model instanceOf hamleModel)
      throw new hamleEx_Unsupported("Unsupported Model, Needs to implement hamleModel Interface");
    if($name)
      self::$namedScopes[$name] = $model;
    else
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

  static function getName($name) {
    if($name && isset(self::$namedScopes[$name])) {
      self::$namedScopes[$name]->rewind();
      return self::$namedScopes[$name];
    } else
      throw new hamleEx_RunTime("Unable to find scope ($name)");
  }

}