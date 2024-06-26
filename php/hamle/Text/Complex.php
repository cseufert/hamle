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

use RuntimeException;
use Seufert\Hamle\Exception\ParseError;
use Seufert\Hamle\Model;
use Seufert\Hamle\Runtime\Context;
use Seufert\Hamle\Text;
use Seufert\Hamle\WriteModel;

class Complex extends Text
{
  protected ?Text $func = null;
  /**
   * @var mixed
   */
  protected $sel = null;
  protected ?Filter $filter = null;

  function __construct(string $s)
  {
    if (false !== ($pos = strpos($s, '|'))) {
      $this->filter = new Filter(substr($s, $pos + 1), $this);
      $s = substr($s, 0, $pos);
    }
    $s = preg_split('/-[>!]/', $s);
    if (!$s[0]) {
      throw new ParseError('Unable to parse Complex Expression');
    }
    if ($s[0][1] === '(') {
      $this->func = new Text\Func($s[0]);
    } elseif ($s[0][1] === '[') {
      $this->func = new Text\Scope($s[0]);
    } else {
      $this->func = new SimpleVar($s[0]);
    }
    array_shift($s);
    $this->sel = $s;
  }

  function toHTML(bool $escape = false): string
  {
    if ($escape) {
      return '<?=htmlspecialchars(' . $this->toPHP() . ')?>';
    }
    return '<?=' . $this->toPHP() . '?>';
  }
  function toPHP(): string
  {
    return $this->filter ? $this->filter->toPHP() : $this->toPHPVar();
  }
  function toPHPVar(): string
  {
    assert($this->func !== null, 'Function must be defined to use this method');
    if ($this->sel) {
      $sel = [];
      foreach ($this->sel as $s) {
        $sel[] = "hamleGet('$s')";
      }
      return $this->func->toPHP() . '->' . implode('->', $sel);
    } else {
      return $this->func->toPHP();
    }
  }

  function getOrCreateModel(
    \Seufert\Hamle\Runtime\Scope $scope,
    Context $ctx,
    Model $parent = null
  ): Model {
    if ($this->func instanceof Text\Scope || $this->func instanceof Text\Func) {
      return $this->func->getOrCreateModel($scope, $ctx, $parent);
    }
    throw new RuntimeException(
      'Unsupported func type encountered:' .
        ($this->func ? get_class($this->func) : 'Unknown'),
    );
  }

  /**
   * @param mixed $value
   * @return WriteModel
   */
  function setValue(
    \Seufert\Hamle\Runtime\Scope $scope,
    Context $ctx,
    mixed $value
  ): WriteModel {
    if (!$this->sel || count($this->sel) != 1) {
      throw new RuntimeException(
        'Can only set values, when one var name is present',
      );
    }
    $model = $this->getOrCreateModel($scope, $ctx);
    if (!$model instanceof WriteModel) {
      throw new RuntimeException(
        'Can only set values on WriteModel, got ' . get_class($model),
      );
    }
    $model->hamleSet($this->sel[0], $value);
    return $model;
  }
}
