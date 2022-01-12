<?php

namespace Seufert\Hamle\TextNode;

class ScopeId implements Evaluated
{
  use ChainTrait, ImmediateTrait;

  public int $id;

  public ?Chainable $chain;

  public ?Chainable $immediate;

  public function __construct(
    ?IntLit $id,
    ?Chainable $chain = null,
    ?Chainable $immediate = null
  ) {
    $this->id = $id ? $id->int : 0;
    $this->chain = $chain;
    $this->immediate = $immediate;
  }

  public function withImmediate(RelQuery $query): self
  {
    $new = clone $this;
    $new->immediate = $query;
    return $new;
  }

  function toPHP(): string
  {
    if ($this->id === 0) {
      $o = 'Hamle\\Scope::get()';
    } else {
      $o = "Hamle\\Scope::get({$this->id})";
    }
    if ($this->immediate) {
      $o = $this->immediate->apply($o);
    }
    if ($this->chain) {
      $o = $this->chain->apply($o);
    }
    return $o;
  }
}
