<?php

/**
 * HAMLE String Conversion Library
 * 
 * @author Chris Seufert <chris@seufert.id.au>
 * @package hamle
 */

class hamleString {
  const TOKEN_CONTROL = 0x07;
  const TOKEN_HTML    = 0x06;
  const TOKEN_CODE    = 0x04;
  
  const REGEX_HTML = '/(\\$[a-zA-Z0-9\\_]+)|({\\$.*?})/';
  const REGEX_CODE = '//';
  
  const FIND_DOLLARFUNC = 0x01;
  const FIND_DOLLARVAR = 0x02;
  const FIND_BARDOLLAR = 0x04;

    /**
     * @var hamleString[] Array of Child String Objects
     */
    protected $nodes;

  function __construct($s, $mode = self::TOKEN_HTML) {
    $m = array(); $pos = 0; $this->nodes = array();
    $rFlag = PREG_OFFSET_CAPTURE + PREG_SET_ORDER;
    if(!trim($s)) return;
    if($mode == self::TOKEN_CONTROL) {
      $this->nodes[] = new hamleString_Complex(trim($s));
      return;
    }
    preg_match_all(self::REGEX_HTML, $s, $m, $rFlag);
    foreach($m as $match) {
      if($mode & self::FIND_BARDOLLAR && isset($match[2])) {
        if($match[2][1] != $pos)
          $this->nodes[] = new hamleString_Plain(
                                    substr($s, $pos, $match[2][1] - $pos));
        $this->nodes[] = new hamleString_Complex(substr($match[2][0],1,-1));
        $pos = $match[2][1] + strlen($match[2][0]);
      } elseif($mode & self::FIND_DOLLARVAR) {
        if($match[1][1] > 0 && $s[$match[1][1]-1] == '\\') continue;
        if($match[1][1] != $pos)
          $this->nodes[] = new hamleString_Plain(
                                    substr($s, $pos, $match[1][1] - $pos));
        $this->nodes[] = new hamleString_SimpleVar($match[1][0]);
        $pos = $match[1][1] + strlen($match[1][0]);
      }
    }
    if($pos != strlen($s))
      $this->nodes[] = new hamleString_Plain(substr($s, $pos), $mode);
  }
  
  function toHTML() {
    $out = array();
    foreach($this->nodes as $string)
      $out[] = $string->toHTML();
    return implode("",$out);
  }
  
  function toPHP() {
    $out = array();
    foreach($this->nodes as $string)
      $out[] = $string->toPHP();
    return implode(".",$out);
  }

  function doEval() {
    return eval('return '.$this->toPHP().';');
  }
    
  static function varToCode($var) {
    if (is_array($var)) {
        $code = array();
        foreach ($var as $key => $value)
            $code[] = self::varToCode($key)."=>".self::varToCode($value);
        return 'array('.implode(",",$code).')'; //remove unnecessary coma
    } elseif(is_bool($var)) {
        return ($var ? 'TRUE' : 'FALSE');
    } elseif(is_int($var) || is_float($var) || is_numeric($var)) {
        return $var;
    } else {
        return "'".str_replace(array('$',"'"),array('\\$',"\\'"),$var)."'";
    }
  }

}

class hamleString_Plain extends hamleString {
  protected $s;
  protected $type;
  function __construct($s, $type = self::TOKEN_HTML) {
    $this->s = str_replace('\\$',"$",$s);
    $this->type = $type;
  }
  function toPHP() {
    return hamleString::varToCode($this->s);
  }
  function toHTML() {
    if($this->type == self::TOKEN_CODE)
      return $this->s;
    return htmlspecialchars($this->s);
  }
}

class hamleString_SimpleVar extends hamleString {
  protected $var;
  function __construct($s) {
    $this->var = substr($s,1);
  }
  function toHTML() {
    return "<?=".$this->toPHP()."?>";
  }
  function toPHP() {
    return "hamleScope::get()->hamleGet(".hamleString::varToCode($this->var).")";
  }
}

class hamleString_Complex extends hamleString {
  protected $func;
  protected $sel = null;
  function __construct($s) {
    $s = explode("->",$s);
    if(!$s[0]) throw new hamleEx_ParseError("Unable to parse Complex Expression");
    if($s[0][1] == "(")
      $this->func = new hamleString_Func($s[0]);
    elseif($s[0][1] == "[")
      $this->func = new hamleString_Scope($s[0]);
    else
      $this->func = new hamleString_SimpleVar($s[0]);
    array_shift($s);
      $this->sel = $s;
  }
  function toHTML() {
    return "<?=".$this->toPHP()."?>";
  }
  function toPHP() {
    if($this->sel) {
      $sel = array();
      foreach($this->sel as $s)
        $sel[] = "hamleGet('$s')";
      return $this->func->toPHP()."->".implode('->',$sel);
    } else
      return $this->func->toPHP();
  }

}

class hamleString_Select extends hamleString_Complex {
  protected $key;
  function __construct($s) {
    $s = explode("->",$s,2);
    $this->key = $s[0];
    if(count($s) > 1)
      $this->sel = $s[1];
  }
}

