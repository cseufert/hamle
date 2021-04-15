<?php

namespace Seufert\Hamle\TextNode;

trait ImmediateTrait
{
  /**
   * @param Chainable|null $immediate
   * @return static
   */
  public function withImmediate(?Chainable $immediate = null)
  {
    $new = clone $this;
    $new->immediate = $immediate;
    return $new;
  }

  /**
   * @param Chainable[] $stack
   * @return static
   */
  public function withImmStack(array $stack = [])
  {
    $new = clone $this;
    if ($stack) {
      $top = array_pop($stack);
      while ($stack) {
        $top = array_pop($stack)->withChain($top);
      }
      $new->immediate = $top;
    } else {
      $new->immediate = null;
    }
    return $new;
  }
}
