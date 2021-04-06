<?php

namespace Seufert\Hamle\TextNode;

trait ChainTrait
{
  /**
   * @param Chainable|null $chain
   * @return static
   */
  public function withChain(?Chainable $chain = null)
  {
    $new = clone $this;
    $new->chain = $chain;
    return $new;
  }
}
