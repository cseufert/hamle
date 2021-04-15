<?php

namespace Seufert\Hamle\TextNode;

class FloatLit implements Literal
{
  private float $float;

  public function __construct(float $float)
  {
    $this->float = $float;
  }

  public function string(): string
  {
    return (string) $this->float;
  }
}
