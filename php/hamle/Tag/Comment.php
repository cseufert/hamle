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

use Seufert\Hamle\Tag;

class Comment extends Tag
{
  protected string $commentstyle = '';

  function __construct(string $type)
  {
    if ($type == '/') {
      $this->commentstyle = 'HTML';
    }
  }

  function renderStTag(): string
  {
    return $this->commentstyle == 'HTML' ? '<!-- ' : '';
  }

  function renderContent(string $pad = '', bool $oneliner = false): string
  {
    if ($this->commentstyle == 'HTML') {
      if (count($this->content) > 1) {
        return $pad . '  ' . implode("\n$pad", $this->content) . "\n";
      } else {
        return current($this->content);
      }
    }
    return '';
  }

  function renderEnTag(): string
  {
    return $this->commentstyle == 'HTML' ? ' -->' : '';
  }
}
