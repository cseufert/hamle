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
namespace Seufert\Hamle\Model;

use Seufert\Hamle\Model;

/**
 * Zero Model
 *
 * This model is an empty model result
 *
 * @package Seufert\Hamle\Model
 */
class Zero implements Model
{
  public function hamleGet($key)
  {
    return new Zero();
  }

  public function hamleRel(
    $rel,
    $typeTags,
    $sort = [],
    $limit = 0,
    $offset = 0,
    $grouptype = 1
  ) {
    return new Zero();
  }

  public function valid()
  {
    return false;
  }

  public function key()
  {
    return 0;
  }

  public function current()
  {
    return $this;
  }

  public function rewind()
  {
  }

  public function next()
  {
  }

  public function __toString()
  {
    return '';
  }
}
