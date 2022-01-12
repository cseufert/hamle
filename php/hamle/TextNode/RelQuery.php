<?php

namespace Seufert\Hamle\TextNode;

use Seufert\Hamle\Hamle;

class RelQuery implements Chainable
{
  use ChainTrait;

  public int $rel;
  public array $filters;

  public ?Chainable $chain = null;

  public function __construct(int $rel = Hamle::REL_CHILD, array $filters = [])
  {
    $this->rel = $rel;
    $this->filters = $filters;
  }

  static function for(string $rel, array $filters): self
  {
    return new self(
      $rel === '>' ? Hamle::REL_CHILD : Hamle::REL_PARENT,
      $filters,
    );
  }

  public function apply(string $out): string
  {
    $out =
      $out .
      "->hamleRel({$this->rel}," .
      Query::queryParams($this->filters, true) .
      ')';
    if ($this->chain) {
      $out = $this->chain->apply($out);
    }
    return $out;
  }
}
