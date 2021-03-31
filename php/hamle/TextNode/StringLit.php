<?php

namespace Seufert\Hamle\TextNode;

class StringLit implements Literal
{
  public string $body;

  public function __construct(string $body)
  {
    $this->body = $body;
  }

  public function string(): string
  {
    return $this->body;
  }
}
