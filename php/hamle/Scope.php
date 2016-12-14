<?php
namespace Seufert\Hamle;

use Seufert\Hamle\Exception\OutOfScope;
use Seufert\Hamle\Exception\Unsupported;
use Seufert\Hamle\Exception\RunTime;
use Seufert\Hamle\Model;

/**
 * HAMLE Controller/Model Scope Handler
 *
 * @author Chris Seufert <chris@seufert.id.au>
 */
class Scope {
  /** @var Model[] Array of Models by Scope Order */
  static $scopes = array();
  /** @var Model[] Assoc array of Models by Scope Name */
  static $namedScopes = array();

  static function add($model, $name = null) {
    if (!$model instanceOf Model)
      throw new Unsupported("Unsupported Model (".get_class($model)."), Needs to implement hamleModel Interface");
    if ($name)
      self::$namedScopes[$name] = $model;
    else
      self::$scopes[] = $model;
  }

  static function done() {
    array_pop(self::$scopes);
  }

  /**
   * Get arbitary scope
   * 0 = current
   * negative vals = back form here, -1 = last, -2 one before last, etc
   * positive vals = absolute position, 1 = first, 2 = second, etc
   * @param int $id ID of scope to get
   * @return Model
   * @throws OutOfScope
   */
  static function get($id = 0) {
    if (0 == $id) {
      if ($scope = end(self::$scopes))
        return $scope;
      throw new OutOfScope("Unable to find Scope ($id)");
    }
    $key = $id - 1;
    if ($id < 0) $key = count(self::$scopes) + $id - 1;
    if ($id == 0) $key = count(self::$scopes) - 1;
    if (!isset(self::$scopes[$key]))
      throw new OutOfScope("Unable to find Scope ($id) or $key");
    return self::$scopes[$key];
  }

  static function getTopScope() {
    return end(self::$scopes);
  }
  static function getDepth() {
    return count(self::$scopes);
  }

  static $returnZeroOnNoScope = false;

  static function getName($name) {
    if ($name && isset(self::$namedScopes[$name])) {
      self::$namedScopes[$name]->rewind();
      return self::$namedScopes[$name];
    } else
      if(self::$returnZeroOnNoScope)
        return new Model\Zero();
      throw new RunTime("Unable to find scope ($name)");
  }

}
