<?php

namespace Seufert\Hamle\TextNode;

class RawOutput implements Evaluated
{
  public function __construct(private Evaluated $expression) {}

  public function toPHP(): string
  {
    return $this->expression->toPHP();
  }
}
