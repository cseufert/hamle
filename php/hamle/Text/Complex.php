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

use http\Exception\RuntimeException;
use Seufert\Hamle\Model;
use Seufert\Hamle\Run;
use Seufert\Hamle\Text;
use Seufert\Hamle\Exception\ParseError;
use Seufert\Hamle\WriteModel;

class Complex extends Text {
  protected $func;
  protected $sel = null;
  protected $filter;

  function __construct($s) {
    if(FALSE !== $pos = strpos($s,'|')) {
      $this->filter = new Filter(substr($s, $pos+1), $this);
      $s = substr($s,0,$pos);
    }
    $s = preg_split("/-[>!]/", $s);
    // if(count($s) == 1) $s = explode("-!",$s[0]);
    if (!$s[0]) throw new ParseError("Unable to parse Complex Expression");
    if ($s[0][1] == "(")
      $this->func = new Text\Func($s[0]);
    elseif ($s[0][1] == "[")
      $this->func = new Text\Scope($s[0]);
    else
      $this->func = new SimpleVar($s[0]);
    array_shift($s);
    $this->sel = $s;
  }

  function toHTML($escape = false) {
    if($escape)
      return "<?=htmlspecialchars(" .$this->toPHP() . ")?>";
    return "<?=" . $this->toPHP() . "?>";
  }
  function toPHP() {
    return $this->filter?$this->filter->toPHP():$this->toPHPVar();
  }
  function toPHPVar() {
    if ($this->sel) {
      $sel = array();
      foreach ($this->sel as $s)
        $sel[] = "hamleGet('$s')";
      return $this->func->toPHP() . "->" . implode('->', $sel);
    } else
      return $this->func->toPHP();
  }

  function getOrCreateModel(Model $parent = null) {
    if($this->func instanceof Text\Scope)
      return $this->func->getOrCreateModel($parent);
    if($this->func instanceof Text\Func)
      return $this->func->getOrCreateModel($parent);
  }

  /**
   * @param $value
   * @return WriteModel
   */
  function setValue($value) {
    if(!$this->sel || count($this->sel) != 1)
      throw new \RuntimeException('Can only set values, when one var name is present');
    $model = $this->getOrCreateModel();
    if(!$model instanceof WriteModel)
      throw new \RuntimeException('Can only set values on Runtime Exceptions');
    $model->hamleSet($this->sel[0], $value);
    return $model;
  }


}