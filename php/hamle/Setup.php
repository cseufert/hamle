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
 * Basic HAMLE Setup Class
 * This class should be extended to override the Model Methods,
 * to use your model
 *
 * @author Chris Seufert <chris@seufert.id.au>
 */
class Setup
{
  public $minify = true;

  /**
   * Returns the full file path to the cache file
   *
   * @param string $f Filename of cache file
   * @return string Directory to store cache in
   */
  public function cachePath($f)
  {
    $s = DIRECTORY_SEPARATOR;
    $dir = implode($s, [__DIR__, '..', '..', 'cache', '']);
    if (!is_dir($dir)) {
      mkdir($dir);
    }
    return $dir . $f;
  }

  /**
   * Open the default model when only an ID is specified in the template
   *
   * @param mixed $id Identifier when no type is passed
   * @param array $sort
   * @param int $limit Results Limit
   * @param int $offset Results Offset
   * @return Model Instance of model class that implements hamleModel
   */
  public function getModelDefault($id, $sort = [], $limit = 0, $offset = 0)
  {
    return new Model\Zero();
  }

  /**
   * Open a specific model type with id
   *
   * @param array[] $typeId Type ID array [type=>[id]] or [page=>[3]]
   * @param array $sort
   * @param int $limit Results Limit
   * @param int $offset Results Offset
   * @return Model
   * @throws Exception\RunTime
   */
  public function getModelTypeID($typeId, $sort = [], $limit = 0, $offset = 0)
  {
    if (count($typeId) > 1) {
      throw new Exception\RunTime('Unable to open more than one ID at a time');
    }
    return new Model\Zero();
  }

  /**
   * Return Iterator containing results from search of tags
   *
   * @param array[] $typeTags Type Tag Array [type=>[tag1,tag2],type2=>[]]
   * @param array $sort
   * @param int $limit Results Limit
   * @param int $offset Results Limit
   * @return Model Instance of Iterable model class
   */
  public function getModelTypeTags(
    $typeTags,
    $sort = [],
    $limit = 0,
    $offset = 0
  ) {
    return new Model\Zero();
  }
  /**
   * Give you the ability to adjust paths to template files, this includes files
   * loaded using '|include'.
   *
   * @param string $f File Name and Path requested
   * @return string File Path to actual template file
   */
  public function templatePath($f)
  {
    return $f;
  }
  /**
   * Returns an array of snippets paths for application to the current template
   * These snippets will be applied to templates |included as well as the
   * initial template.
   *
   * @return array Array of file names
   */
  public function snippetFiles()
  {
    return [];
  }

  /**
   * @return Parse\Filter[] List of HAMLE Parse Filters
   */
  public function getFilters()
  {
    return [];
  }
  /**
   * Function to write debug logs out
   * @param $s string Debug Message String
   */
  public function debugLog($s)
  {
  }

  /**
   * Called when |include "#fragment" is encountered
   * @param Hamle $hamle Current Hamle Instance
   * @param string $fragment Name of Fragment
   * @throws Exception
   */
  public function getFragment(Hamle $hamle, $fragment): string
  {
    throw new Exception("Unable to Include Fragment ($fragment)");
  }

  /**
   * Return if output minifcation should be enabled
   * @return bool
   */
  public function getMinify()
  {
    return $this->minify;
  }
}
