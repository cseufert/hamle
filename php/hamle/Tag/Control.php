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
namespace Seufert\Hamle\Tag;
use Seufert\Hamle as H;
use Seufert\Hamle\Exception\ParseError;

/**
 * HAMLE Control Tag
 * Used for tags starting with the pipe (|) symbol
 */
class Control extends H\Tag {
  /**
   * @var string Variable passed to Control Tag
   */
  protected $var;
  protected $o, $else = false;
  static $instCount = 1;

  /**
   * Crate new Control Tag
   * @param string $tag Type of Control Tag
   * @param \Seufert\Hamle\Tag $parentTag
   * @throws ParseError
   */
  function __construct($tag, $parentTag = null) {
    parent::__construct();
    $this->o = "\$o" . self::$instCount++;
    $this->type = strtolower($tag);
    $this->var = "";
    if ($parentTag) {
      $elseTag = $parentTag->tags[count($parentTag->tags) - 1];
      if ($this->type == "else") {
        if (!$elseTag instanceOf H\Tag)
          throw new ParseError("Unable to use else here");
        if (!in_array($elseTag->type, array('with', 'if')))
          throw new ParseError("You can only use else with |with and |if, you tried |{$parentTag->type}");
        $elseTag->else = true;
      }
    }
  }

  function renderStTag() {
    $out = "<" . "?php ";
    $scopeName = "";
    if (preg_match('/ as ([a-zA-Z]+)$/', $this->var, $m)) {
      $scopeName = $m[1];
      $lookup = substr($this->var, 0, strlen($this->var) - strlen($m[0]));
      $hsv = new H\String(trim($lookup), H\String::TOKEN_CONTROL);
    } else
      $hsv = new H\String($this->var, H\String::TOKEN_CONTROL);
    switch ($this->type) {
      case "each":
        if ($this->var)
          $out .= "foreach(" . $hsv->toPHP() . " as {$this->o}) { \n";
        else
          $out .= "foreach(Hamle\\Scope::get() as {$this->o}) { \n";
        $out .= "Hamle\\Scope::add({$this->o}); ";
        break;
      case "if":
        $hsvcomp = new H\String\Comparison($this->var);
        $out .= "if(" . $hsvcomp->toPHP() . ") {";
        break;
      case "with":
        if ($scopeName)
          $out .= "Hamle\\Scope::add(" . $hsv->toPHP() . ", \"$scopeName\");\n;";
        else {
          $out .= "if(({$this->o} = " . $hsv->toPHP() . ") && " .
              "{$this->o}->valid()) {\n";
          $out .= "Hamle\\Scope::add({$this->o});\n;";
        }
        break;
      case "else":
        $out .= "/* else */";
        break;
      case "include":
        $out .= "echo Hamle\\Run::includeFile(" . $hsv->toPHP() . ");";
        break;
    }
    return $out . ' ?>';
  }

  /**
   * @param string $s Variable String for control tag
   */
  function setVar($s) {
    $this->var = trim($s);
  }

  function renderEnTag() {
    $out = '<' . '?php ';
    switch ($this->type) {
      case "each";
        $out .= 'Hamle\\Scope::done(); ';
        $out .= '}';
        if (!$this->var)
          $out .= "Hamle\\Scope::get()->rewind();\n";
        break;
      case "if":
      case "else":
        $out .= "}";
        break;
      case "with";
        if (!preg_match('/ as ([a-zA-Z]+)$/', $this->var, $m)) {
          $out .= 'Hamle\\Scope::done(); ';
          $out .= '}';
        }
        break;
      case "include":
        return "";
        break;
    }
    if ($this->else) $out .= "else{";
    return $out . ' ?>';
  }

  function render($indent = 0, $doIndent = true) {
    return parent::render($indent - self::INDENT_SIZE, false);
  }
}