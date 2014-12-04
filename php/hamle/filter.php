<?php

namespace Seufert\Hamle;
/**
 * HAMLE Exception base class
 *
 * @author Chris Seufert <chris@seufert.id.au>
 * @package hamle
 *
 */

abstract class Filter {
  static function stTag() {
    return "";
  }

  static function filterText($s) {
    return $s;
  }

  static function ndTag() {
    return "";
  }

}
