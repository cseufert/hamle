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
use Seufert\Hamle\Tag;

/**
 * String Tag
 */
class Text extends Tag
{
  protected $escape = true;
  protected $escapeVars;

  function __construct($tag)
  {
    parent::__construct();
    $this->escape = $tag !== '__';
    $this->escapeVars = $tag === '_';
  }

  function addContent($s, $strtype = H\Text::TOKEN_HTML)
  {
    if (strlen($s)) {
      if ($this->escape) {
        $parse = new H\Text($s, $strtype);
        $this->content[] = $parse->toHTML($this->escapeVars);
      } else {
        $this->content[] = $s;
      }
    }
  }
}
