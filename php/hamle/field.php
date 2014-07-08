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
  
  protected $setValue = null;

  protected $valid = true;
  
  function __construct($name, $options = array()) {
    $this->name = $name;
    $this->hint = "";
    $this->opt = $options + array("label"=>"$name", "regex"=>"", "required"=>"false",
         "default"=>"", "error"=>"$name is Required", "help"=>"", "test"=>null,
        "form"=>"noForm", "readonly"=>false, 'hinttext'=>'',"disabled"=>false);
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
        $this->setValue = $val;
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
        return $this->setValue = $val;
      default:
        return isset($this->opt[$name])?$this->opt[$name]:null;
    }
  }
  
  function getValue() {
    if(!is_null($this->setValue)) return $this->setValue;
    if(isset($_REQUEST[$this->form."_".$this->name])) {
      if(get_magic_quotes_runtime())
          return stripslashes($_REQUEST[$this->form."_".$this->name]);
        else
          return $_REQUEST[$this->form."_".$this->name];
    }
    return $this->opt['default'];
  }
  function getInputAttStatic(&$atts, &$type, &$content) {
    $atts['id'] = $atts['name'] = $this->form."_".$this->name;
    $atts['type'] = "text";
    $atts['class'][] = get_class($this);
  }
  function getInputAttDynamic(&$atts, &$type, &$content) {
    $type = "input";
    $atts['value'] = new hamleString_FormField($this->name);
    if(!$this->valid) {
      $atts['class'][] = "hamleFormError";
    }
    if($this->opt["disabled"])
      $atts['disabled'] = "disabled";
    if($this->opt['help'])
      $atts['title'] = $this->opt['help'];
  }
  function getLabelAttStatic(&$atts, &$type, &$content) {
    $atts['class'][] = get_class($this);
    $atts["for"] = $this->form."_".$this->name;
    $content = array($this->opt['label']);
  }
  function getLabelAttDynamic(&$atts, &$type, &$content) {
  }
  function getHintAttStatic(&$atts, &$type, &$content) {
    $atts['class'][] = get_class($this);
    $atts['class'][] = "hamleFormHint";
  }
  function getHintAttDynamic(&$atts, &$type, &$content) {
    $type = "div";
    if(!$this->valid) {
      $content = array($this->opt['error']);
      $atts['class'][] = "hamleFormError";
    }
  }
  function getDynamicAtt($base, &$atts, &$type, &$content) {
    if($base == "input") {
      $this->getInputAttDynamic($atts, $type, $content);
    } elseif($base == "hint") {
      $this->getHintAttDynamic($atts, $type, $content);
    } elseif($base == "label") {
      $this->getLabelAttDynamic($atts, $type, $contnet);
    }
  }
  function doProcess($submit) {
    if($submit) {
      $value = $this->getValue();
      if($this->opt['required'])
        $this->valid = $this->valid && strlen($value);
      if($this->opt['regex'])
        $this->valid = $this->valid && preg_match($this->opt['regex'], $value);
    }
  }
  
}

class hamleField_Button extends hamleField {
  function getInputAttStatic(&$atts, &$type, &$content) {
    parent::getInputAttStatic($atts, $type, $content);
    $atts['type'] = "submit";
  }
  
  function isClicked() {
    return isset($_REQUEST[$this->form."_".$this->name]);
  }
}

class hamleField_Memo extends hamleField {
  function getInputAttStatic(&$atts, &$type, &$content) {
    parent::getInputAttStatic($atts, $type, $content);
    unset($atts['type']);
  }
  function getInputAttDynamic(&$atts, &$type, &$content) {
    parent::getInputAttDynamic($atts, $type, $content);
    unset($atts['value']);
    $type = "textarea";
    unset($atts['type']);
    $content = array(htmlspecialchars($this->getValue()));
  }

  function isClicked() {
    return isset($_REQUEST[$this->form."_".$this->name]);
  }
}