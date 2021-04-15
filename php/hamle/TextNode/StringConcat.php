<?php

namespace Seufert\Hamle\TextNode;

use Seufert\Hamle\Text;

class StringConcat implements Evaluated
{
  public array $items;

  public function __construct(array $items)
  {
    $this->items = $items;
  }

  public function toPHP(): string
  {
    $o = [];
    foreach ($this->items as $i) {
      if ($i instanceof Literal) {
        $o[] = Text::varToCode($i->string());
      } else {
        $o[] = $i->toPHP();
      }
    }
    if (!$o) {
      return Text::varToCode('');
    }
    return implode('.', $o);
  }

  static function fromParser(
    array $chars,
    ?Evaluated $expr = null,
    $rhs = null
  ): self {
    $o = [];
    if ($chars) {
      $o[] = StringLit::fromArray($chars);
    }
    $o[] = $expr;
    if ($rhs instanceof StringLit) {
      if (!$rhs->empty()) {
        $o[] = $rhs;
      }
    } elseif ($rhs instanceof StringConcat) {
      foreach ($rhs->items as $i) {
        $o[] = $i;
      }
    } else {
      $o[] = $rhs;
    }
    return new self($o);
  }
}
