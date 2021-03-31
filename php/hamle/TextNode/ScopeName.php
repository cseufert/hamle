<?php

namespace Seufert\Hamle\TextNode;

use Seufert\Hamle\Text;

class ScopeName implements Evaluated
{
  use ChainTrait, ImmediateTrait;

  public string $name;

  public ?Chainable $chain;

  public ?Chainable $immediate;

  public function __construct(
    string $name,
    ?Chainable $chain = null,
    ?Chainable $immediate = null
  ) {
    $this->name = $name;
    $this->chain = $chain;
    $this->immediate = $immediate;
  }

  public function withImmediate(RelQuery $query)
  {
    $new = clone $this;
    $new->immediate = $query;
    return $new;
  }

  public function toPHP(): string
  {
    $o = 'Hamle\\Scope::getName(' . Text::varToCode($this->name) . ')';
    if ($this->immediate) {
      $o = $this->immediate->apply($o);
    }
    if ($this->chain) {
      $o = $this->chain->apply($o);
    }
    return $o;
  }

  public function toHTML($escape = false)
  {
  }
}
