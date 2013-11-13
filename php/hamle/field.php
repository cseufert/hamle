<?php

/**
 * HAMLE Form Field
 * Basic form field to extend
 * @author Chris Seufert <chris@seufert.id.au>
 * @package hamle
 */

class hamleField {
  protected $opt;
  
  protected $name;
  
  protected $value;
  
  function __construct($name, $options = array()) {
    $this->value = null;
    $this->name = $name;
    $this->hint = "";
    $this->opt = $options + array("label"=>"$name", "regex"=>"", "required"=>"false",
         "default"=>"", "error"=>"Field is Required", "help"=>"", "test"=>null,
        "form"=>"noForm", "readonly"=>false, 'hinttext'=>'');
  }
  
  function __call($name, $valarray) {
    if(count($valarray) < 1) return $this->__get($name);
    $val = count($valarray) == 1?current($valarray):$valarray;
    switch($name) {
      case "name":
      case "valid":
        throw new hamleEx("Unable to change $name after object is created");
      case "val":
      case "value":
        $this->value = $val;
        break;
      default:
        $this->opt[$name] = $val;
    }
    return $this;
  }
  
  function __get($name) {
     switch($name) {
      case "name":
        return $this->name;
      case "val":
      case "value":
        return $this->getValue();
      default:
        return isset($this->opt[$name])?$this->opt[$name]:null;
    }
  }
  
  function __set($name, $val) {
    switch($name) {
      case "name":
      case "valid":
        throw new hamleEx("Unable to change $name after object is created");
      case "val":
      case "value":
        return $this->value = $val;
      default:
        return isset($this->opt[$name])?$this->opt[$name]:null;
    }
  }
  
  function getValue() {
    if(isset($_POST[$this->form."_".$this->name])) {
      $this->value = $_POST[$this->form."_".$this->name];
      if(get_magic_quotes_runtime())
        $this->value = stripslashes($this->value);
      return $this->value;
    } else 
      return is_null($this->value)?$this->opt['default']:$this->value;
  }
  function getInputAttrib($atts, &$type = "input") {
    $atts['value'] = new hamleString_FormField($this->name);
    $atts['name'] = $this->form."_".$this->name;
    $atts['type'] = "text";
    $atts['class'][] = get_class($this);
    return $atts;
  }
  function getLabelAttrib($atts) {
    $atts['class'][] = get_class($this);
    $atts["for"] = $this->form."_".$this->name;
    return $atts;
  }
  function getHintAttrib($atts) {
    $atts['class'][] = get_class($this);
    $atts['class'][] = "hamleFormHint";
    return $atts;
  }
  function doProcess() {
    $value = $this->getValue();
    if($this->opt['required'])
      $valid = $valid && ($value && true);
    if($this->opt['regex'])
      $valid = $valid && preg_match($this->opt['regex'], $value);
    $this->valid = $valid;
  }
  
}

class hamleField_Button extends hamleField {
  function getInputAttrib($atts, &$type = "input") {
    $atts = parent::getInputAttrib($atts, $type);
    $atts['type'] = "submit";
    return $atts;
  }
  
  function isClicked() {
    return isset($_REQUEST[$this->form."_".$this->name]);
  }
}