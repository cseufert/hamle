<?php
declare(strict_types=1);

namespace Seufert\Hamle;

use Seufert\Hamle\Exception\OutOfScope;

enum NoScopeMode
{
  case Throw;

  case ReturnZero;

  case ReturnUnknown;

  /**
   * @param string $msg Message Text to include in error
   * @throws OutOfScope
   */
  public function get(string $msg): Model
  {
    if ($this === self::Throw) {
      throw new OutOfScope("Unable to find Scope ($msg)");
    }
    if ($this === self::ReturnUnknown) {
      return new Model\Unknown($msg);
    }

    if ($this === self::ReturnZero) {
      return new Model\Zero();
    }
  }
}
