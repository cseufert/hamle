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

class Snippet extends Hamle\Tag
{
  protected array $path;

  function __construct(string $params)
  {
    parent::__construct();
    if (
      !preg_match('/^(append|content|prepend|replace)(?: (.*))?$/', $params, $m)
    ) {
      throw new Hamle\Exception\ParseError("Unable to parse Snippet($params)");
    }
    $this->type = $m[1];
    if (isset($m[2])) {
      $this->path = explode(' ', $m[2]);
    } else {
      $this->path = [];
    }
    foreach ($this->path as $k => $v) {
      $this->path[$k] = self::decodeClassId($v);
    }
  }

  static function decodeClassId(string $s): array
  {
    $out = $m = [];
    if (preg_match('/^[a-zA-Z0-9\_]+/', $s, $m)) {
      $out['type'] = $m[0];
    }
    preg_match_all('/[#\.][a-zA-Z0-9\-\_]+/m', $s, $m);
    if (isset($m[0])) {
      foreach ($m[0] as $ss) {
        if ($ss[0] === '#') {
          $out['id'] = substr($ss, 1);
        }
        if ($ss[0] === '.') {
          $out['class'][] = substr($ss, 1);
        }
      }
    }
    return $out;
  }

  function getType(): string
  {
    return $this->type;
  }

  function addSnipContent(
    Hamle\Tag $contentTag,
    array &$tagArray = [],
    int $key = 0
  ): void {
    if ($this->type == 'content') {
      $tagArray[$key] = $contentTag;
    } else {
      parent::addSnipContent($contentTag, $tagArray, $key);
    }
  }

  function apply(Hamle\Tag $rootTag): void
  {
    if ($this->type == 'append' || $this->type == 'prepend') {
      $matchTags = $rootTag->find($this->path);
      foreach ($matchTags as $tag) {
        foreach ($this->tags as $t) {
          $tag->addChild($t, $this->type);
        }
      }
    } elseif ($this->type == 'replace') {
      $rootTag->replace($this->path, $this);
    } else {
      throw new Hamle\Exception\ParseError(
        "Cant Apply snippet to document '{$this->type}'",
      );
    }
  }
}
