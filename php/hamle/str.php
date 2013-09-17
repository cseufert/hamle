<?php

/**
 * HAMLE String Object
 *
 * @author Chris Seufert <chris@seufert.id.au>
 * @package hamle
 */

class hamleStr {
  static function pass($s, $dollarOnly = false) {
    $out = preg_replace_callback('/{(\$.*?)}/',array(get_class(),"barDollar"),$s);
    if($dollarOnly)
      $out = preg_replace_callback('/\$[a-zA-Z0-9]+/', array(get_class(),"dollar"),$s);
    return $out;
  }
  
  /**
   * Replace Handbar Dollar within a string with php code to product output
   * @param type $m
   * @return type
   */
  static function barDollar($m) {
    $o = addslashes($m[1]);
    return "<?=hamleScope::eval('$o')?>";
  }
  
  static function dollar($m) {
    var_dump($m);
    return "<?=hamleScope::getVal('$m[0]')?>";
  }
}
