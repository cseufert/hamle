<?php

/**
 * HAMLE Model Interface, all models used in the template must implmenent this
 *
 * @author Chris
 */
interface hamleModel extends Iterator {
  /**
   * hamleGet Must be implemented to get a variable using key
   * 
   * @param string $key String of key name to retreive
   * @throws hamleEx_NoKey
   */
  function hamleGet($key);
  /**
   * hamleExec Must also be implemented to exec functions on the model
   * 
   * @param string $func Name of function hamle wants to call
   * @param array $args Arguments passed to function
   * @return hamleModel Must return another object that implements hamleModel
   * @throws hamleEx_NoFunc
   */
  function hamleExec($func, $args);
  
  function hamleChild($selector);
}

class hamleModel_zero implements hamleModel {
  function hamleGet($key) {
    throw new hamleEx_NoKey("Cant find Key ($key)");
  }
  function hamleExec($func, $args) {
    throw new hamleEx_NoFunc("Cant find Func ($func)");
  }
  function hamleChild($selector) {
    throw new hamleEx_NoFunc("Unable to find children");
  }
  
  function valid() { return false; }
  function key() { return 0; }
  function current() {return $this; }
  function rewind() { }
  function next() { }
}

class hamleModel_array extends hamleModel_zero {
  protected $data;
  protected $pos = 0;
  function __construct($array = array()) {
    $this->data = $array;
  }
  function hamleGet($key) {
    if(!isset($this->data[$this->pos][$key]))
      throw new hamleEx_NoKey("Cant find Key ($key)");
    return $this->data[$this->pos][$key];
  }
  function valid() { return isset($this->data[$this->pos]); }
  function key() { return $this->pos; }
  function current() { return $this; }
  function rewind() { $this->pos = 0; }
  function next() { $this->pos++; }
    
}
