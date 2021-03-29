<?php


namespace Seufert\Hamle\TextNode;


interface Chainable
{

  public function apply(string $out):string;

  public function withChain(?Chainable $chain);

}
