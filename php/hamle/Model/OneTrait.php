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
  protected $hamleIndex = 0;

  function valid()
  {
    return $this->hamleIndex == 0;
  }

  function key()
  {
    return $this->hamleIndex;
  }

  function current()
  {
    return $this;
  }

  function rewind()
  {
    $this->hamleIndex = 0;
  }

  function next()
  {
    $this->hamleIndex++;
  }
}
