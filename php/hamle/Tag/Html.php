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
use Seufert\Hamle\Text;

/**
 * HAMLE HTML Tag
 * Use to represent plain HTML Tags
 */
class Html extends H\Tag
{
  /**
   * @var array Options for html tags (eg, href, class, style, etc)
   */
  protected static $selfCloseTags = [
    'area',
    'base',
    'br',
    'col',
    'command',
    'embed',
    'hr',
    'img',
    'input',
    'keygen',
    'link',
    'meta',
    'param',
    'source',
    'track',
    'wbr',
  ];

  function __construct(
    string $tag,
    array $class = [],
    array $attr = [],
    string $id = ''
  ) {
    parent::__construct();
    $this->opt = $attr;
    if (isset($attr['class']) && !is_array($attr['class'])) {
      $this->opt['class'] = $attr['class'] ? explode(' ', $attr['class']) : [];
    }
    $this->source = [];
    $this->type = $tag ? $tag : 'div';
    if ($class) {
      if (isset($this->opt['class'])) {
        $this->opt['class'] = array_merge($this->opt['class'], $class);
      } else {
        $this->opt['class'] = $class;
      }
    }
    if ($id) {
      $this->opt['id'] = $id;
    }
  }

  function renderStTag(): string
  {
    $close = in_array($this->type, self::$selfCloseTags) ? ' />' : '>';
    return "<{$this->type}" . $this->optToTags() . $close;
  }

  function renderEnTag(): string
  {
    if (in_array($this->type, self::$selfCloseTags)) {
      return '';
    }
    return "</{$this->type}>";
  }

  /**
   * Used to convert urlencoded string into html attributes
   *
   * @return string HTML Attributes
   */
  function optToTags(): string
  {
    $out = [];
    foreach ($this->opt as $k => $v) {
      if ($k == 'class' && !$v) {
        continue;
      }
      if (is_array($v)) {
        $v = implode(' ', $v);
      }
      if (!$v instanceof H\Text) {
        $v = new H\Text($v ?? '');
      }
      $k = new H\Text($k);
      $out[] = ' ' . $k->toHTML() . "=\"" . $v->toHTMLAtt() . "\"";
    }
    return implode('', $out);
  }

  function addContent(string $s, int $strtype = Text::TOKEN_HTML): void
  {
    if (trim($s)) {
      $parse = new Text($s, $strtype);
      $this->content[] = $parse->toHTML(true);
    }
  }
}
