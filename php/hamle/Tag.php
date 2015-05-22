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
class Tag {
  /**
   * @var Tag[] Array of children tag elements
   */
  public $tags = array();
  /**
   * @var string Tag Type for Printable Tags
   */
  public $type;
  /**
   * @var array Array of lines of Content
   */
  public $content;

  public $opt;

  public $source;
  /**
   * Number of spaces for each Indent when doing pretty format of output
   */
  const INDENT_SIZE = 2;

  /**
   * Initialize instance vars
   */
  function __construct() {
    $this->tags = array();
    $this->content = array();
  }

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
    foreach ($this->tags as $k => $tag) {
      if ($r = $tag->replace($path, $newTag)) {
        $r->addSnipContent($this->tags[$k]);
        array_splice($this->tags, $k, 1, $r->tags);
      }
    }
    return null;
  }

  function addSnipContent($contentTag, &$tagArray = array(), $key = 0) {
    foreach ($this->tags as $k => $tag)
      $tag->addSnipContent($contentTag, $this->tags, $k);
  }

  function compare($tic) {
    if (isset($tic['type']) && $this->type != $tic['type'])
      return false;
    if (isset($tic['id']) &&
        !(isset($this->opt['id']) && $tic['id'] == $this->opt['id'])
    )
      return false;
    if (isset($tic['class']) && !(isset($this->opt['class'])
            && !array_diff($tic['class'], $this->opt['class']))
    )
      return false;
    return true;
  }

  /**
   * Add a child tag to this tag
   * @param Tag $tag Tag to add as child
   * @param string $mode Mode to add child [append|prepend]
   */
  function addChild(Tag $tag, $mode = "append") {
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

  /**
   * Apply indent, to content, and return as string
   *
   * @param string $pad Indent String
   * @param bool $oneliner Render to fit single line
   * @return string Indented Content
   */
  function renderContent($pad = "", $oneliner = false) {
    $out = "";
    foreach ($this->content as $c)
      $out .= ($oneliner ? "" : $pad) . $c . ($oneliner ? "" : "\n");
    return $out;
  }

  /**
   * Output the Start Tag for this element
   */
  function renderStTag() {
  }

  /**
   * Output the End Tag for this element
   */
  function renderEnTag() {
  }

  /**
   * Add content to this tag, one line at a time
   *
   * @param string $s One line of content
   * @param int $strtype Type of string to parse, hamleString::TOKEN_*
   */
  function addContent($s, $strtype = Text::TOKEN_HTML) {
    if (trim($s)) {
      $parse = new Text($s, $strtype);
      $this->content[] = $parse->toHTML();
    }
  }

}







