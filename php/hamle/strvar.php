<?php

/**
 * String with variables object
 *
 * @author Chris Seufert <chris@seufert.id.au>
 */

/*
 * Example Strings
 * Hellow $user
 * How many \$ have u got $user
 * Ex-Gst {$price->exgst}
 */
class hamleStrVar {
  const TOKEN_CONTROL = 0x07;
  const TOKEN_HTML    = 0x06;
  const TOKEN_CODE    = 0x04;
  
  const FIND_DOLLARFUNC = 0x01;
  const FIND_DOLLARVAR = 0x02;
  const FIND_BARDOLLAR = 0x04;

  protected $nodes;
  protected $mode;
  
  function __construct($s, $mode = self::TOKEN_HTML) {
    $this->nodes = array();
    $this->mode = $mode;
    $lastchar = "";
    $buff = "";
    while(strlen($s) > 1)
      if($s[0] == "\\" && $s[1] == "\$") {
        $buff .= $s[0].$s[1];
        $s = substr($s,2);
      } elseif($s[0] == "{" && $s[1] == "\$") {
        if($this->mode & self::FIND_BARDOLLAR) {
          if(strlen($buff)) $this->nodes[] = new hamleStrVar_string($buff);
          $buff = "";
          $this->bardollarStr($s);
        } else {
          $buff .= $s[0];
          $s = substr($s,1);
        }
      } elseif($s[0] == "\$" && $s[1] == "(" ) {
        if($this->mode & self::FIND_DOLLARFUNC) {
          if(strlen($buff)) $this->nodes[] = new hamleStrVar_string($buff);
          $buff = "";
          $this->dollarFunc($s);
        } else {
          $buff .= $s[0];
          $s = substr($s,1);
        }
      } elseif($s[0] == "\$") {
        if($this->mode & self::FIND_DOLLARVAR) {
          
          if(strlen($buff)) $this->nodes[] = new hamleStrVar_string($buff);
          $buff = "";
          $this->dollarStr($s);
        } else {
          $buff .= $s[0];
          $s = substr($s,1);
        }
      } else {
          $buff .= $s[0];
          $s = substr($s,1);
      }
    $buff .= $s;
    if(strlen($buff))
      $this->nodes[] = new hamleStrVar_string($buff);
  }
  
  protected function dollarStr(&$s) {
    $m = array();
    if(!preg_match('/\$([a-zA-Z0-9_]+)/', $s, $m))
      throw new hamleEx_ParseError("Unable to determine \$ substition in '".
                                    substr($s,0,15)."...");
    $s = substr($s, 1 + strlen($m[1]));
    $this->nodes[] = new hamleStrVar_var($m[1]);
  }

  protected function dollarFunc(&$s) {
    $out = NULL; $m = array();
    if(preg_match('/^\$\(([a-zA-Z0-9\.#_]+)?(?: *([><]) *([a-zA-Z0-9\.#_,]+))?\)\s*$/',$s, $m)) {
      $s = substr($s,strlen($m[0]));
      if(isset($m[1]) && $m[1])
        $out = new hamleStrVar_model($m[1]);
      else
        $out = new hamleStrVar_scope(0);
      $rel = array(">"=>hamle::REL_CHILD, "<"=>hamle::REL_PARENT);
      if(isset($m[2]) && $m[3]) {
        $out->addRel(new hamleStrVar_relfilt($rel[$m[2]], $m[3]));
      }
    } else
      throw new hamleEx_ParseError("Unable to pass expression \"$s\"");
    $this->nodes[] = $out;
  }
  protected function dollarScope(&$s) {
    if(preg_match('/^\$\[([0-9]+)\](.*)$/', $s, $m)) {
      $code .= 'hamleScope::get("'.addslashes($m[1]).'")';
      if($m[2])
        return new hamleStrVar_scope($m[2]);
    } else
      throw new hamleEx_ParseError("Unable to pass expression \"$s\"");
  }

  protected function bardollarStr(&$s) {
    $m = array();
    if(!preg_match('/{\$([a-zA-Z0-9_]+)(.*?)}/', $s, $m))
      throw new hamleEx_ParseError("Unable to determine \{\$ substition in '".
                                    substr($s,0,15)."...");
    $s = substr($s, 3 + strlen($m[1]) + strlen($m[2]));
    $this->nodes[] = new hamleStrVar_var($m[1], $m[2]);
  }
  
