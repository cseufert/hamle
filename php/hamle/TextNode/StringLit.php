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

  public function empty(): bool
  {
    return $this->body === '';
  }

  /** @param string[] $chars */
  public static function fromArray(array $chars): self
  {
    return new self(join('', $chars));
  }
}
