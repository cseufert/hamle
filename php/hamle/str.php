<?php

/**
 * HAMLE String Object
 *
 * @author Chris Seufert <chris@seufert.id.au>
 * @package hamle
 */

class hamleStr {
  const REGEX_BARDOLLAR = '/{\$([a-zA-Z0-9_]+)(.*?)}/';
  const REGEX_DOLLAR = '/(^|[^\\\\])\$([a-zA-Z0-9_]+)/';
  
  static function pass2($s, $dollarOnly = false) {
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
    if($m[2]) throw new hamleEx_ParseError("Unsuppored access method");
    return "<?=hamleScope::getVal(\"".$m[1]."\")?>";
  }
  
  static function dollar($m) {
    return $m[1]."<?=hamleScope::getVal('".$m[2]."')?>";
  }
  
  static function passStr($s) {
    //$s = preg_replace_callback(self::REGEX_BARDOLLAR,array(get_class(),"barDollarStr"),$s);
    return preg_replace_callback(self::REGEX_DOLLAR, array(get_class(),"dollarStr"),$s);
  }
  static function dollarStr($m) {
    return $m[1]."\".hamleScope::getVal('".$m[2]."').\"";
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
