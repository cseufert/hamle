<?php

namespace Seufert\Hamle\TextNode;

use Seufert\Hamle\Text;

class Doc
{
  /**
   * @var Literal[]|Evaluated[]
   */
  public array $body;

  public function __construct(array $body)
  {
    $this->body = $body;
  }

  public function toPHP(): string
  {
    $out = [];
    foreach ($this->body as $n) {
      if ($n instanceof Literal) {
        $s = $n->string();
        if ('' !== $s) {
          $out[] = Text::varToCode($s);
        }
      } else {
        $out[] = $n->toPHP();
      }
    }
    return join('.', $out);
  }

  public function toHtml(
    bool $escVar = false,
    bool $escFixed = true,
    bool $allowRaw = true,
  ): string {
    $out = [];
    foreach ($this->body as $n) {
      if ($n instanceof Literal) {
        $s = $n->string();
        if ($escFixed) {
          $s = htmlspecialchars($s);
        }
        $out[] = $s;
      } else {
        $php = $n->toPHP();
        if ($escVar && !($allowRaw && $n instanceof RawOutput)) {
          $php = 'htmlspecialchars(' . $php . ')';
        }
        $out[] = '<?=' . $php . '?>';
      }
    }
    return join('', $out);
  }
}
