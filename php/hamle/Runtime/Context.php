<?php

namespace Seufert\Hamle\Runtime;

use Seufert\Hamle\Model;

interface Context
{
  public function hamleInclude(Scope $scope, string $name): string;

  public function hamleFilter(
    Scope $scope,
    string $filter,
    mixed ...$args
  ): mixed;

  public function hamleFindId(
    Scope $scope,
    string $id,
    array $sort = [],
    int $limit = 0,
    int $offset = 0
  ): Model;

  /**
   * @param Scope $scope
   * @param array<string,string> $typeTags
   * @param array<string,\Seufert\Hamle\Hamle::SORT_*> $sort
   * @param int $limit
   * @param int $offset
   * @return Model
   */
  public function hamleFindTypeId(
    Scope $scope,
    array $typeId,
    array $sort = [],
    int $limit = 0,
    int $offset = 0
  ): Model;

  /**
   * @param Scope $scope
   * @param array<string,string> $typeTags
   * @param array<string,\Seufert\Hamle\Hamle::SORT_*> $sort
   * @param int $limit
   * @param int $offset
   */
  public function hamleFindTypeTags(
    Scope $scope,
    array $typeTags,
    array $sort = [],
    int $limit = 0,
    int $offset = 0
  ): Model;
}
