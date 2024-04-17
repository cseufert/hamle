<?php

use Seufert\Hamle\Model\Zero;
use Seufert\Hamle\Text;

require_once 'base.php';

class writeTest extends base
{
  public function testWrite1()
  {
    $hs = new Text\Complex("$[write]->title");
    $m = new writeTestModel();
    $m->values['title'] = 'abc';
    $scope = new \Seufert\Hamle\Scope(new Zero());
    $scope->withModel($m)->setNamedModel('write');
    $this->assertEquals('abc', $m->values['title']);
    $hs->setValue($scope, $this->ctx, 'test');
    $this->assertEquals('test', $m->values['title']);
  }

  public function testWrite2()
  {
    $hs = new Text\Complex("$($[write] > write)->new");
    $m = new writeTestModel();

    $scope = new \Seufert\Hamle\Scope(new Zero());
    $scope->withModel($m)->setNamedModel('write');
    $m = $hs->setValue($scope, $this->ctx, 'test');
    $this->assertEquals('test', $m->values['new']);
  }
}

class writeTestModel implements \Seufert\Hamle\WriteModel
{
  use \Seufert\Hamle\Model\OneTrait;

  public $values = [];

  function hamleGet(string $key): mixed
  {
    return $this->values[$key];
  }

  function hamleRel(
    int $rel,
    array $typeTags,
    array $sort = [],
    int $limit = 0,
    int $offset = 0,
    int $grouptype = 1
  ): \Seufert\Hamle\Model {
    return new Zero();
  }

  /**
   * Set a HAMLE model value
   *
   * @param string $key
   * @param mixed $value
   * @return \Seufert\Hamle\WriteModel
   */
  public function hamleSet(string $key, mixed $value): \Seufert\Hamle\WriteModel
  {
    $this->values[$key] = $value;
    return $this;
  }

  function hamleCreateRel(
    int $rel,
    array $typeTags,
    array $sort = [],
    int $limit = 0,
    int $offset = 0,
    int $grouptype = 1
  ): \Seufert\Hamle\WriteModel {
    if ($rel = Seufert\Hamle\Hamle::REL_CHILD) {
      $m = new self();
      $m->values['title'] = 'new';
      return $m;
    }
    return new Zero();
  }
}
