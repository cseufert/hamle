<?php

namespace Seufert\Hamle\Runtime;

use Seufert\Hamle\Model;

interface Scope
{
  public function model(): Model;

  public function withModel(Model $m): Scope;

  public function lastScope(): Scope;

  public function modelNum(int $id): Model;

  public function setNamedModel(string $name): void;

  public function namedModel(string $name): Model;
}
