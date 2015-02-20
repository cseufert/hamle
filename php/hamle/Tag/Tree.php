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

/**
 * HAMLE Simple Tag - Does not contain nested tags.
 *
 * @author Chris Seufert <chris@seufert.id.au>
 * @package hamle
 */

class Tree extends Tag {

  /** @var Tag[] Array of children tag elements */
  public $tags = [];

  function __construct() { }

  /**
   * @param array $path array of arrays[type/class/id]
   * @return Tag[] The tags you are looking for
   */
  function find($path) {
    //var_dump($this->type, json_encode($path), $this->compare($path[0]));
    $list = array();
    if ($this->compare($path[0])) {
      if (count($path) == 1) {
        $list[] = $this;
        return $list;
      }
      array_shift($path);
    }
    foreach ($this->tags as $tag)
      if($tag instanceof Tree)
        if ($found = $tag->find($path))
          $list = array_merge($list, $found);
    return $list;
  }

  /**
   * Replace a tag at $path with a new tag ($newTag)
   * @param $path array Path Array
   * @param $newTag Tag New tag to replace old tag with
   * @return Tag
   */
  function replace($path, Tag $newTag) {
    if ($this->compare($path[0])) {
      if (count($path) == 1) return $newTag;
      array_shift($path);
    }
    foreach ($this->tags as $k => $tag)
      if($tag instanceof Tree) {
      if ($r = $tag->replace($path, $newTag)) {
        $r->addSnipContent($this->tags[$k]);
        array_splice($this->tags, $k, 1, $r->tags);
      }
    }
    return null;
  }

  function addSnipContent($contentTag, &$tagArray = array(), $key = 0) {
    foreach ($this->tags as $k => $tag)
      if($tag instanceof Tree)
      $tag->addSnipContent($contentTag, $this->tags, $k);
  }

  /**
   * Add a child tag to this tag
   * @param Tag $tag Tag to add as child
   * @param string $mode Mode to add child [append|prepend]
   */
  function addChild($tag, $mode = "append") {
    if ($mode == "prepend")
      array_unshift($this->tags, $tag);
    else
      $this->tags[] = $tag;
  }

  /**
   * Render html/php output to disk
   *
   * @param int $indent Number of spaces in current indent level
   * @param boolean $doIndent Indent this tag
   * @return string HTML/PHP Output
   */
  function render($indent = 0, $doIndent = true) {
    //if(strtoupper($this->type) == "A") var_dump($this);
    $ind = $doIndent ? str_pad("", $indent, " ") : "";
    $oneliner = ((count($this->content) > 1 || $this->tags) ? false : true);
    $out = $ind . $this->renderStTag() . ($oneliner ? "" : "\n");
    if ($this->content) $out .= $this->renderContent($ind, $oneliner);
    foreach ($this->tags as $tag)
      $out .= $tag->render($indent + self::INDENT_SIZE);
    $out .= ($oneliner ? "" : $ind) . $this->renderEnTag() . "\n";
    return $out;
  }


}