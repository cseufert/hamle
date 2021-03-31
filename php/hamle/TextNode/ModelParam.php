<?php

namespace Seufert\Hamle\TextNode;

use Seufert\Hamle\Text;

class ModelParam implements Chainable
{
  use ChainTrait;

  public string $name;

  public ?Chainable $chain;

  public function __construct(string $name, ?Chainable $chain = null)
  {
    $this->name = $name;
    $this->chain = $chain;
  }

  public function apply(string $out): string
  {
    $o = "{$out}->hamleGet(" . Text::varToCode($this->name) . ")";
    if ($this->chain) {
      $o = $this->chain->apply($o);
    }
    return $o;
  }
}
