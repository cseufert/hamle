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


/**
 * HAMLE Tag object
 *
 * @author Chris Seufert <chris@seufert.id.au>
 * @package hamle
 */
class Tag {

  /** @var array Array of lines of Content */
  public $content = [];

  /** Number of spaces for each Indent when doing pretty format of output */
  const INDENT_SIZE = 2;
  const INDENT_STRING = "  ";

  function __construct() { }

  function compare($tic) {
    if (isset($tic['type']) && $this->type != $tic['type'])
      return false;
    if (isset($tic['id']) &&
        !(isset($this->opt['id']) && $tic['id'] == $this->opt['id'])
    )
      return false;
    if (isset($tic['class']) && !(isset($this->opt['class'])
            && !array_diff($tic['class'], $this->opt['class']))
    )
      return false;
    return true;
  }

  /**
   * Render html/php output to disk
   *
   * @param int $indent Number of spaces in current indent level
   * @param boolean $doIndent Indent this tag
   * @return string HTML/PHP Output
   */
  function render($indent = 0, $doIndent = true) {
    $pad = $doIndent ? str_pad("", $indent, " ") : "";
    $inline = count($this->content) < 2;
    $out = $pad . $this->renderStTag() . ($inline?"":"\n");
    $out .= $this->renderContent($pad.self::INDENT_STRING, $inline);
    $out .= ($inline?"":$pad) . $this->renderEnTag() . "\n";
    return $out;
  }

  function renderHamle($pad = "") {
    return $pad.$this->renderHamleTag().
      $this->renderHamleContent($pad . self::INDENT_STRING);
  }

  function renderHamleTag() { }
  function renderHamleContent($pad) { }

  /**
   * Apply indent, to content, and return as string
   *
   * @param string $pad Indent String
   * @param bool $oneliner Render to fit single line
   * @return string Indented Content
   */
  function renderContent($pad = "", $inline = false) {
    $out = "";
    foreach ($this->content as $c)
      $out .= ($inline ? "" : $pad) . $c . ($inline ? "" : "\n");
    return $out;
  }

  /**
   * Output the Start Tag for this element
   */
  function renderStTag() { }

  /**
   * Output the End Tag for this element
   */
  function renderEnTag() { }

  /**
   * Add content to this tag, one line at a time
   *
   * @param string $s One line of content
   * @param int $strtype Type of string to parse, hamleString::TOKEN_*
   */
  function addContent($s, $strtype = String::TOKEN_HTML) {
    if (trim($s)) {
      $parse = new String($s, $strtype);
      $this->content[] = $parse->toHTML();
    }
  }

}







