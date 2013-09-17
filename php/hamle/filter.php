<?php

/**
 * HAMLE Exception base class
 *
 * @author Chris Seufert <chris@seufert.id.au>
 * @package hamle
 * 
 */

abstract class hamleFilter {
  static function stTag() { return ""; }
  static function filterText($s) {return $s;}
  static function ndTag() { return ""; }

}

/**
 * HAMLE Javascript Fiter
 */
class hamleFilter_javascript extends hamleFilter {
  static function stTag() { return "<script type=\"text/javascript\">"; }
  static function ndTag() { return "</script>"; }
  static function filterText($s) {
	  return "/*<![CDATA[*/\n" .
	  	/*preg_replace(HamlParser::MATCH_INTERPOLATION, '<?php echo \1; ?>', $text)*/
	  	$s."/*]]>*/";
  }
}
/**
 * HAMLE CSS Filter
 */
class hamleFilter_css extends hamleFilter {
  static function stTag() { return "<style>"; }
  static function ndTag() { return "</style>"; }
  static function filterText($s) {
    return $s;
  }
}
class hamleFilter_style extends hamleFilter_css { }
