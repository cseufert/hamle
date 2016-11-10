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

use Seufert\Hamle\Exception\ParseError;
use Seufert\Hamle\Model\WrapArray;
use Seufert\Hamle\Text;

class Filter extends Text {
  protected $filter;

  protected $vars;

  /** @var SimpleVar */
  protected $what;

  function __construct($s, Text $what) {
    if(preg_match("/^([a-z]+)(\\((.*)\\))?$/", $s, $m)) {
      $this->filter = $m[1];
      $this->vars = isset($m[3]) ? explode(',', $m[3]) : [];
      foreach($this->vars as $k=>$v)
        $this->vars[$k] = str_replace("&comma;",',',$v);
    } else {
      throw new ParseError("Unable to parse filter expression \"$s\"");
    }
    if(!in_array($this->filter, ['itersplit', 'newlinebr', 'round',
        'strtoupper', 'strtolower', 'ucfirst','replace', 'json'])) {
      throw new ParseError("Unknown Filter Type \"{$this->filter}\"");
    }
    if(in_array($this->filter,['itersplit','newlinebr', 'replace'])) {
        $this->filter = "Seufert\\Hamle\\Text\\Filter::{$this->filter}";
    }
    $mapFilter = ['json'=>'json_encode'];
    if(isset($mapFilter[$this->filter]))
      $this->filter = $mapFilter[$this->filter];
    $this->what = $what;
  }

  function toHTML($escape = false) {
    if($escape)
      return "<?=htmlspecialchars(" .$this->toPHP() . ")?>";
    return "<?=" . $this->toPHP() . "?>";
  }

  function toPHP() {
    $o = [$this->what->toPHPVar()] ;
    foreach($this->vars as $v)
      $o[] = $this->varToCode($v);
    return "{$this->filter}(" . implode(',',$o) . ")";
  }

  static function itersplit($v, $sep = ",") {
    $o = [];
    foreach(explode($sep, $v) as $k=>$i) {
      if($i)
        $o[] = ['v'=>trim($i), 'value'=>trim($i), 'k'=>$k,'key'=>$k];
    }
    return new WrapArray($o);
  }

  static function newlinebr($v) {
    return str_replace("\n","<br />\n",$v);
  }

  static function replace($v, $src, $dst) {
    return str_replace($src,$dst,$v);
  }

}