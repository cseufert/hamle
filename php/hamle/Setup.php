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
/**
 * Basic HAML Setup Class
 * This class should be extended to override the Model Methods, 
 * to use your model
 *
 * @author Chris Seufert <chris@seufert.id.au>
 */
class Setup {
  /**
   * Returns the full file path to the cache file
   * 
   * @param string $f Filename of cache file
   * @return string Directory to store cache in
   */
  function cachePath($f) {
    $dir = __DIR__."/../../cache/$f";
    if(!is_dir($dir)) mkdir($dir);
    return $dir;
  }

  /**
   * Open the default model when only an ID is specified in the template
   *
   * @param mixed $id Identifier when no type is passed
   * @param int $sortDir Sort Direction defined by hamle::SORT_*
   * @param string $sortField Field name to sort by
   * @param int $limit Results Limit
   * @param int $offset Results Offset
   * @return Model Instance of model class that implements hamleModel
   */
  function getModelDefault($id, $sortDir = 0, $sortField = "", $limit = 0, $offset = 0) { return new hamleDemoModel($id); }

  /**
   * Open a specific model type with id
   *
   * @param array[] $typeId Type ID array [type=>[id]] or [page=>[3]]
   * @param int $sortDir Sort Direction defined by hamle::SORT_*
   * @param string $sortField Field name to sort on
   * @param int $limit Results Limit
   * @param int $offset Results Offset
   * @throws Exception\RunTime
   * @return Model Instance of model class that implements hamleModel
   */
  function getModelTypeID($typeId, $sortDir = 0, $sortField = "", $limit = 0, $offset = 0) {
    if(count($typeId) > 1)
      throw new Exception\RunTime("Unable to open more than one ID at a time");
    foreach($typeId as $type=>$id)
      return hamleDemoModel::findId($type, current($id));
    return new Model\Zero();
  }

  /**
   * Return Iterator containing results from search of tags
   *
   * @param array[] $typeTags Type Tag Array [type=>[tag1,tag2],type2=>[]]
   * @param int $sortDir Sort Direction defined by hamle::SORT_*
   * @param string $sortField Field name to sort
   * @param int $limit Results Limit
   * @param int $offset Results Limit
   * @return Model Instance of Iteratable model class
   */
  function getModelTypeTags($typeTags, $sortDir = 0, $sortField = "", $limit = 0, $offset = 0) {
    return hamleDemoModel::findTag($typeTags);
  }
  /**
   * Give you the ability to adjust paths to template files, this includes files
   * loaded using '|include'.
   * 
   * @param string $f File Name and Path requested
   * @return string File Path to actual template file
   */
  function templatePath($f) {
    return $f;
  }
  /**
   * Returns an array of snippets paths for application to the current template
   * These snippets will be applied to tempaltes |included as well as the 
   * initial template.
   * 
   * @return array Array of file names
   */
  function snippetFiles() {
    return array();
  }

  /**
   * @return Parse\Filter[] List of HAMLE Parse Filters
   */
  function getFilters() {
    return array();
  }
  /**
   * Function to write debug logs out
   * @param $s string Debug Message String
   */
  function debugLog($s) {
    //var_dump($s);
  }
}
