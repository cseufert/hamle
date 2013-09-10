<?php

/**
 * HAMLE Tag object
 *
 * @author Chris Seufert <chris@seufert.id.au>
 * @package hamle
 */
class hamleTag {
  protected $tags;
  protected $type;
  protected $opt;
  protected $content;
  
  function __construct() {
    $this->tags = array();
    $this->content = "";
  }
  
  function addChild($tag) {
    $this->tags[] = $tag;
  }
  
  function render($indent = 0) {
    $ind = str_pad("", $indent, " ");
    $out = $ind.$this->renderStTag()."\n";
    if($this->content) $out .= $ind.$this->content."\n";
    foreach($this->tags as $tag)
      $out .= $tag->render($indent + 2);
    $out .= $ind.$this->renderEnTag()."\n";
    return $out;
  }
  
  function renderStTag() {}
  function renderEnTag() {}
  
  function addContent($s) {
    $this->content = $s;
  }
  
}

class hamleTag_Ctrl {
  
}

class hamleTag_HTML extends hamleTag {
  function __construct($tag, $classid, $param=array()) {
    parent::__construct();
    $this->type = $tag;
    /// todo: variable substitution
    if(isset($param[0]) && $param[0] == "[") {
      $param = substr($param, 1, strlen($param)-2);
      parse_str($param, $this->opt); 
    }
    if(!isset($this->opt['class']))
      $this->opt['class'] = "";
    preg_match_all('/[#\.][a-zA-Z0-9\-\_]+/m', $classid, $m);
    if(isset($m[0])) foreach($m[0] as $s) {
      if($s[0] == "#") $this->opt['id'] = substr($s,1);
      if($s[0] == ".") $this->opt['class'] .= " ".substr($s,1);
    }
    $this->opt['class'] = trim($this->opt['class']);
    if(!$this->opt['class']) unset($this->opt['class']);
  }
  function renderStTag() {
    return "<{$this->type}".$this->optToTags().">";
  }
  function renderEnTag() {
    return "</{$this->type}>";
  }
  
  function optToTags() {
    $out = array();
    foreach($this->opt as $k=>$v)
      $out[] = " $k=\"".  htmlspecialchars($v)."\"";
    return implode("", $out);
  }
}
