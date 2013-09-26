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
    $rx = '/^(\s*)(?:(?:([a-zA-Z0-9]*)((?:[\.#][\w\-\_]+)*)(\[(?:[^\\\]]*(?:\\.)*)+\])?)|([_\/][\/]?)|([\|:\$]\w+)|({?\$[^}]+}?)|)(?: (.*))?$/';
    $heir = array();
    $root = array();
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
        $textcode = isset($m[5])?$m[5]:"";
        $text = isset($m[8])?$m[8]:"";
        $code = isset($m[6])?$m[6]:"";
        //var_dump($m);
        switch(strlen($code)?$code[0]:($textcode?$textcode:"")) {
          case "|": //Control Tag
            $hTag = new hamleTag_Ctrl(substr($code,1));
            $hTag->setVar($text);
            break;
          case ":": //Filter Tag
            $hTag = new hamleTag_Filter(substr($code,1));
            $hTag->addContent($text);
            while($lineNo + 1 < $lineCount && ( !trim($lines[$lineNo+1]) ||
                    preg_match('/^(\s){'.$indent.'}((\s)+[^\s].*)$/', 
                                      $lines[$lineNo+1], $m))) {
              if(trim($lines[$lineNo+1]))
                $hTag->addContent($m[2]);
              $lineNo++;
            }
            break;
          case "_": //String Tag
            $hTag = new hamleTag_String();
            $hTag->addContent($text);
            break;
          case "/": // Comment
          case "//":
            $hTag = new hamleTag_Comment($textcode);
            $hTag->addContent($text);
            while($lineNo + 1 < $lineCount && ( !trim($lines[$lineNo+1]) ||
                    preg_match('/^(\s){'.$indent.'}((\s)+[^\s].*)$/', 
                                      $lines[$lineNo+1], $m))) {
              if(trim($lines[$lineNo+1]))
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
        else
          $root[] = $hTag;
      } else 
        throw new hamleEx_ParseError("Unable to parse line $lineNo\n\"$line\"");
      $lineNo++;
    }
    $out = "";
    //var_dump($root);
    foreach($root as $tag)
      $out .= $tag->render();
    return $out;
    //return $heir[0]->render();

    
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
