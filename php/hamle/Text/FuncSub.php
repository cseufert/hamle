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

use Seufert\Hamle;
use Seufert\Hamle\Exception\ParseError;

class FuncSub extends Hamle\Text\Func {
  protected $dir;
  protected $grouptype = ['grouptype'=>0];

  /**
   * FuncSub constructor.
   * @param string $s
   */
  function __construct($s) {
    $m = array();
    if (!preg_match('/^ +([><]) +('.self::REGEX_FUNCSEL . '+)(.*)$/', $s, $m))
      throw new ParseError("Unable to read \$ sub func in '$s'");
    if ($m[1] == "<") $this->dir = Hamle\Hamle::REL_PARENT;
    elseif ($m[1] == ">") $this->dir = Hamle\Hamle::REL_CHILD;
    else $this->dir = Hamle\Hamle::REL_ANY;
    $this->sortlimit = $this->attSortLimit($m[2]);
    $this->filt = $this->attIdTag($m[2]);
    $this->grouptype = $this->attGroupType($m[2]);
    if ($this->filt['id']) throw new ParseError("Unable to select by id");
    if (trim($m[3]))
      $this->sub = new FuncSub($m[3]);
  }

  /**
   * Return as PHP Code
   * @return string
   */
  function toPHP() {
    $limit = Hamle\Text::varToCode($this->sortlimit['sort']) . "," .
        $this->sortlimit['limit'] . "," . $this->sortlimit['offset'] . "," .
        $this->grouptype['grouptype'];
    $sub = $this->sub ? "->" . $this->sub->toPHP() : "";
    return "hamleRel(" . $this->dir . "," .
    Hamle\Text::varToCode($this->filt['tag']) . ",$limit)$sub";
  }
}