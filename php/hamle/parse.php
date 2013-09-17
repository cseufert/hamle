<?php

/**
 * HAML Enhanced - Parser, parses hamle files, 
 * executes it and leaves a .php file to cache it
 *
 * @author Chris Seufert <chris@seufert.id.au>
 * @package hamle
 */
class hamleParse {
  /**
   * Parse a File
   * @param string $file Filename to parse
   * @return string Parsed HAMLE as HTML
   */
  static $indents;

  static function file($file) {
    return self::str(file_get_conents($file));
  }
  
  /**
   * Parse a string
   * @param string $s String to parse
   * @return string Parsed HAMLE as HTML
   */
  static function str($s) {
    $lines = explode("\n", str_replace("\r","",$s));
    $rx = '/^(\s*)(?:(?:([a-zA-Z0-9]*)((?:[\.#]\w+)*)(\[(?:[^\\\]]*(?:\\.)*)+\])?)|([_\/][\/]?)|([\|:\$]\w+)|({?\$[^}]+}?)|)(?: (.*))?$/';
    $heir = array();
    $lineCount = count($lines);
    $lineNo = 0;
    while($lineNo < $lineCount) {
      $line = $lines[$lineNo];
      if(trim($line)) if(preg_match($rx, $line, $m)) {
        unset($m[0]);
        $indent = strlen($m[1]);
        $tag = isset($m[2])?$tag = $m[2]:""; 
        $classid = isset($m[3])?$m[3]:""; 
        $params = isset($m[4])?$m[4]:"";
        $text = isset($m[8])?$m[8]:"";
        $code = isset($m[6])?$m[6]:"";
        //var_dump($m);
        switch(strlen($code)?$code[0]:"") {
          case "|":
            $hTag = new hamleTag_Ctrl(substr($tag,1));
            $hTag->addContent($text);
            break;
          case ":":
            $hTag = new hamleTag_Filter(substr($code,1));
            $hTag->addContent($text);
            while($lineNo + 1 < $lineCount && 
                    preg_match('/^(\s){'.$indent.'}((\s)+[^\s].*)$/', 
                                      $lines[$lineNo+1], $m)) {
              $hTag->addContent($m[2]);
              $lineNo++;
            }
            break;
          default:
            $hTag = new hamleTag_HTML($tag, $classid, $params);
            $hTag->addContent($text);
            break;
        }
        $i = self::indentLevel($indent);
        $heir[$i] = $hTag;
        if($indent > 0)
          $heir[$i - 1]->addChild($hTag);
        $lineNo++;
      } else 
        throw new hamlEx_ParseError("Unable to parse line $l\n\"$line\"");
    }
    return $heir[0]->render();

    
  }
  
  
  static function indentLevel($indent) {
    if(!isset(self::$indents)) self::$indents = array();
    if($indent == 0) {
      self::$indents = array(0=>0); // Key = indent, Value = Depth
      return 0;
    }
    foreach(self::$indents as $k=>$v) {
      if($v == $indent) {
         array_slice(self::$indents,0,$k+1);
        return $k;
      }
    }
    self::$indents[] = $indent;
    return max(array_keys(self::$indents));
  }
  
}
