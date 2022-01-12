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

use Seufert\Hamle\Exception\RunTime;

/**
 * HAMLE Runtime
 *
 * @author Chris Seufert <chris@seufert.id.au>
 */
class Run
{
  /**
   * @var Hamle Current HAMLE instance
   */
  protected static ?Hamle $hamle = null;
  protected static array $hamleList = [];

  /**
   * Add a hamle instance to the stack
   * @param Hamle $hamle Hamle Instance
   */
  static function addInstance(Hamle $hamle): void
  {
    self::$hamleList[] = $hamle;
    self::$hamle = $hamle;
  }

  /**
   * Remove hamle Instance from the stack
   */
  static function popInstance(): void
  {
    array_pop(self::$hamleList);
    if (self::$hamleList) {
      self::$hamle = self::$hamleList[count(self::$hamleList) - 1];
    } else {
      self::$hamle = null;
    }
  }

  /**
   * Execute a hamle String Filter
   * @param string $name Name of Filter
   * @param mixed $data Data to pass to filter
   * @return mixed Fitlered data
   */
  static function filter(string $name, mixed $data): mixed
  {
    return strrev($data);
  }

  /**
   * Helper for hamle |include command
   * @param string $path Path to file to include
   * @return string HTML Code
   */
  static function includeFile($path)
  {
    return self::$hamle->load($path)->output();
  }

  /**
   * @param string $fragment Name of fragment
   * @internal Only for use in template system
   * @return string String to output where |include #fragment was called
   */
  static function includeFragment(string $fragment): string
  {
    return self::$hamle->setup->getFragment(self::$hamle, substr($fragment, 1));
  }

  /**
   * Called from template by $() to find a specific model
   * @param array[] $typeTags array of tags with types as key eg ['page'=>[]] or ['product'=>['featured]]
   * @param array $sort
   * @param int $limit Results Limit
   * @param int $offset Offset Results by
   * @internal param string $sortBy Field name to sort by
   * @return Model
   */
  static function modelTypeTags($typeTags, $sort = [], $limit = 0, $offset = 0)
  {
    return self::$hamle->setup->getModelTypeTags(
      $typeTags,
      $sort,
      $limit,
      $offset,
    );
  }

  /**
   * Called from template by $() to find a specific model
   * @param string $id id to search for
   * @param array $sort
   * @param int $limit Limit of results
   * @param int $offset Results Offset
   * @throws RunTime
   * @return Model
   */
  static function modelId($id, $sort = [], $limit = 0, $offset = 0)
  {
    $o = self::$hamle->setup->getModelDefault($id, $sort, $limit, $offset);
    if (!$o instanceof Model) {
      throw new RunTime('Application must return instance of hamleModel');
    }
    return $o;
  }

  /**
   * Called from template by $() to find a specific model
   * @param array[] $typeId Array of types mapped to ids [type1=>[1],type2=>[2]]
   * @param array<string,int> $sort Sort fields as key, and direction as value
   * @param int $limit Results Limit
   * @param int $offset Results Offset
   * @internal param string $type type to filter by
   * @internal param string $id id to search for
   * @return Model
   */
  static function modelTypeId(
    array $typeId,
    array $sort = [],
    int $limit = 0,
    int $offset = 0
  ) {
    return self::$hamle->setup->getModelTypeId($typeId, $sort, $limit, $offset);
  }
}
