<?php


namespace Seufert\Hamle\TextNode;


trait ChainTrait
{

  public function withChain(?Chainable $chain = null) {
    $new = clone $this;
    $new->chain = $chain;
    return $new;
  }

}
