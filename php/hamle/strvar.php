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
class hamleStrVar implements hamleStrVar_int {
  const TOKEN_CONTROL = 0x07;
  const TOKEN_HTML    = 0x06;
  const TOKEN_CODE    = 0x04;
  const TOKEN_COMPARE = 0x0f;
  
  const FIND_DOLLARFUNC = 0x01;
  const FIND_DOLLARVAR = 0x02;
  const FIND_BARDOLLAR = 0x04;
  const FIND_COMPARISON = 0x08;

  const REGEX_VARNAME = '[a-zA-Z0-9_]+';
  
  protected $nodes;
  protected $mode;
  
  static function comparison($s) {
    $m = array(); $t = self::TOKEN_COMPARE;
    if(preg_match('/^(.*) '.hamleStrVar_Comp::REGEX_COMP_OPER.' (.*)$/', $s, $m))
      return new hamleStrVar_Comp(new self($m[1], $t),
                                  new self($m[3], $t), $m[2]);
    else
      return new self($s);
  }
  
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
  static function getCodeSnippet($s) {
    $line = hamle::getLineNo()+1;

    return "'".substr($s,0,20)."...' on line $line";
  }
  protected function dollarStr(&$s) {
    $m = array();
    if(!preg_match('/\$('.self::REGEX_VARNAME.')/', $s, $m))
      throw new hamleEx_ParseError("Unable to determine \$ substition in ".
                                    hamleStrVar::getCodeSnippet($s));
    $s = substr($s, 1 + strlen($m[1]));
    $this->nodes[] = new hamleStrVar_var($m[1]);
  }

