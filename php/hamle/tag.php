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
  protected $tags = array();
  /**
   * @var string Tag Type for Printable Tags 
   */
  protected $type;
  /**
   * @var array Array of lines of Content 
   */
  protected $content;
  
  protected $opt;
  
  protected $source;
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
   @param array $path array of arrays[type/class/id]
   */
  function find($path) {
    //var_dump($this->type, json_encode($path), $this->compare($path[0]));
    $list = array();
    if($this->compare($path[0]))
      array_shift($path);
    if(count($path))
      foreach($this->tags as $tag)
        $list = array_merge($list, $tag->find($path));
    else
      $list[] = $this;
    return $list;
  }
  function replace($path, $newTag) {
    $r = false;
    if($this->compare($path[0]))
      array_shift($path);
    if(!count($path))
      $r = true;
    foreach($this->tags as $k=>$tag)
      if($tag->replace($path, $newTag)) {
        $inner = $this->tags[$k];
        array_splice($this->tags, $k, 1, $newTag->tags);
        $newTag->addSnipContent($inner);
      }
    return $r;
  }
  
  function addSnipContent($contentTag, &$tagArray = array(), $key = 0) {
    foreach($this->tags as $k => $tag)
      $tag->addSnipContent($contentTag, $this->tags, $k);
  }
  
  function compare($tic) {
    $class = array();
    if(isset($tic['type']) && $this->type != $tic['type'])
      return false; 
    if(isset($tic['id']) && 
            !(isset($this->opt['id']) && $tic['id'] == $this->opt['id']))
      return false;
    if(isset($this->opt['class']))
      $class = explode(' ',$this->opt['class']);
    if(isset($tic['class']) && 
            count($tic['class']) && array_diff($tic['class'],$class))
      return false;      
    return true;
  }
  
  /**
   * Add a child tag to this tag
   * @param hamleTag $tag Tag to add as child
   */
  function addChild($tag, $mode = "append") {
    if($mode == "prepend")
      array_unshift($this->tags,$tag);
    else
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
  function addContent($s, $strtype = hamleStrVar::TOKEN_HTML) {
    if(trim($s)) {
      $parse = new hamleStrVar($s, $strtype);
      $this->content[] = $parse->toHTML();
    }
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
    $hsv = new hamleStrVar($this->var, hamleStrVar::TOKEN_CONTROL);
    switch($this->type) {
      case "each":
        if($this->var)
          $out .= "foreach(".$hsv->toPHP()." as {$this->o}) { \n";
        else
          $out .= "foreach(hamleScope::get() as {$this->o}) { \n";
        $out .= "hamleScope::add({$this->o}); ";
        break;
      case "if":
        $out .= "if(".$hsv->toPHP().") {";
        break;
      case "with":
        $out .= "if(({$this->o} = ".$hsv->toPHP().") && ".
                    "(is_array({$this->o}) || {$this->o}"."->valid())) {\n";
        $out .= "hamleScope::add({$this->o});\n;";
        break;
      case "include":
        $out .= "echo hamleRun::includeFile(".$hsv->toPHP().");";
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
        $out .= 'hamleScope::done(); ';
        $out .= '}';
        if(!$this->var)
          $out .= "hamleScope::get()->rewind();\n";
        break;
      case "if":
        $out .= "}";
        break;
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
  static protected $selfCloseTags = array("area","base","br","col","command",
                              "embed","hr","img","input","keygen","link",
                                "meta","param","source","track","wbr");

  function __construct($tag, $classid, $param=array()) {
    parent::__construct();
    $this->opt = array();
    $this->source = array();
    $this->type = $tag?$tag:"div";
    /// todo: variable substitution
    if(isset($param[0]) && $param[0] == "[") {
      $param = substr($param, 1, strlen($param)-2);
      parse_str($param, $this->opt); 
    }
    $this->opt += array("class"=>"");
    
    preg_match_all('/[#\.!][a-zA-Z0-9\-\_]+/m', $classid, $m);
    if(isset($m[0])) foreach($m[0] as $s) {
      if($s[0] == "#") $this->opt['id'] = substr($s,1);
      if($s[0] == ".") $this->opt['class'] .= " ".substr($s,1);
      if($s[0] == "!") $this->source[] = substr($s,1);
    }
    $this->opt['class'] = trim($this->opt['class']);
    if(!$this->opt['class']) unset($this->opt['class']);
  }
  function renderStTag() {
    $close = in_array($this->type,self::$selfCloseTags)?" />":">";
    return "<{$this->type}".$this->optToTags().$close;
  }
  function renderEnTag() {
    if(in_array($this->type,self::$selfCloseTags))
            return "";
    return "</{$this->type}>";
  }
  /**
   * Used to convert urlencoded string into html attributes
   * 
   * @return string HTML Attributes
   */
  function optToTags() {
    $out = array();
    foreach($this->opt as $k=>$v) {
      $v = new hamleStrVar($v);
      $k = new hamleStrVar($k);
      $out[] = " ".$k->toHTML()."=\"".$v->toHTML()."\"";
    }
    return implode("", $out);
  }
}

class hamleTag_Snippet extends hamleTag {
  protected $path;
  function __construct($params) {
    parent::__construct();
    if(!preg_match('/^(append|content|prepend|replace)(?: (.*))?$/', $params,$m))
      throw new hamleEx_ParseError("Unable to parse Snippet($params)");
    $this->type = $m[1];
    if(isset($m[2]))
      $this->path = explode(" ",$m[2]);
    else
      $this->path = array();
    foreach($this->path as $k=>$v)
      $this->path[$k] = hamleStrVar::getTIC($v);
  }

  function addSnipContent($contentTag, &$tagArray = array(), $key = 0) {
    if($this->type == "content") {
      $tagArray[$key] = $contentTag;
    } else
      parent::addSnipContent ($contentTag, $tagArray, $key);
  }

  function apply(hamleTag $rootTag) {
    if($this->type == "append" or $this->type == "prepend") {
      $matchTags = $rootTag->find($this->path);
    foreach($matchTags as $tag)
      foreach($this->tags as $t) {
        $tag->addChild($t, $this->type);
      }
    } elseif($this->type == "replace") {
      $rootTag->replace($this->path, $this);
    } else
      throw new Exception("Cant Apply snippet to document '{$this->type}'");
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

class hamleTag_Form extends hamleTag {
  protected static $sForm, $sCount;
  protected $var;
  /**
   * @var hamleForm  Hamle Form Instance for configuring template
   */
  protected $form;
  
  function __construct($param) {
    parent::__construct();
    $param = explode(' ',$param);
    if(count($param) < 2) throw new hamleEx_ParseError("|form requires 2 arguments, form type, and instance");
    $this->var = new hamleStrVar($param[1]);
    $this->form = new $param[0];
  }
  function renderStTag() {
    self::$sForm[] = $this;
    self::$sCount = count(self::$sForm);
    $out = array();
    foreach($this->form->getHTMLProp() as $k=>$v) {
      $v = $v; $k = $k;
      $out[] = "$k=\"$v\"";
    }
    $fields = $this->form->getFields();
    $labelTags = $this->find(array(array("type"=>"label")));
     foreach($labelTags as $tag) 
      if($tag instanceOf hamleTag_HTML)
        foreach($tag->source as $source) {
          $tag->opt = $fields[$source]->getLabelAttrib($tag->opt);
          $tag->addContent($fields[$source]->label);
        }
    $inputTags = $this->find(array(array("type"=>"input")));
    foreach($inputTags as $tag) 
      if($tag instanceOf hamleTag_HTML)
        foreach($tag->source as $source) {
          $tag->opt = $fields[$source]->getInputAttrib($tag->opt);
          unset($fields[$source]);
        }
    foreach($fields as $n=>$f) {
      $this->addChild($label = new hamleTag_HTML("label","!$n"));
      $this->addChild($input = new hamleTag_HTML("input","!$n"));
      $label->opt = $f->getLabelAttrib($label->opt);
      $input->opt = $f->getInputAttrib($input->opt);
      $label->addContent($f->label);
    }
    return "<form ".implode(" ", $out)."><?php \$form = ".$this->var->toPHP()."; ?>";
  }

  function renderEnTag() {
    return "<?php unset(\$form); ?></form>";
    array_pop(self::$sForm);
    self::$sCount = count(self::$sForm);
  }
}