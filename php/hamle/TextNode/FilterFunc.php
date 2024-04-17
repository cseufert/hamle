<?php

namespace Seufert\Hamle\TextNode;

use Seufert\Hamle\Exception\ParseError;
use Seufert\Hamle\Text;
use Seufert\Hamle\Text\Filter;

class FilterFunc implements Chainable
{
  use ChainTrait;

  public string $func;
  public ?Chainable $chain;
  public array $args;

  private string $name = '';

  public function __construct(
    string $func,
    Chainable $chain = null,
    array $args = []
  ) {
    $this->chain = $chain;
    $this->args = $args;
    if (in_array($func, ['strtoupper', 'strtolower', 'ucfirst', 'round'])) {
      $this->func = $func;
    } elseif ($func === 'json') {
      $this->func = 'json_encode';
    } else {
      $this->func = '';
      $this->name = Text::varToCode($func);
    }
  }

  public function apply(string $out): string
  {
    $args = array_map(
      fn($v): string => $v instanceof Literal
        ? Text::varToCode($v->string())
        : $v->toPHP(),
      $this->args,
    );
    array_unshift($args, $out);
    if ($this->name !== '') {
      $o =
        "\$ctx->hamleFilter(\$scope,{$this->name}," . implode(',', $args) . ')';
    } else {
      $o = "{$this->func}(" . join(',', $args) . ')';
    }
    if ($this->chain) {
      $o = $this->chain->apply($o);
    }
    return $o;
  }
}