  function toHTML() {
    $out = "";
    foreach($this->nodes as $n)
      $out .= $n->toHTML();
    return $out;
  }
  
  function toPHP($allowtext = true) {
    $out = array();
    foreach($this->nodes as $n)
      $out[] = $n->toPHP();
    return implode(".",$out);
  }
  
  static function fromCommand($s) {
    
  }
  static function fromString($s) {
    
  }
  static function arrayToPHP($a) {
      return str_replace(array("\n", "\r"),"",var_export($a, true));
    }
}

interface hamleStrVar_int {
  function toHTML();
  function toPHP();
}

class hamleStrVar_string implements hamleStrVar_int {
  protected $s;
  function __construct($s) {
    $this->s = $s;
  }
  function toHTML() {
    return str_replace(
              array('\\$', "&"    ,"\""    ),
              array('$'  , "&amp;","&quot;"),$this->s);
  }
  function toPHP() {
    return '"'.$this->s.'"';
  }
}

class hamleStrVar_var implements hamleStrVar_int {
  protected $var, $extra;
  function __construct($var, $extra = "") {
    $this->var = $var;
    $this->extra = $extra;
  }
  function toHTML() {
    return "<?=".$this->toPHP()."?>";
  }
  function toPHP() {
    return "hamleScope::getVal(\"$this->var\")";
  }
}

abstract class hamleStrVar_intChild implements hamleStrVar_int {
  protected $relModel;
  function addRel(hamleStrVar_int $model) {
    $this->relModel = $model;
  }
  function relPHP($out) {
    if($this->relModel)
      return $out.$this->relModel->toPHP();
    return $out;
  }
}

class hamleStrVar_model extends hamleStrVar_intChild {
  protected $id = NULL, $tags = array(), $type = NULL;
  function __construct($idclass) {
    preg_match_all('/[#\.][a-zA-Z0-9\-\_]+/m', $idclass, $m);
    if(isset($m[0])) foreach($m[0] as $s) {
      if($s[0] == "#") $this->id = substr($s,1);
      if($s[0] == ".") $this->tags[] = substr($s,1);
    }
    if(preg_match('/^[a-zA-Z0-9\_]+/',$idclass, $m))
      $this->type = $m[0];
  }
  function toHTML() {
    return "<?=".$this->toPHP()."?>";
  }
  function toPHP() {
    $out = "";
    $id = addslashes($this->id);
    $type = addslashes($this->type);
    if($this->id)
      if($this->type)
        $out = "hamleRun::modelTypeID(\"$type\",\"$id\")";
      else
        $out = "hamleRun::modelID(\"$id\")";
    else
      if($this->tags && $this->type)
        throw new Exception("Unimplemented");
      elseif($this->tags)
        throw new Exception("Unimplemented");
      elseif($this->type)
        $out = "hamleRun::modelType(\"$type\")";
    return $this->relPHP($out);
  }
}

class hamleStrVar_scope extends hamleStrVar_intChild {
  protected $id;
  function __construct($id) {
    $this->id = $id;
  }
  function toHTML() {
    return "<?=".$this->toPHP()."?>";
  }
  function toPHP() {
    $out = "hamleScope::get(\"$this->id\")";
    return $this->relPHP($out);
  }
}
class hamleStrVar_relfilt implements hamleStrVar_int {
  protected $typeTags = array(), $rel;
  function __construct($rel, $filter) {
    $this->rel = $rel;
    $filters = explode(",",$filter);
    foreach($filters as $filter) {
      $type = ""; $tags = array();
      preg_match_all('/[#\.][a-zA-Z0-9\-\_]+/m', $filter, $m);
      if(isset($m[0])) foreach($m[0] as $s) {
        if($s[0] == "#")
            throw new hamleEx_ParseError("Unable to specify child by ID");
        if($s[0] == ".") $tags[] = substr($s,1);
      }
      if(preg_match('/^[a-zA-Z0-9\_]+/',$filter, $m))
        $type = $m[0];
      if(!$type) { 
        $type = "*";
        if(!$tags) continue;
      }
      $this->typeTags[$type] = $tags;
    }
  }
  function toHTML() {

  }
  function toPHP() {

    $tags = hamleStrVar::arrayToPHP($this->typeTags);
    return "->hamleRel({$this->rel}, $tags)";
  }
}