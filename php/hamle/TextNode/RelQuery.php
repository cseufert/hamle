<?php

namespace Seufert\Hamle\TextNode;

use Seufert\Hamle\Hamle;
use Seufert\Hamle\Text;

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

  static function for(string $rel, array $filters)
  {
    return new self(
      $rel === ">" ? Hamle::REL_CHILD : Hamle::REL_PARENT,
      $filters
    );
  }

  public function apply(string $s): string
  {
    $s =
      $s .
      "->hamleRel({$this->rel}," .
      Query::queryParams($this->filters, true) .
      ")";
    if ($this->chain) {
      $s = $this->chain->apply($s);
    }
    return $s;
  }
}
