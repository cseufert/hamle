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
 * HAMLE String Conversion Library
 *
 * @author Chris Seufert <chris@seufert.id.au>
 * @package hamle
 */
class Text {
  const TOKEN_CONTROL = 0x07;
  const TOKEN_HTML = 0x06;
  const TOKEN_CODE = 0x04;

  const REGEX_HTML = '/(\\$[a-zA-Z0-9\\_]+)|({\\$.*?})/';
  const REGEX_CODE = '//';

  const FIND_DOLLARFUNC = 0x01;
  const FIND_DOLLARVAR = 0x02;
  const FIND_BARDOLLAR = 0x04;

  /**
   * @var \Seufert\Hamle\Text[] Array of Child String Objects
   */
  protected $nodes;

  function __construct($s, $mode = self::TOKEN_HTML) {
    $m = array();
    $pos = 0;
    $this->nodes = array();
    $rFlag = PREG_OFFSET_CAPTURE + PREG_SET_ORDER;
    if (!trim($s)) return;
    if ($mode == self::TOKEN_CONTROL) {
      if (preg_match('/^"(.*)"$/', trim($s), $m)) {
        $this->nodes[] = new Text($m[1]);
      } else
        $this->nodes[] = new Text\Complex(trim($s));
      return;
    }
    preg_match_all(self::REGEX_HTML, $s, $m, $rFlag);
    foreach ($m as $match) {
      if ($mode & self::FIND_BARDOLLAR && isset($match[2])) {
        if ($match[2][1] != $pos)
          $this->nodes[] = new Text\Plain(
              substr($s, $pos, $match[2][1] - $pos), $mode);
        $this->nodes[] = new Text\Complex(substr($match[2][0], 1, -1));
        $pos = $match[2][1] + strlen($match[2][0]);
      } elseif ($mode & self::FIND_DOLLARVAR) {
        if ($match[1][1] > 0 && $s[$match[1][1] - 1] == '\\') continue;
        if ($match[1][1] != $pos)
          $this->nodes[] = new Text\Plain(
              substr($s, $pos, $match[1][1] - $pos), $mode);
        $this->nodes[] = new Text\SimpleVar($match[1][0]);
        $pos = $match[1][1] + strlen($match[1][0]);
      }
    }
    if ($pos != strlen($s))
      $this->nodes[] = new Text\Plain(substr($s, $pos), $mode);
  }

  function toHTML() {
    $out = array();
    foreach ($this->nodes as $string)
      $out[] = $string->toHTML();
    return implode("", $out);
  }

  function toPHP() {
    $out = array();
    foreach ($this->nodes as $string)
      $out[] = $string->toPHP();
    return implode(".", $out);
  }

  function doEval() {
    return eval('return ' . $this->toPHP() . ';');
  }

  static function varToCode($var) {
    if (is_array($var)) {
      $code = array();
      foreach ($var as $key => $value)
        $code[] = self::varToCode($key) . "=>" . self::varToCode($value);
      return 'array(' . implode(",", $code) . ')'; //remove unnecessary coma
    } elseif (is_bool($var)) {
      return ($var ? 'TRUE' : 'FALSE');
    } elseif (is_int($var) || is_float($var) || is_numeric($var)) {
      return $var;
    } else {
      return "'" . str_replace(array('$', "'"), array('\\$', "\\'"), $var) . "'";
    }
  }

}

