<?php


namespace Seufert\Hamle\TextNode;


class IntLit implements Literal
{
  public int $int;

  public function __construct(int $int)
  {
    $this->int = $int;
  }

  public function string(): string
  {
    return (string)$this->int;
  }
}
