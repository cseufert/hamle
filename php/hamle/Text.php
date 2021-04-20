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

use Seufert\Hamle\Exception\ParseError;
use Seufert\Hamle\Grammar\Parser;
use Seufert\Hamle\Grammar\SyntaxError;
use Seufert\Hamle\Text\Filter;
use Seufert\Hamle\TextNode\Doc;
use Seufert\Hamle\TextNode\Literal;

/**
 * HAMLE String Conversion Library
 *
 * @author Chris Seufert <chris@seufert.id.au>
 * @package hamle
 */
class Text
{
  const TOKEN_CONTROL = 0x07;
  const TOKEN_HTML = 0x06;
  const TOKEN_CODE = 0x04;

  const REGEX_HTML = '/(\\$[a-zA-Z_][a-zA-Z0-9_]*)|({\\$.*?})/';
  const REGEX_CODE = '//';

  const FIND_DOLLARFUNC = 0x01;
  const FIND_DOLLARVAR = 0x02;
  const FIND_BARDOLLAR = 0x04;

  const START_RULE_MAP = [
    self::TOKEN_HTML => 'HtmlInput',
    self::TOKEN_CODE => 'CodeInput',
    self::TOKEN_CONTROL => 'ControlInput',
  ];

  protected $mode;

  protected $tree;

  function __construct($s, $mode = self::TOKEN_HTML)
  {
    //    var_dump($s);
    $this->mode = $mode;
    try {
      $this->tree = (new Parser())->parse($s, [
        'startRule' => self::START_RULE_MAP[$mode],
      ]);
    } catch (SyntaxError $e) {
      throw new ParseError(
        'Unable to parse:' . $s . "\n\n" . $e->getMessage(),
        0,
        $e,
      );
    }

    //    var_dump($this->tree);
    if (!$this->tree instanceof Doc) {
      $this->tree = new Doc(
        is_array($this->tree) ? $this->tree : [$this->tree],
      );
    }
  }

  static function queryParams(array $query, bool $addGroup = false)
  {
    $lastType = '*';
    $typeTags = [];
    $limit = 0;
    $offset = 0;
    $group = 0;
    $sort = [];
    $o = '';
    foreach ($query as $q) {
      switch ($q['q']) {
        case 'type':
          $typeTags[($lastType = $q['id'])] = [];
          break;
        case 'tag':
          $typeTags[$lastType][] = $q['id'];
          break;
        case 'group':
          $group = $q['id'];
          break;
      }
    }
    $opt = [
      self::varToCode($typeTags),
      self::varToCode($sort),
      self::varToCode($limit),
      self::varToCode($offset),
    ];
    if ($addGroup) {
      $opt[] = self::varToCode($group);
    }
    return join(',', $opt);
  }

  static function queryId(array $query)
  {
    $type = '';
    $id = '';
    $limit = 0;
    $offset = 0;
    $sort = [];
    foreach ($query as $q) {
      switch ($q['q']) {
        case 'type':
          $type = $q['id'];
          break;
        case 'id':
          $id = $q['id'];
          break;
      }
    }
    $opt = [
      self::varToCode([$type => [$id]]),
      self::varToCode($sort),
      self::varToCode($limit),
      self::varToCode($offset),
    ];
    return 'Hamle\Run::modelTypeId(' . join(',', $opt) . ')';
  }

  function toHTML($escape = false)
  {
    return $this->tree->toHTML($escape, $this->mode !== self::TOKEN_CODE);
    $out = '';
    foreach ($this->tree as $node) {
      switch ($node['type']) {
        case 'string':
          if ($node['body'] !== '') {
            $out .= $node['body'];
          }
          break;
        case 'scopeName':
          $out .= '<?=' . self::renderScopeName($node) . '?>';
          break;
        case 'scopeThis':
          $out .= '<?=' . self::renderScopeThis($node) . '?>';
          break;
        case 'expr':
          $out .= '<?=' . self::renderExpr($node) . '?>';
          break;
        default:
          throw new \RuntimeException('Invalid Node:' . $node['type']);
      }
    }
    return $out;
  }

  function toHTMLAtt()
  {
    return $this->toHTML(true);
  }

  function toPHP()
  {
    return $this->tree->toPHP();
    $out = [];
    foreach ($this->tree as $node) {
      switch ($node['type']) {
        case 'string':
          if ($node['body'] !== '') {
            $out[] = self::varToCode($node['body']);
          }
          break;
        case 'scopeThis':
          $out[] = self::renderScopeThis($node);
          break;
        case 'expr':
          $out[] = self::renderExpr($node);
          break;
        default:
          throw new \RuntimeException('Invalid Node:' . $node['type']);
      }
    }
    return join('.', $out);
  }

  function doEval()
  {
    return eval('use Seufert\Hamle; return ' . $this->toPHP() . ';');
  }

  static function varToCode($var)
  {
    if (is_array($var)) {
      $code = [];
      foreach ($var as $key => $value) {
        $code[] = self::varToCode($key) . '=>' . self::varToCode($value);
      }
      return 'array(' . implode(',', $code) . ')'; //remove unnecessary coma
    }
    if (is_bool($var)) {
      return $var ? 'TRUE' : 'FALSE';
    }
    if (is_int($var) || is_float($var) || is_numeric($var)) {
      return $var;
    }
    if ($var instanceof Text) {
      return $var->toPHP();
    }
    if (strpos($var, "\n") !== false) {
      return '"' .
        str_replace(
          ['\\', '$', '"', "\n"],
          ['\\\\', '\$', '\\"', '\\n'],
          $var,
        ) .
        '"';
    }
    return "'" .
      str_replace(['\\', '$', "'"], ['\\\\', '$', "\\'"], $var) .
      "'";
  }

  /**
   * @param $value
   * @return WriteModel
   */
  function setValue($value)
  {
    throw new \RuntimeException('Unsupported');
  }
}
