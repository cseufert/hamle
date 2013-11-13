<?php

/**
 * Basic HAML Setup Class
 * This class should be extended to override the Model Methods, 
 * to use your model
 *
 * @author Chris Seufert <chris@seufert.id.au>
 */
class hamleSetup {
  /**
   * Returns the full file path to the cache file
   * 
   * @param string $file Filename of cache file
   * @return string Directory to store cache in
   */
  function cachePath($f) {
    return __DIR__."/../../cache/$f"; }
  
  /**
   * Open the default model when only an ID is specified in the template
   * 
   * @param mixed $id Identifier when no type is passed
   * @return hamleModel Instance of model class that implements hamleModel
   */
  function getModelDefault($id, $sortDir = 0, $sortField = "", $limit = 0, $offset = 0) { return new hamleDemoModel($id); }
  
  /**
   * Open a specific model type with id
   * 
   * @param string $name Type Name
   * @param mixed $id Identifier for Model
   * @return hamleModel Instance of model class that implements hamleModel
   */
  function getModelTypeID($typeId, $sortDir = 0, $sortField = "", $limit = 0, $offset = 0) {
    if(count($typeId) > 1)
      throw new hamleEx_Runtime("Unable to open more than one ID at a time");
    foreach($typeId as $type=>$id)
      return hamleDemoModel::findId($name, current($id)); 
  }
  
  /**
   * Return Iterator containing results from search of tags
   * 
   * @param string $name Model Type Name
   * @param array $tags Array of Tags to search for (Default Logic is AND)
   * @return hamleModel Instance of Iteratable model class
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
   * Function to write debug logs out
   * @param $s string Debug Message String
   */
  function debugLog($s) {
    //var_dump($s);
  }
}
