<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 19/10/2017
 * Time: 4:25 PM
 */

namespace Seufert\Hamle\Model;

trait OneTrait
{
  protected int $hamleIndex = 0;

  function valid(): bool
  {
    return $this->hamleIndex == 0;
  }

  function key(): mixed
  {
    return $this->hamleIndex;
  }

  function current(): mixed
  {
    return $this;
  }

  function rewind(): void
  {
    $this->hamleIndex = 0;
  }

  function next(): void
  {
    $this->hamleIndex++;
  }
}
