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

  public function toPHP()
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

  public function toHtml(bool $escVar = false,bool $escFixed = true): string
  {
    $out = [];
    if ($escVar) {
      $openTag = '<?=htmlspecialchars(';
      $closeTag = ')?>';
    } else {
      $openTag = '<?=';
      $closeTag = '?>';
    }
    $code = false;
    foreach ($this->body as $n) {
      if ($n instanceof Literal) {
        if ($code) {
          $out[] = $closeTag;
          $code = false;
        }
        $s = $n->string();
        if($escFixed) $s = htmlspecialchars($s);
        $out[] = $s;
      } else {
        if (!$code) {
          $out[] = $openTag;
          $code = true;
        }
        $out[] = $n->toPHP();
      }
    }
    if ($code) $out[] = $closeTag;
    return join('', $out);
  }
}
