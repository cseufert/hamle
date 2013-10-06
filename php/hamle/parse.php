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
  protected function loadLines($s) {
    $this->lines = explode("\n", str_replace("\r","",$s));
    $this->lineCount = count($this->lines);
    $this->lineNo = 0;
  }
  
  function parseSnip($s) {
    //save root tags
    $roots = $this->root; $this->root = array();
    $this->loadLines($s);
    $this->procLines();
    foreach($this->root as $tag) {
      if(! $tag instanceOf hamleTag_Snippet)
        throw new hamleEx_ParseError("Illegal Tag in snippet file, parent most tag has to be |snippet");
      foreach($roots as $root)
        $tag->apply($root);
    }
    $this->root = $roots;
  }
  function oldParseSnip($s) {
    echo "Parsing Snipppet\n";
    $m = array(); $tags = array();
    $this->loadLines($s);
    while($this->lineNo < $this->lineCount) {
      if(!preg_match('/^( *)\\|snippet (\w+) (.*)$/', $this->lines[$this->lineNo],$m))
        throw new hamleEx_ParseError("Unable to parse Snippet $name, on line {$this->lineNo}. Expecting |snippet not '{$this->lines[$this->lineNo]}'");
      $indent = strlen($m[1]);
      $mode = $m[2];
      if(!in_array($mode, array("prepend","append","replace")))
        throw new hamleEx_ParseError("Unknown snippet type $mode");
      $path = explode(" ",$m[3]);
      if(!$path) throw new hamleEx_ParseError("No path is specified for snippet on line {$this->lineNo}.");
      foreach($path as $k=>$p)
        $path[$k] = $this->getTIC($p);
      $block = $this->consumeBlock(strlen($m[1]));
      foreach($this->root as $tag)
        $tags = array_merge($tags, $tag->find($path));
      $lineState = array($this->lines, $this->lineCount, $this->lineNo, $this->root, $this->indents);
      foreach($tags as $tag) {
        $this->lines = $block;
        $this->lineCount = count($block);
        $this->lineNo = 0;
        $this->root = $tag;
        $this->indents = array();
        $this->indentLevel($indent);
        $this->procLines($heir = array($tag));
      }
      list($this->lines, $this->lineCount, $this->lineNo, $this->root, $this->indents) = $lineState;
//      var_dump($tags[0]->render());
//      throw new hamleEx(print_r($tags[0]->renderStTag(),true));
      $this->lineNo++;
    }
  }
 
  /**
   * Parse HAMLE template, from a string
   * @param string $s String to parse
   * @return string Parsed HAMLE as HTML
   */
  function str($s) {
    $this->init();
    $this->loadLines($s);
    $this->procLines();
  }

  function procLines() {
    while($this->lineNo < $this->lineCount) {
      $line = $this->lines[$this->lineNo];
      if(trim($line)) if(preg_match(self::REGEX_PARSE_LINE, $line, $m)) {
        unset($m[0]);
        if(FALSE !== strpos($m[1], "\t"))
          throw new hamleEx_ParseError("Tabs are not supprted in templates at this time");
        $indent = strlen($m[1]);
        $tag = isset($m[2])?$tag = $m[2]:""; 
        $classid = isset($m[3])?$m[3]:""; 
        $params = str_replace('\\&','%26',isset($m[4])?$m[4]:"");
        $textcode = isset($m[5])?$m[5]:"";
        $text = isset($m[8])?$m[8]:"";
        $code = isset($m[6])?$m[6]:"";
        //var_dump($m);
        switch(strlen($code)?$code[0]:($textcode?$textcode:"")) {
          case "|": //Control Tag
            if($code == "|snippet")
              $hTag = new hamleTag_snippet ($text);
            else {
              $hTag = new hamleTag_Ctrl(substr($code,1));
              $hTag->setVar($text);
            }
            break;
          case ":": //Filter Tag
            $hTag = new hamleTag_Filter(substr($code,1));
            $hTag->addContent($text);
            foreach($this->consumeBlock($indent) as $l)
              $hTag->addContent($l,hamleStrVar::TOKEN_CODE);
            break;
          case "_": //String Tag
            $hTag = new hamleTag_String();
            $hTag->addContent($text);
            break;
          case "/": // Comment
          case "//":
            $hTag = new hamleTag_Comment($textcode);
            $hTag->addContent($text);
            foreach($this->consumeBlock($indent) as $l)
              $hTag->addContent($l,hamleStrVar::TOKEN_CODE);
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
  }
  function output() {
    $out = "";
    foreach($this->root as $tag)
      $out .= $tag->render();
    return $out;

  }
  function consumeBlock($indent) {
    $out = array();
    while($this->lineNo + 1 < $this->lineCount &&
            ( !trim($this->lines[$this->lineNo+1]) ||
        preg_match('/^(\s){'.$indent.'}((\s)+[^\s].*)$/', 
                          $this->lines[$this->lineNo+1], $m))) {
      if(trim($this->lines[$this->lineNo+1]))
        $out[] = $m[2];
      $this->lineNo++;
    }
    return $out;
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
