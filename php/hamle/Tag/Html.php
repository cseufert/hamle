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

/**
 * HAMLE HTML Tag
 * Use to represent plain HTML Tags
 */
class Html extends H\Tag {
  /**
   * @var array Options for html tags (eg, href, class, style, etc)
   */
  static protected $selfCloseTags = array("area", "base", "br", "col", "command",
      "embed", "hr", "img", "input", "keygen", "link",
      "meta", "param", "source", "track", "wbr");

  function __construct($tag, $classid, $param = array()) {
    parent::__construct();
    $this->opt = array();
    $this->source = array();
    $this->type = $tag ? $tag : "div";
    if (is_array($param))
      $this->opt = $param;
    elseif (isset($param[0]) && $param[0] == "[") {
      $param = substr($param, 1, strlen($param) - 2);
      parse_str($param, $this->opt);
    }
    if (isset($this->opt['class']) && !is_array($this->opt['class']))
      $this->opt['class'] = explode(" ", $this->opt['class']);
    $this->opt += array('class' => array());

    preg_match_all('/[#\.!][a-zA-Z0-9\-\_]+/m', $classid, $m);
    if (isset($m[0])) foreach ($m[0] as $s) {
      if ($s[0] == "#") $this->opt['id'] = substr($s, 1);
      if ($s[0] == ".") $this->opt['class'][] = substr($s, 1);
      if ($s[0] == "!") $this->source[] = substr($s, 1);
    }
  }

  function renderStTag() {
    $close = in_array($this->type, self::$selfCloseTags) ? " />" : ">";
    return "<{$this->type}" . $this->optToTags() . $close;
  }

  function renderEnTag() {
    if (in_array($this->type, self::$selfCloseTags))
      return "";
    return "</{$this->type}>";
  }

  /**
   * Used to convert urlencoded string into html attributes
   *
   * @return string HTML Attributes
   */
  function optToTags() {
    $out = array();
    foreach ($this->opt as $k => $v) {
      if ($k == "class" && !$v) continue;
      if (is_array($v)) $v = implode(" ", $v);
      if (!$v instanceof H\Text)
        $v = new H\Text($v);
      $k = new H\Text($k);
      $out[] = " " . $k->toHTML() . "=\"" . $v->toHTMLAtt() . "\"";
    }
    return implode("", $out);
  }
}