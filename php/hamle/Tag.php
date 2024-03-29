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
namespace Seufert\Hamle;

/**
 * HAMLE Tag object
 *
 * @author Chris Seufert <chris@seufert.id.au>
 * @package hamle
 */
class Tag
{
  /**
   * @var Tag[] Array of children tag elements
   */
  public $tags = [];
  /**
   * @var string Tag Type for Printable Tags
   */
  public string $type = '';
  /**
   * @var array Array of lines of Content
   */
  public array $content = [];

  public array $opt = [];

  /** @var array */
  public $source = [];
  /**
   * Number of spaces for each Indent when doing pretty format of output
   */
  const INDENT_SIZE = 2;

  /**
   * Initialize instance vars
   */
  function __construct()
  {
    $this->tags = [];
    $this->content = [];
  }

  /**
   * @param array $path array of arrays[type/class/id]
   * @return Tag[] The tags you are looking for
   */
  function find($path)
  {
    $list = [];
    if ($this->compare($path[0])) {
      if (count($path) == 1) {
        $list[] = $this;
        return $list;
      }
      array_shift($path);
    }
    foreach ($this->tags as $tag) {
      if ($found = $tag->find($path)) {
        $list = array_merge($list, $found);
      }
    }
    return $list;
  }

  /**
   * Replace a tag at $path with a new tag ($newTag)
   * @param $path array Path Array
   * @param $newTag Tag New tag to replace old tag with
   * @return Tag|null
   */
  function replace(array $path, Tag $newTag): ?Tag
  {
    if ($this->compare($path[0])) {
      if (count($path) == 1) {
        return $newTag;
      }
      array_shift($path);
    }
    foreach ($this->tags as $k => $tag) {
      if ($r = $tag->replace($path, $newTag)) {
        $r->addSnipContent($this->tags[$k]);
        array_splice($this->tags, $k, 1, $r->tags);
      }
    }
    return null;
  }

  function addSnipContent(
    Tag $contentTag,
    array &$tagArray = [],
    int $key = 0
  ): void {
    foreach ($this->tags as $k => $tag) {
      $tag->addSnipContent($contentTag, $this->tags, $k);
    }
  }

  function compare(array $tic): bool
  {
    if (isset($tic['type']) && $this->type != $tic['type']) {
      return false;
    }
    if (
      isset($tic['id']) &&
      !(isset($this->opt['id']) && $tic['id'] == $this->opt['id'])
    ) {
      return false;
    }
    if (array_diff($tic['class'] ?? [], $this->opt['class'] ?? [])) {
      return false;
    }
    return true;
  }

  /**
   * Add a child tag to this tag
   * @param Tag $tag Tag to add as child
   * @param string $mode Mode to add child [append|prepend]
   */
  function addChild(Tag $tag, $mode = 'append'): void
  {
    if ($mode == 'prepend') {
      array_unshift($this->tags, $tag);
    } else {
      $this->tags[] = $tag;
    }
  }

  /**
   * Render html/php output to disk
   *
   * @param int $indent Number of spaces in current indent level
   * @param bool $minify Output HTML in minified format
   * @return string HTML/PHP Output
   */
  function render(int $indent = 0, bool $minify = false): string
  {
    $ind = $minify ? '' : str_pad('', $indent);
    $oneliner = !(count($this->content) > 1 || $this->tags);
    $out = $ind . $this->renderStTag() . ($oneliner || $minify ? '' : "\n");
    if ($this->content) {
      $out .= $this->renderContent($ind, $oneliner || $minify);
    }
    foreach ($this->tags as $tag) {
      $out .= $tag->render($indent + self::INDENT_SIZE, $minify);
    }
    $out .=
      ($minify || $oneliner ? '' : $ind) .
      $this->renderEnTag() .
      ($minify ? '' : "\n");
    return $out;
  }

  /**
   * Apply indent, to content, and return as string
   *
   * @param string $pad Indent String
   * @param bool $oneliner Render to fit single line
   * @return string Indented Content
   */
  function renderContent(string $pad = '', bool $oneliner = false): string
  {
    $out = '';
    foreach ($this->content as $c) {
      $out .= ($oneliner ? '' : $pad) . $c . ($oneliner ? '' : "\n");
    }
    return $out;
  }

  /**
   * Output the Start Tag for this element
   */
  function renderStTag(): string
  {
    return '';
  }

  /**
   * Output the End Tag for this element
   */
  function renderEnTag(): string
  {
    return '';
  }

  /**
   * Add content to this tag, one line at a time
   *
   * @param string $s One line of content
   * @param int $strtype Type of string to parse, hamleString::TOKEN_*
   */
  function addContent(string $s, int $strtype = Text::TOKEN_HTML): void
  {
    if (trim($s)) {
      $parse = new Text($s, $strtype);
      $this->content[] = $parse->toHTML();
    }
  }
}
