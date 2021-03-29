<?php


namespace Seufert\Hamle\TextNode;


use Seufert\Hamle\Hamle;
use Seufert\Hamle\Text;

class Query implements Evaluated
{
  use ChainTrait, ImmediateTrait;

  public array $filters;

  public ?Chainable $chain = null;

  private ?Chainable $immediate;

  public function __construct(array $filters = [], ?RelQuery $related = null) {
    $this->filters = $filters;
    $this->immediate = $related;
  }

  public function toPHP(): string
  {
    $o = '';
    $id = null;
    $type = [];
    foreach ($this->filters ?? [] as $q) {
      if ($q['q'] === 'id')
        $id = $q['id'] ?? null;
      if ($q['q'] === 'type')
        $type = $q['id'];
    }
    if (!$this->filters) {
      $o = 'Hamle\Scope::get(0)';
    } elseif ($id !== null) {
      $o = self::queryId($this->filters);
    } else {
      $o = 'Hamle\Run::modelTypeTags(' . self::queryParams($this->filters) . ')';
    }
    if($this->immediate) $o = $this->immediate->apply($o);
    if($this->chain) $o = $this->chain->apply($o);
    return $o;
  }

  static function queryParams(array $query, bool $addGroup = false)
  {
    $lastType = '*';
    $typeTags = [];
    $limit = 0;
    $offset = 0;
    $group = 0;
    $sort = [];
    $o = '';
    foreach ($query as $q) {
      switch ($q['q']) {
        case 'type':
          $typeTags[$lastType = $q['id']] = [];
          break;
        case 'tag':
          $typeTags[$lastType][] = $q['id'];
          break;
        case 'group':
          $group = $q['id'];
          break;
        case 'range':
          $limit = $q['limit'];
          $offset = $q['offset'];
          break;
        case 'sort':
          $sd = $q['id'];
          if(!$sd) {
            $sort[''] = Hamle::SORT_RANDOM;
          } elseif($sd[0] === '-') {
            $sort[substr($sd, 1)] = Hamle::SORT_DESCENDING;
          }else {
            $sort[$sd] = Hamle::SORT_ASCENDING;
          }
      }
    }
    $opt = [
      Text::varToCode($typeTags),
      Text::varToCode($sort),
      Text::varToCode($limit),
      Text::varToCode($offset)
    ];
    if ($addGroup)
      $opt[] = Text::varToCode($group);
    return join(',', $opt);
  }

  static function queryId(array $query)
  {
    $type = '';
    $id = '';
    $limit = 0;
    $offset = 0;
    $sort = [];
    foreach ($query as $q) {
      switch ($q['q']) {
        case 'type':
          $type = $q['id'];
          break;
        case 'id':
          $id = $q['id'];
          break;
        case 'range':
          $limit = $q['limit'];
          $offset = $q['offset'];
          break;
      }
    }
    $opt = [
      Text::varToCode([$type => [$id]]),
      Text::varToCode($sort),
      Text::varToCode($limit),
      Text::varToCode($offset)
    ];
    if(!$type || $type === '*') {
      $opt[0] = Text::varToCode($id);
      return 'Hamle\Run::modelId(' . join(',', $opt) . ')';
    }
    return 'Hamle\Run::modelTypeId(' . join(',', $opt) . ')';
  }

}
