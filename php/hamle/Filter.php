<?php

namespace Seufert\Hamle;
/**
 * HAMLE Exception base class
 *
 * @author Chris Seufert <chris@seufert.id.au>
 * @package hamle
 *
 */

abstract class Filter
{
  static function stTag(): string
  {
    return '';
  }

  static function filterText(string $s): string
  {
    return $s;
  }

  static function ndTag(): string
  {
    return '';
  }
}
