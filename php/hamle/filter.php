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
class hamleFilter_html extends hamleFilter {
  static function stTag() { return "<!-- HTML Fitler -->"; }
  static function ndTag() { return "<!-- HTML Fitler End -->"; }
  static function filterText($s) {
    return "$s";
  }
}
/**
 * HAMLE CSS Filter
 */
class hamleFilter_css extends hamleFilter {
  static function stTag() { return "<style type=\"text/css\">"; }
  static function ndTag() { return "</style>"; }
  static function filterText($s) {
    return $s;
  }
}
class hamleFilter_style extends hamleFilter_css { }
class hamleFilter_sass extends hamleFilter_css {
  static function filterText($s) {
    $as = explode("\n",$s);
    $indent = -1;
    foreach($as as $line)
      if(preg_match('/^(\s+).*$/',$line, $m)) {
        $lnInd = strlen($m[1]);
        if($indent < 0) $indent = $lnInd;
        $indent = min($indent, $lnInd);
    }
    foreach($as as $k=>$v)
      $as[$k] = substr($v, $indent);
    $s = implode("\n", $as);

    require_once ME_DIR."/lib/phpsass/SassParser.php";
    $sp = new SassParser(array("cache"=>FALSE,
        "style"=>stdConf::get("me.developer")?SassRenderer::STYLE_EXPANDED:SassRenderer::STYLE_COMPRESSED,
        "syntax"=>SassFile::SASS, 'debug'=>TRUE));
    $tree = $sp->toTree($s);
    $out = $tree->render();
    $pad = str_pad("",$indent," ");
    return $pad.str_replace("\n","\n$pad",trim($out));
    return "/*\n$s*/\n".$tree->render();
  }
}