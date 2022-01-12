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
  public function hamleGet(string $key): mixed
  {
    return new Zero();
  }

  public function hamleRel(
    int $rel,
    array $typeTags,
    array $sort = [],
    int $limit = 0,
    int $offset = 0,
    int $grouptype = 1
  ): Model {
    return new Zero();
  }

  public function valid(): bool
  {
    return false;
  }

  public function key(): mixed
  {
    return 0;
  }

  public function current(): mixed
  {
    return $this;
  }

  public function rewind(): void
  {
  }

  public function next(): void
  {
  }

  public function __toString(): string
  {
    return '';
  }
}
