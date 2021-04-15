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
namespace Seufert\Hamle\Text;

use Seufert\Hamle\Exception\ParseError;
use Seufert\Hamle\Model\WrapArray;
use Seufert\Hamle\Text;

class Filter extends Text
{
  protected $filter;

  protected $vars;

  /** @var SimpleVar */
  protected $what;

  /** @var Filter|null Chained Filter*/
  protected $chained;

  /** @var Callable|null Filter resolver, must return function name, or null */
  static $filterResolver = null;

  function __construct($s, Text $what)
  {
    if (
      preg_match(
        "/^([a-z_]+)(?:\\((?P<vars>.*)\\))?(?:\\|(?P<chained>.+?))?$/",
        $s,
        $m,
      )
    ) {
      $this->filter = $m[1];
      $this->vars =
        isset($m['vars']) && strlen($m['vars']) ? explode(',', $m['vars']) : [];
      foreach ($this->vars as $k => $v) {
        $this->vars[$k] = str_replace('&comma;', ',', $v);
      }
      if (isset($m['chained']) && strlen($m['chained'])) {
        $this->chained = new Filter($m['chained'], $what);
      }
    } else {
      throw new ParseError("Unable to parse filter expression \"$s\"");
    }
    if (method_exists(Filter::class, $this->filter)) {
      $this->filter = Filter::class . '::' . $this->filter;
    } elseif (
      in_array($this->filter, ['round', 'strtoupper', 'strtolower', 'ucfirst'])
    ) {
    } elseif ($this->filter === 'json') {
      $this->filter = 'json_encode';
    } elseif (
      self::$filterResolver &&
      ($filter = (self::$filterResolver)($this->filter))
    ) {
      $this->filter = $filter;
    } else {
      throw new ParseError("Unknown Filter Type \"{$this->filter}\"");
    }
    $this->what = $what;
  }

  function toHTML($escape = false)
  {
    if ($escape) {
      return '<?=htmlspecialchars(' . $this->toPHP() . ')?>';
    }
    return '<?=' . $this->toPHP() . '?>';
  }

  function toPHPpre()
  {
    $pre = '';
    if ($this->chained) {
      $pre = $this->chained->toPHPpre();
    }
    return "$pre{$this->filter}(";
  }

  function toPHPpost()
  {
    $post = '';
    if ($this->chained) {
      $post = $this->chained->toPHPpost();
    }
    $o = '';
    foreach ($this->vars as $v) {
      $o .= ',' . $this->varToCode($v);
    }
    return "$o)$post";
  }

  function toPHP()
  {
    return $this->toPHPpre() . $this->what->toPHPVar() . $this->toPHPpost();
    // $o = [$this->what->toPHPVar()] ;
    // foreach($this->vars as $v)
    //   $o[] = $this->varToCode($v);
    // return "{$this->filter}(" . implode(',',$o) . ")";
  }

  static function itersplit($v, $sep = ',')
  {
    $o = [];
    foreach (explode($sep, $v) as $k => $i) {
      if ($i) {
        $o[] = ['v' => trim($i), 'value' => trim($i), 'k' => $k, 'key' => $k];
      }
    }
    return new WrapArray($o);
  }

  static function newlinebr($v)
  {
    return str_replace("\n", "<br />\n", $v);
  }

  static function replace($v, $src, $dst)
  {
    return str_replace($src, $dst, $v);
  }

  static function ascents($v)
  {
    $v = str_replace(['$', ' ', ','], '', $v);
    return (int) round($v * 100, 0);
  }
}
