<?php

/**
 * HAMLE Tag object
 *
 * @author Chris Seufert <chris@seufert.id.au>
 * @package hamle
 */
class hamleTag {
  /**
   * @var array Array of children tag elements 
   */
  protected $tags;
  /**
   * @var string Tag Type for Printable Tags 
   */
  protected $type;
  /**
   * @var array Array of lines of Content 
   */
  protected $content;
  /**
   * Number of spaces for each Indent when doing pretty format of output
   */
  const INDENT_SIZE = 2;
  
  /**
   * Initialize instance vars
   */
  function __construct() {
    $this->tags = array();
    $this->content = array();
  }
  
  /**
   * Add a child tag to this tag
   * @param hamleTag $tag Tag to add as child
   */
  function addChild($tag) {
    $this->tags[] = $tag;
  }
  
  /**
   * Render html/php output to disk
   * 
   * @param int $indent Number of spaces in current indent level
   * @param boolean $doIndent Indent this tag
   * @return string HTML/PHP Output
   */
  function render($indent = 0, $doIndent = true) {
    //if(strtoupper($this->type) == "A") var_dump($this);
    $ind = $doIndent?str_pad("", $indent, " "):"";
    $oneliner = ((count($this->content) > 1 || $this->tags)?false:true);
    $out = $ind.$this->renderStTag().($oneliner?"":"\n");
    if($this->content) $out .= $this->renderContent($ind, $oneliner);
    foreach($this->tags as $tag)
      $out .= $tag->render($indent + self::INDENT_SIZE);
    $out .= ($oneliner?"":$ind).$this->renderEnTag()."\n";
    return $out;
  }
  
  /**
   * Apply indent, to content, and return as string
   * 
   * @param string $pad Indent String
   * @return string Indented Content
   */
  function renderContent($pad = "", $oneliner = false) {
    $out = "";
    foreach($this->content as $c)
      $out .= ($oneliner?"":$pad).$c.($oneliner?"":"\n");
    return $out;
  }
  /**
   * Output the Start Tag for this element
   */
  function renderStTag() {}
  /**
   * Output the End Tag for this element
   */
  function renderEnTag() {}
  /**
   * Add content to this tag, one line at a time
   * 
   * @param string $s One line of content
   */
  function addContent($s) {
    if(trim($s))
      $this->content[] = hamleStr::pass($s, true);
    //else
    //  throw new Exception("Blank Line");
  }
  
}
/**
 * HAMLE Control Tag
 * Used for tags starting with the pipe (|) symbol
 */
class hamleTag_Ctrl extends hamleTag {
  /**
   * @var string Variable passed to Control Tag 
   */
  protected $var;
  protected $o;
  static $instCount = 1;
  function __construct($tag) {
    parent::__construct();
    $this->o = "\$o".self::$instCount++;
    $this->type = strtolower($tag);
    $this->var = "";
  }
    
  function renderStTag() {
    $out = "<"."?php ";
    //var_dump($this->type);
    switch($this->type) {
      case "each":
        if($this->var) {
          $out .= "foreach(".hamleStr::native($this->var)." as {$this->o}) { \n";
          $out .= "hamleScope::add({$this->o}); ";
        } else {
          $out .= "foreach(hamleScope::get() as {$this->o}) { \n";
          $out .= "hamleScope::add({$this->o}); ";
        }
        break;
      case "if":
        $out .= "if()";
        break;
      case "with":
        $out .= "if(({$this->o} = ".hamleStr::native($this->var).") && (is_array({$this->o}) || {$this->o}"."->valid())) {\n";
        $out .= "hamleScope::add({$this->o});\n;";
        break;
      case "include":
        $out .= "echo hamle::includeFile(\"".hamleStr::passStr($this->var)."\");";
        break;
    }
    return $out.' ?>';
  }
  /**
   * @param string $s Variable String for control tag
   */
  function setVar($s) {
    $this->var = trim($s);
  }
  function renderEnTag() {
    $out = '<'.'?php ';
    switch($this->type) {
      case "each";
      case "with";
          $out .= 'hamleScope::done(); ';
        $out .= '}';
        break;
      case "include":
        return "";
        break;
    }
    return $out.' ?>';
  }
  function render($indent = 0, $doIndent = true) {
    return parent::render($indent - self::INDENT_SIZE, false);
  }
}

/**
 * HAMLE Filter Tag
 * Filter tags start with colon or (:) and use hamleFilter_<filtername>
 */
class hamleTag_Filter extends hamleTag {
  function __construct($tag) {
    parent::__construct();
    $this->type = strtolower($tag);
    $this->filter = "hamleFilter_{$this->type}";
    if(!class_exists($this->filter))
      Throw new hamleEx_NoFilter("Unable to fild filter $tag");
  }
  function renderContent($pad = "", $oneliner = false) {
    $c = $this->filter;
    return $c::filterText(parent::renderContent($pad));
  }
  function renderStTag() {
    $c = $this->filter;
    return $c::stTag();
  }
  function renderEnTag() {
    $c = $this->filter;
    return $c::ndTag();
  }
}
/**
 * HAMLE HTML Tag
 * Use to represent plain HTML Tags
 */
class hamleTag_HTML extends hamleTag {
  /**
   * @var array Options for html tags (eg, href, class, style, etc) 
   */
  protected $opt;

  function __construct($tag, $classid, $param=array()) {
    parent::__construct();
    $this->type = $tag?$tag:"div";
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
  /**
   * Used to convert urlencoded string into html attributes
   * 
   * @return string HTML Attributes
   */
  function optToTags() {
    $out = array();
    foreach($this->opt as $k=>$v)
      $out[] = " ".hamleStr::pass($k, true)."=\"".hamleStr::pass($v, true)."\"";
    return implode("", $out);
  }
}

/**
 * String Tag
 */
class hamleTag_String extends hamleTag {
  
}

class hamleTag_Comment extends hamleTag {
  function render($indent = 0, $doIndent = true) {
    return "";
  }
    
}