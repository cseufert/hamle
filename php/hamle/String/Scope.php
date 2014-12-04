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
namespace Seufert\Hamle\String;

use Seufert\Hamle\String;
use Seufert\Hamle\Exception\ParseError;

class Scope extends SimpleVar {
  protected $scope = 0;

  function __construct($s) {
    $m = array();
    //var_dump($s);
    if (!preg_match('/\$\[(-?[0-9]+|[a-zA-Z]+)\]/', $s, $m))
      throw new ParseError("Unable to match scope");
    $this->scope = $m[1];
  }

  function toPHP() {
    if (is_numeric($this->scope))
      return "Hamle\\Scope::get(" . String::varToCode($this->scope) . ")";
    else
      return "Hamle\\Scope::getName(" . String::varToCode($this->scope) . ")";
  }

  function toHTML() {
    throw new
    ParseError("Unable to use Scope operator in HTML Code");
  }
}