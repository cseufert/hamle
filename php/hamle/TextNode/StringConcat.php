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

  /**
   * @param list<Evaluated|Literal|null> $nodes
   */
  static function fromList(array $nodes): self
  {
    return new self(
      array_filter(
        $nodes,
        fn($i) => $i && (!$i instanceof Literal || $i->string() !== ''),
      ),
    );
  }
}
