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

class WrapArray extends Zero
{
  protected array $data;
  protected int $pos = 0;

  function __construct(array $array = [])
  {
    $this->data = $array;
  }

  function hamleGet(string $key): mixed
  {
    if (!isset($this->data[$this->pos][$key])) {
      return "Missing Key [$key]";
    }
    return $this->data[$this->pos][$key];
  }

  function valid(): bool
  {
    return isset($this->data[$this->pos]);
  }

  function key(): mixed
  {
    return $this->pos;
  }

  function current(): mixed
  {
    return $this;
  }

  function rewind(): void
  {
    $this->pos = 0;
  }

  function next(): void
  {
    ++$this->pos;
  }
}