  protected function dollarFunc(&$s) {
    $out = NULL; $m = array();
    if(preg_match('/^\$\(([a-zA-Z0-9\.,#_:\\^\\-]+)?(?: *([><]) *([a-zA-Z0-9\.,#_:\\^\\-]+))?\)\s*$/',$s, $m)) {
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
      throw new hamleEx_ParseError("Unable to exec \$() in ".
                                    hamleStrVar::getCodeSnippet($s));
    $this->nodes[] = $out;
    return $out;
  }
  protected function dollarScope(&$s) {
    if(preg_match('/^\$\[([0-9]+)\](.*)$/', $s, $m)) {
      $out = new hamleStrVar_scope($m[1]);
      $this->nodes[] = $out;
      return $out;
    } else
      throw new hamleEx_ParseError("Unable to pass expression \"$s\"");
  }

  protected function bardollarStr(&$s) {
    $m = array();
    if(preg_match('/{\$('.self::REGEX_VARNAME.')(.*?)}/', $s, $m)) {
      $s = substr($s, 3 + strlen($m[1]) + strlen($m[2]));
      $this->nodes[] = new hamleStrVar_var($m[1], $m[2]); 
    } else {
      if(preg_match('/{(\$.*?)(->('.self::REGEX_VARNAME.')(.*?))?}/',$s,$m)) {
        $s = substr($s, strlen($m[0]));
        if($m[1][1] == "[")
          $n = $this->dollarScope($m[1]);
        else
          $n = $this->dollarFunc($m[1]);
        if($m[3])
          $n->getVar($m[3]);
        if($m[4])
          throw new hamleEx_ParseError("Not sure what to do with {$m[4]} in ".
                                    hamleStrVar::getCodeSnippet($s));
      } else
        throw new hamleEx_ParseError("Unable to determine \{\$ substition in ".
                                    hamleStrVar::getCodeSnippet($s));
    }
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
  
  static function arrayToPHP($var) {
    if (is_array($var)) {
        $code = 'Array(';
        foreach ($var as $key => $value) {
            $code .= "'".addslashes($key)."'=>".self::arrayToPHP($value).',';
        }
        $code = chop($code, ','); //remove unnecessary coma
        $code .= ')';
        return $code;
    } else {
        if (is_bool($var)) {
            return ($var ? 'TRUE' : 'FALSE');
        } else {
            return "'".addslashes($var)."'";
        }
    }
    //  return str_replace(array("\n", "\r"),"",var_export($a, true));
  }
  
  static function getTIC($s) {
    $m = $tic = array();
    if(preg_match('/^[a-zA-Z0-9\_]+/', $s, $m))
      $tic['type'] = $m[0];
    preg_match_all(hamleStrVar_model::REGEX_SELECTOR,$s,$m);
    foreach($m[0] as $n) {
      if($n[0] == ".")
        $tic['class'][] = substr($n,1);
      if($n[0] == "#")
        $tic['id'] = substr($n,1);
    }
    return $tic;
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
    return hamleStrVar::arrayToPHP($this->s);
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
    return "hamleScope::get()->hamleGet(".hamleStrVar::arrayToPHP($this->var).")";
  }
}

abstract class hamleStrVar_intChild implements hamleStrVar_int {
  protected $relModel;
  protected $var = "";
  function addRel(hamleStrVar_int $model) {
    $this->relModel = $model;
  }
  function relPHP($out) {
    if($this->relModel)
      $out = $out.$this->relModel->toPHP();
    if($this->var)
      $out = $out."->hamleGet(".hamleStrVar::arrayToPHP($this->var).")";
    return $out;
  }
  function getVar($var) {
    $this->var = $var;
  }

}

class hamleStrVar_model extends hamleStrVar_intChild {
  const REGEX_SELECTOR = '/[#\.\^\:][a-zA-Z0-9\-\_]*/m';
  protected $typeId = array(), $typeTags = array();
  protected $limit, $offset, $sortBy = "", $sortDir;
  function __construct($selector) {
    $this->limit = $this->offset = 0;
    $this->sortDir = hamle::SORT_NATURAL;
    $type = "";
    $selectors = explode(",",$selector);
    foreach($selectors as $idclass) {
      $set = false;
      if(preg_match('/^[a-zA-Z0-9\_]+/',$idclass, $m))
        $type = $m[0];
      else
        $type = "*";
      preg_match_all(self::REGEX_SELECTOR, $idclass, $m);
      if(isset($m[0])) foreach($m[0] as $s) {
        if($s[0] == "#") {
          $this->typeId[$type] = substr($s,1);
          $set = true;
        }
        if($s[0] == ".") {
          $this->typeTags[$type][] = substr($s,1);
          $set = true;
        }
        if($s[0] == ":" && preg_match('/^\:(?:([0-9]+)\-)?([0-9]+)$/',$s,$mLimit)) {
          $this->limit = $mLimit[2];
          $this->offset = $mLimit[1]?$mLimit[1]:0;
        }
        if($s[0] == "^") {
          if(strlen($s) == 1) $this->sortDir = hamle::SORT_RANDOM;
          elseif($s[1] == "-") {
            $this->sortDir = hamle::SORT_DESCENDING;
            $this->sortBy = substr($s,2);
          } else {
            $this->sortDir = hamle::SORT_ASCENDING;
            $this->search = substr($s,1);
          }
        }
      }
      if(!$set && $type != "*")
        $this->typeTags[$type] = array();
    }
    if(!($this->typeId xor $this->typeTags) || 
              (isset($this->typeId['*']) && count($this->typeId) > 1)) {
        throw new hamleEx_ParseError("Unable to search by both id, and tags in".
                                    hamleStrVar::getCodeSnippet($selector).
                          hamleStrVar::arrayToPHP($this->typeTags, true).
                          hamleStrVar::arrayToPHP($this->typeId, true));
              }
  }
  function toHTML() {
    return "<?=".$this->toPHP()."?>";
  }
  function toPHP() {
    $out = "";
    $flags = ",{$this->sortDir},".hamleStrVar::arrayToPHP($this->sortBy).
                                ",{$this->limit},{$this->offset}";
    if($this->typeId)
      if(isset($this->typeId['*']))
        $out = "hamleRun::modelID(".hamleStrVar::arrayToPHP($this->typeId['*'])."$flags)";
      else
        $out = "hamleRun::modelTypeID(".hamleStrVar::arrayToPHP($this->typeId)."$flags)";
    elseif($this->typeTags)
        $out = "hamleRun::modelTypeTags(".hamleStrVar::arrayToPHP($this->typeTags)."$flags)";
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
    $out = "hamleScope::get(".hamleStrVar::arrayToPHP($this->id).")";
    return $this->relPHP($out);
  }
}
class hamleStrVar_relfilt implements hamleStrVar_int {
  protected $typeTags = array(), $rel;
  protected $limit = 0, $offset = 0, $sortBy = "", $sortDir = 0;
  function __construct($rel, $filter) {
    $this->rel = $rel;
    $filters = explode(",",$filter);
    foreach($filters as $filter) {
      $type = ""; $tags = array();
      preg_match_all(hamleStrVar_model::REGEX_SELECTOR, $filter, $m);
      if(isset($m[0])) foreach($m[0] as $s) {
        if($s[0] == "#")
            throw new hamleEx_ParseError("Unable to specify child by ID in ".
                                   hamleStrVar::getCodeSnippet($s));
        if($s[0] == ".") $tags[] = substr($s,1);
        if($s[0] == ":" && preg_match('/^\:(?:([0-9]+)\-)?([0-9]+)$/',$s,$mLimit)) {
          $this->limit = $mLimit[2];
          $this->offset = $mLimit[1]?$mLimit[1]:0;
        }
        if($s[0] == "^") {
          if(strlen($s) == 1) $this->sortDir = hamle::SORT_RANDOM;
          elseif($s[1] == "-") {
            $this->sortDir = hamle::SORT_DESCENDING;
            $this->sortBy = substr($s,2);
          } else {
            $this->sortDir = hamle::SORT_ASCENDING;
            $this->search = substr($s,1);
          }
        }
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
    throw new hamleEx_Unsupported("Unable to do this");
  }
  function toPHP() {
    $flags = ",{$this->sortDir},".hamleStrVar::arrayToPHP($this->sortBy).
                                ",{$this->limit},{$this->offset}";
    $tags = hamleStrVar::arrayToPHP($this->typeTags);
    return "->hamleRel({$this->rel},$tags$flags)";
  }
}

class hamleStrVar_Comp implements hamleStrVar_int {
  protected $param1, $param2, $operator;
  const REGEX_COMP_OPER = '(equals|less|greater|has|starts|contains|ends)';
  function __construct(hamleStrVar_int $p1, hamleStrVar_int $p2, $operator) {
    $this->param1 = $p1;
    $this->param2 = $p2;
    $this->operator = $operator;
  }
  function toPHP() {
    $p1 = $this->param1->toPHP();
    $p2 = $this->param2->toPHP();
    switch($this->operator) {
      case "equals":
        return $p1." == ".$p2;
      case "less":
        return $p1." < ".$p2;
      case "greater":
        return $p1." > ".$p2;
      case "has":
        return "in_array($p2, $p1)";
      case "starts":
        return "strpos($p1, $p2) === 0";
      case "contains":
        return "strpos($p1, $p2) !== FALSE";
      case "ends":
        return "substr($p1, -strlen($p2)) === $p2";
      case "or":
      case "and":
      case "xor":
        throw new hamleEx_Unimplemented("OR/AND/XOR Unimplmented at this time");
        return "($p1) OR ($p2)";
        return "($p1) AND ($p2)";
        return "($p1) XOR ($p2)";
    }
    return "";
  }
  function toHTML() {
    throw new hamleEx_Unimplemented("Unable to output comparison results to HTML");
  }
}