class hamleString_Func extends hamleString_SimpleVar {
  protected $sub = null;
  protected $scope = false;
  protected $filt;
  protected $sortlimit;
  const REGEX_FUNCSEL = '[a-zA-Z0-9\.,#_:\\^\\-]';
  function __construct($s) {
    $m = array();
    if(!preg_match('/^\$\(('.self::REGEX_FUNCSEL.'*)(.*)\)$/', $s, $m))
      throw new hamleEx_ParseError("Unable to read \$ func in ($s)");
    if(trim($m[2]))
      $this->sub = new hamleString_FuncSub($m[2]);
    if(!trim($m[1])) { 
      $this->scope = true;
      return;
    }
    $this->sortlimit = $this->attSortLimit($m[1]);
    $this->filt = $this->attIdTag($m[1]);
  }
  
  function attIdTag(&$s) {
    $m = array();
    $att = array('id'=>array(), 'tag'=>array());
    foreach(explode(",",$s) as $str) {
      if(preg_match('/^[a-zA-Z0-9\\_]+/', $str, $m)) $type = $m[0];
      else $type = "*";
      if(preg_match('/#([a-zA-Z0-9\_]+)/', $str, $m)) $att['id'][$type][] = $m[1];
      elseif(preg_match('/\\.([a-zA-Z0-9\_]+)/', $str, $m))
                                  $att['tag'][$type][] = $m[1];
      else $att['tag'][$type] = array();
    }
    //var_dump($att);
    if(!(count($att['id']) xor count($att['tag'])))
      throw new hamleEx_ParseError("Only tag, type or id can be combined");
    return $att;
  }
  
  function attSortLimit(&$s) {
    $att = array('limit'=>0,'offset'=>0,'dir'=>0,'field'=>'');
    $m = array();
    if(preg_match('/:(?:([0-9]+)\-)?([0-9]+)/',$s,$m)) {
      $att['limit'] = $m[2];
      $att['offset'] = $m[1]?$m[1]:0;
    }
    if(preg_match('/\\^(-?)([a-zA-Z0-9\_]*)/', $s, $m)) {
      if($m[2]) {
        $att['field'] = $m[2];
        if($m[1] == "-") $att['dir'] = hamle::SORT_DESCENDING;
        else $att['dir'] = hamle::SORT_ASCENDING;
      } else $att['dir'] = hamle::SORT_RANDOM;
    }
    return $att;
  }
  function toPHP() {
    $limit = $this->sortlimit['dir'].",".
            hamleString::varToCode($this->sortlimit['field']).",".
            $this->sortlimit['limit'].",".$this->sortlimit['offset'];
    $sub = $this->sub?"->".$this->sub->toPHP():"";
    if($this->scope) return "hamleScope::get(0)$sub";
    if(count($this->filt['tag']))
      return "hamleRun::modelTypeTags(".
                hamleString::varToCode($this->filt['tag']).",$limit)$sub";
    if(count($this->filt['id']))
      if(isset($this->filt['id']['*']) && count($this->filt['id']['*']) == 1)
        return "hamleRun::modelId(".
                hamleString::varToCode(current($this->filt['id']['*'])).
                                                                ",$limit)$sub";
      else
        return "hamleRun::modelTypeId(".
                hamleString::varToCode ($this->filt['id']).",$limit)$sub";
    return "";
  }
  
  function toHTML() { throw new 
          hamleEx_ParseError("Unable to use Scope operator in HTML Code"); }
}

class hamleString_FuncSub extends hamleString_Func {
  protected $dir;
  function __construct($s) {
    $m = array();
    if(!preg_match('/^ +([><]) +('.self::REGEX_FUNCSEL.'+)(.*)$/', $s, $m))
      throw new hamleEx_ParseError("Unable to read \$ sub func in ($s)");
    if($m[1] == "<") $this->dir = hamle::REL_PARENT;
    elseif($m[1] == ">") $this->dir = hamle::REL_CHILD;
    else $this->dir = hamle::REL_ANY;
    $this->sortlimit = $this->attSortLimit($m[2]);
    $this->filt = $this->attIdTag($m[2]);
    if($this->filt['id']) throw new hamleEx_ParseError("Unable to select by id");
    if(trim($m[3]))
      $this->sub = new hamleString_FuncSub($m[3]);
  }
  function toPHP() {
    $limit = $this->sortlimit['dir'].",".
            hamleString::varToCode($this->sortlimit['field']).",".
            $this->sortlimit['limit'].",".$this->sortlimit['offset'];
    $sub = $this->sub?"->".$this->sub->toPHP():"";
    return "hamleRel(".$this->dir.",".
              hamleString::varToCode($this->filt['tag']).",$limit)$sub";
  }
}

class hamleString_Scope extends hamleString_SimpleVar {
  protected $scope = 0;
  function __construct($s) {
    $m = array();
    //var_dump($s);
    if(!preg_match('/\$\[(-?[0-9]+)\]/', $s, $m))
      throw new hamleEx_ParseError("Unable to match scope");
    $this->scope = $m[1];
  }
  function toPHP() {
    return "hamleScope::get(".hamleString::varToCode($this->scope).")";
  }
  function toHTML() { throw new 
          hamleEx_ParseError("Unable to use Scope operator in HTML Code"); }
}

class hamleString_FormField extends hamleString {
  protected $var;
  function __construct($var) {
    $this->var = $var;
  }
  function toPHP() {
    return '$form->getField('.hamleString::varToCode($this->var).')->getValue()';
  }
  function toHTML() {
    return '<?='.$this->toPHP().'?>';
  }
}

