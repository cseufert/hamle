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
   * @param array Array of indent levels
   */
  protected $indents;
  /**
   * @var array Array of Root Document Tags
   */
  protected $root;
  /**
   * @var string Each Line read in from template
   */
  protected $lines;
  /**
   * Regex for parsing each HAMLE line
   */
  const REGEX_PARSE_LINE = '/^(\s*)(?:(?:([a-zA-Z0-9]*)((?:[\.#][\w\-\_]+)*)(\[(?:[^\\\\\\]]*?(?:\\\.)*?)+\])?)|([_\/][\/]?)|([\|:\$]\w+)|({?\$[^}]+}?)|)(?: (.*))?$/';
  /**
   * @var int Current Line Number
   */
  protected $lineNo;
  /**
   * @var Total Lines in File 
   */
  protected $lineCount;
  
  function __construct() {
    $this->init();
  }
  /**
   * Clear Lines, and Line Number, so if output is 
   * called, no output will be produced
   */
  protected function init() {
    $this->lines = array();
    $this->lineNo = 0;
    $this->lineCount = 0;
    $heir = array();
    $this->root = array();
  }
  
  /**
   * Parse HAMLE template, from a string
   * @param string $s String to parse
   * @return string Parsed HAMLE as HTML
   */
  function str($s) {
    $this->init();
    $this->lines = explode("\n", str_replace("\r","",$s));
    $this->lineCount = count($this->lines);
    while($this->lineNo < $this->lineCount) {
      $line = $this->lines[$this->lineNo];
      if(trim($line)) if(preg_match(self::REGEX_PARSE_LINE, $line, $m)) {
        unset($m[0]);
        $indent = strlen($m[1]);
        if(FALSE !== strpos($indent, "\t"))
          throw new hamleEx_ParseError("Tabs are not supprted in templates at this time");
        $tag = isset($m[2])?$tag = $m[2]:""; 
        $classid = isset($m[3])?$m[3]:""; 
        $params = str_replace('\\&','%26',isset($m[4])?$m[4]:"");
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
            $this->consumeBlock($hTag, $indent);
            break;
          case "_": //String Tag
            $hTag = new hamleTag_String();
            $hTag->addContent($text);
            break;
          case "/": // Comment
          case "//":
            $hTag = new hamleTag_Comment($textcode);
            $hTag->addContent($text);
            $this->consumeBlock($hTag, $indent);
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
          $this->root[] = $hTag;
      } else 
        throw new hamleEx_ParseError("Unable to parse line $this->lineNo\n\"$line\"");
      $this->lineNo++;
    }
    $out = "";
    foreach($this->root as $tag)
      $out .= $tag->render();
    return $out;
  }
  
  function consumeBlock($tag, $indent) {
    while($this->lineNo + 1 < $this->lineCount &&
            ( !trim($this->lines[$this->lineNo+1]) ||
        preg_match('/^(\s){'.$indent.'}((\s)+[^\s].*)$/', 
                          $this->lines[$this->lineNo+1], $m))) {
      if(trim($this->lines[$this->lineNo+1]))
        $tag->addContent($m[2],hamleStrVar::TOKEN_CODE);
      $this->lineNo++;
    }
  }
  
  function indentLevel($indent) {
    if(!isset($this->indents)) $this->indents = array();
    if($indent == 0) {
      $this->indents = array(0=>0); // Key = indent, Value = Depth
      return 0;
    }
    foreach($this->indents as $k=>$v) {
      if($v == $indent) {
         array_slice($this->indents,0,$k+1);
        return $k;
      }
    }
    $this->indents[] = $indent;
    return max(array_keys($this->indents));
  }

  function getLineNo() {
    return $this->lineNo;
  }
}
