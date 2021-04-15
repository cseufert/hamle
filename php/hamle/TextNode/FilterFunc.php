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

  public function __construct(
    string $func,
    Chainable $chain = null,
    array $args = []
  ) {
    $this->chain = $chain;
    $this->args = $args;
    if (method_exists(Filter::class, $func)) {
      $this->func = Filter::class . '::' . $func;
    } elseif (
      in_array($func, ['round', 'strtoupper', 'strtolower', 'ucfirst'])
    ) {
      $this->func = $func;
    } elseif ($func === 'json') {
      $this->func = 'json_encode';
    } elseif (
      Filter::$filterResolver &&
      ($filter = (Filter::$filterResolver)($func))
    ) {
      $this->func = $filter;
    } else {
      throw new ParseError("Unknown Filter Type \"{$func}\"");
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
    $o = "{$this->func}(" . join(',', $args) . ')';
    if ($this->chain) {
      $o = $this->chain->apply($o);
    }
    return $o;
  }
}
