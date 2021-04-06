<?php

namespace Seufert\Hamle\TextNode;

interface Chainable
{
  public function apply(string $out): string;

  /**
   * @param Chainable|null $chain
   * @return static
   */
  public function withChain(?Chainable $chain);
}
