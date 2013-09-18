<?php

/**
 * HAMLE String Object
 *
 * @author Chris Seufert <chris@seufert.id.au>
 * @package hamle
 */

class hamleStr {
  static function pass($s, $dollarOnly = false) {
    $out = preg_replace_callback('/{\$(.*?)}/',array(get_class(),"barDollar"),$s);
    if($dollarOnly)
      $out = preg_replace_callback('/\$([a-zA-Z0-9]+)/', array(get_class(),"dollar"),$s);
    return $out;
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
    //var_dump($m);
    return "<?=hamleScope::getVal('$m[1]')?>";
  }
  
  static function native($s) {
    $code = "";
    if(preg_match('/^\$\(([a-zA-Z0-9\.#]+)\)(.*)$/',$s, $m)) {
      $code .= '$o = hamle::modelFind("'.  addslashes($m[1]).'");'."\n";
      if($m[2]) {
        $code .= '$o = $o'.$m[2].";\n";
      }
    }
    if(preg_match('/^\$\[([0-9]+)\](.*)$/', $s, $m)) {
      $code .= '$o = hamleScope::get("'.addslashes($m[1]).'");'."\n";
      if($m[2]) {
        $code .= '$o = $o'.$m[2].";\n";
      }
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
