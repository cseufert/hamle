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
namespace Seufert\Hamle\Text;
use Seufert\Hamle\Exception;
use Seufert\Hamle\Text;

class Comparison extends Text {


  protected $param1, $param2, $operator;
  const REGEX_COMP_OPER = '(equals|notequal|notequals|less|greater|has|starts|contains|ends)';

  function __construct($s, $mode = self::TOKEN_CONTROL) {
    $m = array();
    if(preg_match('/^(.*) '.self::REGEX_COMP_OPER.' (.*)$/', $s, $m)) {
      $this->param1 = new Text($m[1],Text::TOKEN_HTML);
      $this->param2 = new Text($m[3],Text::TOKEN_HTML);
      $this->operator = $m[2];
    } else
      $this->param1 = new Text($s,Text::TOKEN_HTML);
  }

//  function __construct(String $p1, String $p2, $operator) {
//    $this->param1 = $p1;
//    $this->param2 = $p2;
//    $this->operator = $operator;
//  }
  function toPHP() {
    if(!$this->param2) return $this->param1->toPHP();
    $p1 = $this->param1->toPHP();
    $p2 = $this->param2->toPHP();
    switch($this->operator) {
      case "equals":
      case "equal":
        return $p1." == ".$p2;
      case "notequals":
      case "notequal":
        return $p1." != ".$p2;
      case "less":
        return $p1." < ".$p2;
      case "greater":
        return $p1." > ".$p2;
      case "has":
        return "in_array($p2, $p1)";
      case "starts":
        return "strpos($p1, $p2) === 0";
      case "contains":
        return "strpos($p1, $p2) !== FALSE";
      case "ends":
        return "substr($p1, -strlen($p2)) === $p2";
      case "or":
      case "and":
      case "xor":
        throw new Exception\Unimplemented("OR/AND/XOR Unimplmented at this time");
//        return "($p1) OR ($p2)";
//        return "($p1) AND ($p2)";
//        return "($p1) XOR ($p2)";
    }
    return "";
  }
  function toHTML($escape = false) {
    throw new Exception\Unimplemented("Unable to output comparison results to HTML");
  }
}