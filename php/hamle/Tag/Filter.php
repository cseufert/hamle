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
use Seufert\Hamle;
use Seufert\Hamle\Exception\ParseError;
use Seufert\Hamle\Tag;

/**
 * HAMLE Filter Tag
 * Filter tags start with colon or (:) and use hamleFilter_<filtername>
 */
class Filter extends Tag
{
  /**
   * @var Hamle\Filter $filter Filter CLass
   */
  protected $filter;

  function __construct($tag)
  {
    parent::__construct();
    $this->type = ucfirst(strtolower($tag));
    $this->filter = "\\Seufert\\Hamle\\Filter\\{$this->type}";
    if (!class_exists($this->filter)) {
      throw new ParseError("Unable to fild filter $tag");
    }
  }

  function renderContent($pad = '', $oneliner = false)
  {
    $c = $this->filter;
    return $c::filterText(parent::renderContent($pad));
  }

  function renderStTag()
  {
    $c = $this->filter;
    return $c::stTag();
  }

  function renderEnTag()
  {
    $c = $this->filter;
    return $c::ndTag();
  }
}
