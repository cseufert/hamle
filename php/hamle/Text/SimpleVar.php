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

use Seufert\Hamle\Model;
use Seufert\Hamle\Text;
use Seufert\Hamle\WriteModel;

class SimpleVar extends Text
{
  protected string $var = '';

  protected ?Filter $filter = null;

  function __construct(string $s)
  {
    if (false !== ($pos = strpos($s, '|'))) {
      $this->var = substr($s, 1, $pos - 1);
      $this->filter = new Filter(substr($s, $pos + 1), $this);
    } else {
      $this->var = substr($s, 1);
    }
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
    return 'Hamle\\Scope::get()->hamleGet(' . Text::varToCode($this->var) . ')';
  }

  function getOrCreateModel(Model $parent = null): Model
  {
    return \Seufert\Hamle\Scope::get()->current();
  }

  /**
   * @param mixed $value
   * @return WriteModel
   */
  function setValue(mixed $value): WriteModel
  {
    $model = $this->getOrCreateModel();
    if (!$model instanceof WriteModel) {
      throw new \RuntimeException(
        'Can only write to model that implements WriteModel',
      );
    }
    $model->hamleSet($this->var, $value);
    return $model;
  }
}
