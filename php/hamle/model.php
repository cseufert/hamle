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
   * Retreive an iterable result of relatives to the current object
   * @param int $rel Relation to this object hamle::REL_CHILD, etc
   * @param array $typeTags Array of types to search containing tags eg([photo->[landscape,wide]])
   * @return hamleModel Return object must implmement hamleModel interface
   */
  function hamleRel($rel, $typeTags);
}

class hamleModel_zero implements hamleModel {
  function hamleGet($key) {
    throw new hamleEx_NoKey("Cant find Key ($key)");
  }
  function hamleRel($rel, $typeTags) {
    throw new hamleEx_NoFunc("Unable to retreive relations");
  }
  
  function valid() { return false; }
  function key() { return 0; }
  function current() {return $this; }
  function rewind() { }
  function next() { }
}

class hamleModel_one implements hamleModel {
  protected $hamleIndex = 0;
  function hamleGet($key) {
    throw new hamleEx_NoKey("Cant find Key ($key)");
  }
  function hamleRel($rel, $typeTags) {
    throw new hamleEx_NoFunc("Unable to retreive relations");
  }
  
  function valid() { return $this->hamleIndex == 0; }
  function key() { return $this->hamleIndex; }
  function current() {return $this; }
  function rewind() { $this->hamleIndex = 0; }
  function next() { $this->hamleIndex++; }
}

class hamleModel_array extends hamleModel_zero {
  protected $data;
  protected $pos;
  function __construct($array = array()) {
    $this->data = $array;
    $this->pos = 0;
  }
  function hamleGet($key) {
    if(!isset($this->data[$this->pos][$key]))
      return "Missing Key [$key]";
    return $this->data[$this->pos][$key];
  }
  function valid() { return isset($this->data[$this->pos]); }
  function key() { return $this->pos; }
  function current() { return $this; }
  function rewind() { $this->pos = 0; }
  function next() { ++$this->pos; }
    
}

class hamleModel_arrayObj extends hamleModel_array {
  function hamleGet($key) {
    return $this->data[$this->pos]->hamleGet($key);
  }
  function hamleRel($rel, $typeTags) {
    return $this->data[$this->pos]->hamleRel($rel, $typeTags);
  }
  function current() { return $this->data[$this->pos]; }
}
