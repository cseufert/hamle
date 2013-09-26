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
   * Returns the Dir to store cached hamle templates in
   * 
   * @return string Directory to store cache in
   */
  function getCacheDir() { 
    return __DIR__."/../../cache"; }
  
  /**
   * Open the default model when only an ID is specified in the template
   * 
   * @param mixed $id Identifier when no type is passed
   * @return hamleModel Instance of model class that implements hamleModel
   */
  function getDefaultModel($id) { return new hamleDemoModel($id); }
  
  /**
   * Open a specific model type with id
   * 
   * @param string $name Type Name
   * @param mixed $id Identifier for Model
   * @return hamleModel Instance of model class that implements hamleModel
   */
  function getNamedModel($name, $id = NULL) { 
    return hamleDemoModel::findId($name, $id); 
  }
  
  /**
   * Return Iterator containing results from search of tags
   * 
   * @param string $name Model Type Name
   * @param array $tags Array of Tags to search for (Default Logic is AND)
   * @return hamleModel Instance of Iteratable model class
   */
  function getSearchedModel($name, $tags, $id = NULL) {
    return hamleDemoModel::findTag($name, $tags, $id);
  }
  /**
   * Give you the ability to adjust paths to template files
   * 
   * @param string $f File Name and Path requested
   * @return string File Path to actual template file
   */
  function themePath($f) {
    return $f;
  }
}
