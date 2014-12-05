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
use Seufert\Hamle\Model;

/**
 * HAMLE Runtime
 *
 * @author Chris Seufert <chris@seufert.id.au>
 */
class Run {
  /**
   * @var Hamle Current HAMLE instance
   */
  static protected $hamle;
  static protected $hamleList = array();

  /**
   * Add a hamle instance to the stack
   * @param Hamle $hamle Hamle Instance
   */
  static function addInstance(Hamle $hamle) {
    self::$hamleList[] = $hamle;
    self::$hamle = $hamle;
  }

  /**
   * Remove hamle Instance from the stack
   */
  static function popInstance() {
    array_pop(self::$hamleList);
    if (self::$hamleList)
      self::$hamle = self::$hamleList[count(self::$hamleList) - 1];
    else
      self::$hamle = NULL;
  }

  /**
   * Execute a hamle String Filter
   * @param string $name Name of Filter
   * @param string $data Data to pass to filter
   * @return string Fitlered data
   */
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
   * @param array[] $typeTags array of tags with types as key eg ['page'=>[]] or ['product'=>['featured]]
   * @param int $sortDir Sort Direction see hamle::SORT_NATURAL...
   * @param string $sortField Sort Direction defined by hamle::SORT_*
   * @param int $limit Results Limit
   * @param int $offset Offset Results by
   * @internal param string $sortBy Field name to sort by
   * @return Model
   */
  static function modelTypeTags($typeTags, $sortDir = 0, $sortField = "", $limit = 0, $offset = 0) {
    return self::$hamle->setup->getModelTypeTags($typeTags,
        $sortDir, $sortField, $limit, $offset);
  }

  /**
   * Called from template by $() to find a specific model
   * @param string $id id to search for
   * @param int $sortDir Sort Direction defined by hamle::SORT_*
   * @param string $sortField Field to sort by
   * @param int $limit Limit of results
   * @param int $offset Results Offset
   * @throws RunTime
   * @return Model
   */
  static function modelId($id, $sortDir = 0, $sortField = "", $limit = 0, $offset = 0) {
    $o = self::$hamle->setup->getModelDefault($id,
        $sortDir, $sortField, $limit, $offset);
    if (!$o instanceOf Model) throw new RunTime("Application must return instance of hamleModel");
    return $o;
  }

  /**
   * Called from template by $() to find a specific model
   * @param array[] $typeId Array of types mapped to ids [type1=>[1],type2=>[2]]
   * @param int $sortDir Sort Direction defined by hamle::SORT_*
   * @param string $sortField Field name to sort by
   * @param int $limit Results Limit
   * @param int $offset Results Offset
   * @internal param string $type type to filter by
   * @internal param string $id id to search for
   * @return Model
   */
  static function modelTypeID($typeId, $sortDir = 0, $sortField = "", $limit = 0, $offset = 0) {
    return self::$hamle->setup->getModelTypeId($typeId,
        $sortDir, $sortField, $limit, $offset);
  }


}
