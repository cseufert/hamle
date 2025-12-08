<?php
namespace Seufert\Hamle;

use Seufert\Hamle\Exception\OutOfScope;
use Seufert\Hamle\Exception\Unsupported;
use Seufert\Hamle\Exception\RunTime;

/**
 * HAMLE Controller/Model Scope Handler
 *
 * @author Chris Seufert <chris@seufert.id.au>
 */
class Scope
{
  /** @var Model[] Array of Models by Scope Order */
  static $scopes = [];
  /** @var Model[] Assoc array of Models by Scope Name */
  static $namedScopes = [];

  /** @var null|Callable */
  static $scopeHook;

  static NoScopeMode $noScopeMode = NoScopeMode::Throw;

  static function add(Model $model, ?string $name = null): void
  {
    if (!$model instanceof Model) {
      throw new Unsupported(
        'Unsupported Model (' .
          get_class($model) .
          '), Needs to implement hamleModel Interface',
      );
    }
    if ($name) {
      self::$namedScopes[$name] = $model;
    } else {
      self::$scopes[] = $model;
    }
    if (self::$scopeHook) {
      (self::$scopeHook)($model);
    }
  }

  static function done(): void
  {
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
  static function get(int $id = 0): Model
  {
    if (0 === $id) {
      if ($scope = end(self::$scopes)) {
        return $scope;
      }
      return self::$noScopeMode->get($id);
    }
    $key = $id - 1;
    if ($id < 0) {
      $key = count(self::$scopes) + $id - 1;
    }
    if (!isset(self::$scopes[$key])) {
      return self::$noScopeMode->get("$id / $key");
    }
    return self::$scopes[$key];
  }

  static function getTopScope(): ?Model
  {
    return end(self::$scopes) ?: null;
  }
  static function getDepth(): int
  {
    return count(self::$scopes);
  }

  /**
   * @var bool Return Zero Model when named Scope is not found
   */
  static bool $returnZeroOnNoScope = false;

  /**
   * @param string $name Name of scope to return
   * @return Model
   * @throws OutOfScope
   */
  static function getName(string $name): Model
  {
    if ($name && isset(self::$namedScopes[$name])) {
      self::$namedScopes[$name]->rewind();
      return self::$namedScopes[$name];
    } elseif (self::$returnZeroOnNoScope) {
      return new Model\Zero();
    }
    return self::$noScopeMode->get($name);
  }
}
