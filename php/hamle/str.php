<?php

/**
 * HAMLE String Object
 *
 * @author Chris Seufert <chris@seufert.id.au>
 * @package hamle
 */

class hamleStr {
  const REGEX_BARDOLLAR = '/{\$(.*?)}/';
  const REGEX_DOLLAR = '/(?:^|[^\\\\])\$([a-zA-Z0-9_]+)/';
  
  static function pass($s, $dollarOnly = false) {
    $out = preg_replace_callback(self::REGEX_BARDOLLAR,array(get_class(),"barDollar"),$s);
    if($dollarOnly)
      $out = preg_replace_callback(self::REGEX_DOLLAR, array(get_class(),"dollar"),$out);
    return stripslashes($out);
  }
  
  /**
   * Replace Handbar Dollar within a string with php code to product output
   * @param type $m
   * @return type
   */
  static function barDollar($m) {
    $o = addslashes($m[1]);
    return "<?=hamleScope::eval(\"$o\")?>";
  }
  
  static function dollar($m) {
    return "<?=hamleScope::getVal('".$m[1]."')?>";
  }
  
  static function passStr($s) {
    //$s = preg_replace_callback(self::REGEX_BARDOLLAR,array(get_class(),"barDollarStr"),$s);
    return preg_replace_callback(self::REGEX_DOLLAR, array(get_class(),"dollarStr"),$s);
  }
  static function dollarStr($m) {
    return "\".hamleScope::getVal('".$m[1]."').\"";
  }
  
  static function native($s) {
    $code = "";
    if(preg_match('/^\$\(([a-zA-Z0-9\.#,]+)?(?: *([\>]) *([a-zA-Z0-9\.#,]+))?\)\s*$/',$s, $m)) {
      if(isset($m[1]) && $m[1])
        $code .= 'hamle::modelFind("'.  addslashes($m[1]).'")';
      else
        $code .= "hamleScope::get()";
      if(isset($m[2]) && $m[2] = ">") {
        $code .= '->hamleChild("'.  addslashes($m[3]).'")';
      }
    } elseif(preg_match('/^\$\[([0-9]+)\](.*)$/', $s, $m)) {
      $code .= 'hamleScope::get("'.addslashes($m[1]).'")';
      if($m[2]) {
        $code .= $m[2];
      }
    } else {
      throw new hamleEx_ParseError("Unable to pass expression \"$s\"");
    }
    return $code;
    
  }
  
  static function parseIDClass($s, &$idclass = array()) {
    $m = array();
    preg_match_all('/[#\.][a-zA-Z0-9\-\_]+/m', $s, $m);
    if(isset($m[0])) foreach($m[0] as $s) {
      if($s[0] == "#") $idclass['id'] = substr($s,1);
      if($s[0] == ".") $idclass['class'][] = substr($s,1);
    }
    if(preg_match('/^[a-zA-Z0-9\_]+/',$s, $m))
      return $m[0];
    return "";
  }
}
