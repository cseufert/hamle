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

use Seufert\Hamle;
use Seufert\Hamle\Model;
use Seufert\Hamle\Runtime\Context;
use Seufert\Hamle\Text;
use Seufert\Hamle\Exception\ParseError;

class Func extends SimpleVar
{
  const REGEX_FUNCSEL = '[a-zA-Z0-9\*\.,#_:\\^\\-@\\${}[\]]';

  protected ?FuncSub $sub = null;

  /** @var bool|Scope */
  protected $scope = false;

  protected array $filt = [];

  protected array $sortlimit = [];

  /**
   * Func constructor.
   * @param string $s
   */
  public function __construct($s)
  {
    $m = [];
    if (!preg_match('/^\$\((' . self::REGEX_FUNCSEL . '*)(.*)\)$/', $s, $m)) {
      throw new ParseError("Unable to read \$ func in '$s'");
    }
    if (trim($m[2])) {
      $this->sub = new FuncSub($m[2]);
    }
    if (!trim($m[1])) {
      $this->scope = true;
      return;
    }
    if ($m[1][0] == '$' && $m[1][1] == '[') {
      $this->scope = new Scope($m[1]);
      return;
    }
    $this->sortlimit = $this->attSortLimit($m[1]);
    $this->filt = $this->attIdTag($m[1]);
  }

  public function attIdTag(string &$s): array
  {
    $m = [];
    $att = ['id' => [], 'tag' => []];
    foreach (explode(',', $s) as $str) {
      if (preg_match('/^[a-zA-Z0-9_]+/', $str, $m)) {
        $type = $m[0];
      } else {
        $type = '*';
      }
      if (preg_match('/#([a-zA-Z0-9_${}]+)/', $str, $m)) {
        $att['id'][$type][] = $m[1];
      } elseif (preg_match_all('/\\.([a-zA-Z0-9_\-${}]+)/', $str, $m)) {
        foreach ($m[1] as $tag) {
          $att['tag'][$type][] = new Text($tag, Text::TOKEN_CODE);
        }
      } else {
        $att['tag'][$type] = [];
      }
    }
    if (!(count($att['id']) xor count($att['tag']))) {
      throw new ParseError('Only tag, type or id can be combined');
    }
    return $att;
  }

  public function attSortLimit(string &$s): array
  {
    $att = ['limit' => 0, 'offset' => 0, 'sort' => []];
    $m = [];
    if (preg_match('/:(?:([0-9]+)-)?([0-9]+)/', $s, $m)) {
      $att['limit'] = $m[2];
      $att['offset'] = $m[1] ? $m[1] : 0;
    }
    $rand = false;
    if (preg_match_all('/\\^(-?)([a-zA-Z0-9_]*)/', $s, $m)) {
      foreach ($m[0] as $k => $mv) {
        if ($m[2][$k]) {
          $dir =
            $m[1][$k] == '-'
              ? Hamle\Hamle::SORT_DESCENDING
              : Hamle\Hamle::SORT_ASCENDING;
          $att['sort'][$m[2][$k]] = $dir;
        } else {
          $rand = true;
        }
      }
    }
    if ($rand) {
      $att['sort'] = ['' => ($att['dir'] = Hamle\Hamle::SORT_RANDOM)];
    }
    return $att;
  }

  public function attGroupType(string &$s): array
  {
    $att = ['grouptype' => 0];
    $m = [];
    if (preg_match('/@([0-9]+)/', $s, $m)) {
      $att['grouptype'] = $m[1];
    }
    return $att;
  }

  /**
   * @return string PHP Code
   */
  public function toPHP(): string
  {
    $sub = $this->sub ? '->' . $this->sub->toPHP() : '';
    if ($this->scope instanceof Scope) {
      return $this->scope->toPHP() . $sub;
    } elseif ($this->scope === true) {
      return "Hamle\\Scope::get(0)$sub";
    }
    $limit =
      Text::varToCode($this->sortlimit['sort']) .
      ',' .
      $this->sortlimit['limit'] .
      ',' .
      $this->sortlimit['offset'];
    if (count($this->filt['tag'])) {
      return '$ctx->hamleFindTypeTags($scope,' .
        Text::varToCode($this->filt['tag']) .
        ",$limit)$sub";
    }
    if (count($this->filt['id'])) {
      if (isset($this->filt['id']['*']) && count($this->filt['id']['*']) == 1) {
        return '$ctx->hamleFindId($scope,' .
          Text::varToCode(current($this->filt['id']['*'])) .
          ",$limit)$sub";
      } else {
        return '$ctx->hamleFindTypeId($scope,' .
          Text::varToCode($this->filt['id']) .
          ",$limit)$sub";
      }
    }
    return '';
  }

  /**
   * @param Model|null $parent
   * @param Context $ctx
   * @return Model
   */
  public function getOrCreateModel(
    Hamle\Runtime\Scope $scope,
    Context $ctx,
    Model $parent = null
  ): Model {
    if ($this->scope instanceof Scope) {
      $parent = $this->scope->getOrCreateModel($scope, $ctx);
    } elseif ($this->scope === true) {
      $parent = $scope->model();
    }
    if ($this->filt && count($this->filt['tag'])) {
      $parent = $ctx->hamleFindTypeTags(
        $scope,
        $this->filt['tag'],
        $this->sortlimit['sort'],
        $this->sortlimit['limit'],
        $this->sortlimit['offset'],
      );
    }
    if ($this->filt && count($this->filt['id'])) {
      if (
        isset($this->filt['id']['*']) &&
        count($this->filt['id']['*']) === 1
      ) {
        $parent = $ctx->hamleFindId(
          $scope,
          current($this->filt['id']['*']),
          $this->sortlimit['sort'],
          $this->sortlimit['limit'],
          $this->sortlimit['offset'],
        );
      } else {
        $parent = $ctx->hamleFindTypeId(
          $scope,
          $this->filt['id'],
          $this->sortlimit['sort'],
          $this->sortlimit['limit'],
          $this->sortlimit['offset'],
        );
      }
    }
    if ($this->sub) {
      return $this->sub->getOrCreateModel($scope, $ctx, $parent)->current();
    }
    if (!$parent) {
      throw new \RuntimeException('Unable to create model with no relation');
    }
    return $parent->current();
  }

  public function toHTML(bool $escape = false): string
  {
    throw new ParseError('Unable to use Scope operator in HTML Code');
  }
}
