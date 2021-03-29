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

  const START_RULE_MAP = [self::TOKEN_HTML => 'HtmlInput', self::TOKEN_CODE => 'CodeInput',self::TOKEN_CONTROL => 'ControlInput'];

  protected $mode;

  protected $tree;

  function __construct($s, $mode = self::TOKEN_HTML)
  {
//    var_dump($s);
    $this->mode = $mode;
    $this->tree = (new Parser())->parse($s,['startRule' => self::START_RULE_MAP[$mode]]);
//    var_dump($this->tree);
    if(!$this->tree instanceof Doc) {
      $this->tree = new Doc(is_array($this->tree) ? $this->tree : [$this->tree]);
    }
//    $m = [];
//    $pos = 0;
//    $this->nodes = [];
//    $rFlag = PREG_OFFSET_CAPTURE + PREG_SET_ORDER;
//    if(trim($s) === '') {
//      $this->nodes[] = new Text\Plain($s, $mode);
//      return;
//    }
//    if($mode === self::TOKEN_CONTROL) {
//      if(preg_match('/^"(.*)"$/', trim($s), $m)) {
//        $this->nodes[] = new Text($m[1]);
//      }
//      else {
//        $this->nodes[] = new Text\Complex(trim($s));
//      }
//      return;
//    }
//    preg_match_all(self::REGEX_HTML, $s, $m, $rFlag);
//    foreach($m as $match) {
//      if($mode & self::FIND_BARDOLLAR && isset($match[2])) {
//        if($match[2][1] != $pos) {
//          $this->nodes[] = new Text\Plain(
//            substr($s, $pos, $match[2][1] - $pos), $mode);
//        }
//        $this->nodes[] = new Text\Complex(substr($match[2][0], 1, -1));
//        $pos = $match[2][1] + strlen($match[2][0]);
//      }
//      else if($mode & self::FIND_DOLLARVAR) {
//        if($match[1][1] > 0 && $s[$match[1][1] - 1] === '\\') {
//          continue;
//        }
//        if($match[1][1] != $pos) {
//          $this->nodes[] = new Text\Plain(
//            substr($s, $pos, $match[1][1] - $pos), $mode);
//        }
//        $this->nodes[] = new Text\SimpleVar($match[1][0]);
//        $pos = $match[1][1] + strlen($match[1][0]);
//      }
//    }
//    if($pos != strlen($s)) {
//      $this->nodes[] = new Text\Plain(substr($s, $pos), $mode);
//    }
  }

  private static function addFilter(string $o, array $filter): string
  {
    $func = $filter['func'];
    if (method_exists(Filter::class, $func)) {
      $func = Filter::class . '::' . $func;
    } elseif (in_array($func, ['round', 'strtoupper', 'strtolower', 'ucfirst'])) {
    } elseif ($func === 'json') {
      $func = 'json_encode';
    } elseif (Filter::$filterResolver && $filter = (Filter::$filterResolver)($func)) {
      $func = $filter;
    } else {
      throw new ParseError("Unknown Filter Type \"{$func}\"");
    }
    $args = join(',', array_map(function($v) {
      if(is_array($v) && $v['type'] ?? false === 'expr') {
        return self::renderExpr($v);
      } else
        return self::varToCode($v);
    } , $filter['args']));
    if(strlen($args)) $args = ','.$args;
    $o = "$func($o" . $args . ")";
    if($filter['chain'] ?? false) {
      $o = self::addFilter($o, $filter['chain']);
    }
    return $o;
  }

  static function renderScopeThis($n)
  {
    $o = 'Hamle\Scope::get()->hamleGet(' . self::varToCode($n['name']) . ')';
    $o = self::addParams($o, $n['param'] ?? []);
    return $o;
  }

  static function renderScopeId($n)
  {
    $o = 'Hamle\Scope::get(' . $n['id'] . ')';
    $o = self::addParams($o, $n['param'] ?? []);
    return $o;
  }

  static function renderScopeName($n)
  {
    $o = 'Hamle\Scope::getName(' . self::varToCode($n['name']) . ')';
    $o = self::addParams($o, $n['param'] ?? []);
    return $o;
  }

  static function addParams(string $o, array $params)
  {
    while ($params['type'] ?? null === 'sub') {
      $o .= '->hamleGet(' . self::varToCode($params['name']) . ')';
      $params = $params['params'] ?? [];
    }
    return $o;
  }

  static function addRel(string $o, array $query, string $rel): string
  {
    $r = $rel === 'child' ? Hamle::REL_CHILD : Hamle::REL_PARENT;
    $o = $o . "->hamleRel(" . self::varToCode($r) . ',' . self::queryParams($query, true) . ')';
    return $o;
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
          $typeTags[$lastType = $q['id']] = [];
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
      self::varToCode($offset)
    ];
    if ($addGroup)
      $opt[] = self::varToCode($group);
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
      self::varToCode($offset)
    ];
    return 'Hamle\Run::modelTypeId(' . join(',', $opt) . ')';
  }

  static function renderQuery($n)
  {
    $o = '';
    $id = null;
    $type = [];
    foreach ($n['query'] ?? [] as $q) {
      if ($q['q'] === 'id')
        $id = $q['id'] ?? null;
      if ($q['q'] === 'type')
        $type = $q['id'];
    }
    if ($n['query'] === null) {
      $o = 'Hamle\Scope::get(0)';
    } elseif ($id !== null) {
      $o = self::queryId($n['query']);
    } else {
      $o = 'Hamle\Run::modelTypeTags(' . self::queryParams($n['query']) . ')';
    }
    if ($n['sub'] ?? []) {
      $o = self::addRel($o, $n['sub'], $n['rel']);
    }
    if ($n['param'] ?? []) {
      $o = self::addParams($o, $n['param']);
    }
    return $o;
  }

  static function renderExpr(array $expr)
  {
    switch ($expr['body']['type']) {
      case 'scopeThis':
        $o = self::renderScopeThis($expr['body']);
        break;
      case 'scopeId':
        $o = self::renderScopeId($expr['body']);
        break;
      case 'scopeName':
        $o = self::renderScopeName($expr['body']);
        break;
      case 'query':
        $o = self::renderQuery($expr['body']);
        break;
      default:
        throw new \RuntimeException('Invalid Node: ' . $expr['body']['type']);
    }
    if ($expr['body']['filter'] ?? false) {
      $o = self::addFilter($o, $expr['body']['filter']);
    }
    return $o;
  }

  function toHTML($escape = false)
  {
    return $this->tree->toHTML($escape, $this->mode !== self::TOKEN_CODE);
    $out = '';
    foreach ($this->tree as $node) {
      switch ($node['type']) {
        case 'string':
          if ($node['body'] !== '')
            $out .= $node['body'];
          break;
        case 'scopeName':
          $out .= '<?=' . self::renderScopeName($node) . '?>';
          break;
        case 'scopeThis':
          $out .= '<?=' . self::renderScopeThis($node) . '?>';
          break;
        case 'expr':
          $out .= '<?=' . self::renderExpr($node) . "?>";
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
          if ($node['body'] !== '')
            $out[] = self::varToCode($node['body']);
          break;
        case 'scopeThis':
          $out[] = self::renderScopeThis($node);
          break;
        case 'expr':
          $out [] = self::renderExpr($node);
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
      return ($var ? 'TRUE' : 'FALSE');
    }
    if (is_int($var) || is_float($var) || is_numeric($var)) {
      return $var;
    }
    if ($var instanceof Text) {
      return $var->toPHP();
    }
    return "'" . str_replace(['$', "'"], ['$', "\\'"], $var) . "'";
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

