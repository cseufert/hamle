<?php

namespace Seufert\Hamle\Tag;

class HTMLElement {
  public $tag = "div";
  public $id = null;
  public $class = null;
  public $style = null;
  public $href = null;
  public $src = null;
  public $alt = null;
  public $title = null;
  public $name = null;
  public $value = null;
  public $type = null;
  public $target = null;

  public $data = [];
  public $extra = [];

  function __construct($tag, $attr) {
    foreach($attr as $k=>$v) {
      if(property_exists($this,$k))
        $this->$k = $v;
      else
    }
  }
}