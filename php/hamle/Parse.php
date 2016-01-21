<?php
/*
This project is Licenced under The MIT License (MIT)

Copyright (c) 2014 Christopher Seufert

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

 */
namespace Seufert\Hamle;
use Seufert\Hamle\Exception\ParseError;
use Seufert\Hamle\Parse\Filter as ParseFilter;
use Seufert\Hamle\Text;

/**
 * HAML Enhanced - Parser, parses hamle files,
 * executes it and leaves a .php file to cache it
 *
 * @author Chris Seufert <chris@seufert.id.au>
 * @package hamle
 */
class Parse {
  /**
   * @param array $indents Array of indent levels
   */
  protected $indents;
  /**
   * @var Tag[] Array of Root Document Tags
   */
  public $root;
  /**
   * @var array $lines Each Line read in from template
   */
  protected $lines;
  /**
   * Regex for parsing each HAMLE line
   */

  const REGEX_PARSE_LINE = <<<'ENDREGEX'
/^(\s*)(?:(?:([a-zA-Z0-9-]*)((?:[\.#!][\w\-\_]+)*)(\[(?:(?:\{\$[^\}]+\})?[^\\\]{]*?(?:\\.)*?(?:{[^\$])*?)+\])?)|([_\/]{1,2})|([\|:\$]\w+)|({?\$[^}]+}?)|)(?: (.*))?$/
ENDREGEX;

  /**
   * @var int Current Line Number
   */
  protected $lineNo;
  /**
   * @var int Total Lines in File
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
    $this->root = array();
  }

  protected function loadLines($s) {
    $this->lines = explode("\n", str_replace("\r", "", $s));
    $this->lineCount = count($this->lines);
    $this->lineNo = 0;
  }

  function parseFilter(ParseFilter $filter) {
    foreach ($this->root as $k => $tag) {
      $this->root[$k] = $filter->filterTag($tag);
    }
  }

  function parseSnip($s) {
    //save root tags
    /** @var Tag[] $roots */
    $roots = $this->root;
    $this->root = array();
    $this->loadLines($s);
    $this->procLines();
    $this->root = array_merge($roots, $this->root);
  }

  function applySnip() {
    /** @var Tag\Snippet[] $fwdSnip */
    $fwdSnip = array();
    /** @var Tag\Snippet[] $revSnip */
    $revSnip = array();
    /** @var Tag[] $roots */
    $roots = array();
    foreach ($this->root as $snip)
      if ($snip instanceOf Tag\Snippet) {
        if ($snip->getType() == "append") {
          array_unshift($revSnip, $snip);
        } else {
          $fwdSnip[] = $snip;
        }
      } else {
        $roots[] = $snip;
      }
    foreach ($fwdSnip as $snip)
      foreach ($roots as $root)
        $snip->apply($root);
    foreach ($revSnip as $snip)
      foreach ($roots as $root)
        $snip->apply($root);
    $this->root = $roots;
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
    /* @var $heir Tag[] Tag Heirachy Array */
    $heir = array();
    while ($this->lineNo < $this->lineCount) {
      $line = $this->lines[$this->lineNo];
      if (trim($line)) if (preg_match(self::REGEX_PARSE_LINE, $line, $m)) {
        if (FALSE !== strpos($m[1], "\t"))
          throw new ParseError("Tabs are not supported in templates at this time");
        $indent = strlen($m[1]);
        $tag = isset($m[2]) ? $tag = $m[2] : "";
        $classid = isset($m[3]) ? $m[3] : "";
        $params = str_replace(array('\[', '\]', '\\&'), array('[', ']', '%26'), isset($m[4]) ? $m[4] : "");
        $textcode = isset($m[5]) ? $m[5] : "";
        $text = isset($m[8]) ? $m[8] : "";
        $code = isset($m[6]) ? $m[6] : "";
        $i = self::indentLevel($indent);
        unset($m[0]);
        switch (strlen($code) ? $code[0] : ($textcode ? $textcode : "")) {
          case "|": //Control Tag
            if ($code == "|snippet")
              $hTag = new Tag\Snippet($text);
            elseif ($code == "|form")
              $hTag = new Tag\Form($text);
            elseif ($code == "|formhint")
              $hTag = new Tag\FormHint($text);
            elseif ($code == "|else") {
              $hTag = new Tag\Control(substr($code, 1), $heir[$i - 1]);
              $hTag->setVar($text);
            } else {
              $hTag = new Tag\Control(substr($code, 1));
              $hTag->setVar($text);
            }
            break;
          case ":": //Filter Tag
            $hTag = new Tag\Filter(substr($code, 1));
            $hTag->addContent($text, Text::TOKEN_CODE);
            foreach ($this->consumeBlock($indent) as $l)
              $hTag->addContent($l, Text::TOKEN_CODE);
            break;
          case "_": //String Tag
          case "__": //Unescape String Tag
            $hTag = new Tag\Text($textcode);
            $hTag->addContent($text);
            break;
          case "/": // HTML Comment
          case "//": // Non Printed Comment
            $hTag = new Tag\Comment($textcode);
            $hTag->addContent($text);
            foreach ($this->consumeBlock($indent) as $l)
              $hTag->addContent($l, Text::TOKEN_CODE);
            break;
          default:
            $attr = array();
            if(isset($params[0]) && $params[0] == "[") {
              $param = substr($params, 1, strlen($params) - 2);
              $param = str_replace('+','%2B', $param);
              parse_str($param, $attr);
            }
            $class = array(); $id = ""; $ref = "";
            preg_match_all('/[#\.!][a-zA-Z0-9\-\_]+/m', $classid, $cid);
            if (isset($cid[0])) foreach ($cid[0] as $s) {
              if ($s[0] == "#") $id = substr($s, 1);
              if ($s[0] == ".") $class[] = substr($s, 1);
              if ($s[0] == "!") $ref = substr($s, 1);
            }
            if($ref)
              $hTag = new Tag\DynHtml($tag, $class, $attr, $id, $ref);
            else
              $hTag = new Tag\Html($tag, $class, $attr, $id);
            $hTag->addContent($text);
            break;
        }
        $heir[$i] = $hTag;
        if ($i > 0)
          $heir[$i - 1]->addChild($hTag);
        else
          $this->root[] = $hTag;
      } else
        throw new ParseError("Unable to parse line {$this->lineNo}\n\"$line\"/" . preg_last_error());
      $this->lineNo++;
    }
  }

  function output() {
    $out = "<?php\nuse Seufert\\Hamle;\n?>";
    foreach ($this->root as $tag)
      $out .= $tag->render();
    return $out;

  }

  function consumeBlock($indent) {
    $out = array();
    $m = array();
    while ($this->lineNo + 1 < $this->lineCount &&
        (!trim($this->lines[$this->lineNo + 1]) ||
            preg_match('/^(\s){' . $indent . '}((\s)+[^\s].*)$/',
                $this->lines[$this->lineNo + 1], $m))) {
      if (trim($this->lines[$this->lineNo + 1]))
        $out[] = $m[2];
      $this->lineNo++;
    }
    return $out;
  }

  function indentLevel($indent) {
    if (!isset($this->indents)) $this->indents = array();
    if (!count($this->indents)) {
      $this->indents = array(0 => $indent);
      // Key = indent level, Value = Depth in spaces
      return 0;
    }
    foreach ($this->indents as $k => $v) {
      if ($v == $indent) {
        $this->indents = array_slice($this->indents, 0, $k + 1);
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
