<?php
namespace Seufert\Hamle;

use Seufert\Hamle\Exception\OutOfScope;
use Seufert\Hamle\Exception\Unsupported;
use Seufert\Hamle\Exception\RunTime;

/**
 * HAMLE Controller/Model Scope Handler
 *
 * @author Chris Seufert <chris@seufert.id.au>
 */
class Scope implements \Seufert\Hamle\Runtime\Scope
{
  public Scope|null $lastScope = null;

  /** @var list<Model> $parents  Parent Scopes */
  public array $parents = [];

  public ?Scope $root = null;

  public function __construct(public Model $model)
  {
    $this->parents = [$model];
  }

  public function model(): Model
  {
    return $this->model;
  }

  public function modelNum(int $id): Model
  {
    if (0 === $id) {
      return $this->model;
    }
    $key = $id - 1;
    if ($id < 0) {
      $key = count($this->parents) + $id - 1;
    }
    return $this->parents[$key] ??
      throw new OutOfScope("Unable to find Scope ($id)");
  }

  public function lastScope(): \Seufert\Hamle\Runtime\Scope
  {
    return $this->lastScope ?? $this;
  }

  public function withModel(Model $m): \Seufert\Hamle\Runtime\Scope
  {
    $new = new self($m);
    $new->lastScope = $this;
    $new->parents = [...$this->parents, $this->model];
    $new->root = $this->root ?? $this;
    return $new;
  }

  /** @var array<string,Model> */
  public array $namedModels = [];

  public function setNamedModel(string $name): void
  {
    $root = $this->root ?? $this;
    $root->namedModels[$name] = $this->model;
  }

  public function namedModel(string $name): Model
  {
    $root = $this->root ?? $this;
    $model =
      $root->namedModels[$name] ??
      throw new RunTime("Unable to find named model ($name)");
    $model->rewind();
    return $model;
  }
}
