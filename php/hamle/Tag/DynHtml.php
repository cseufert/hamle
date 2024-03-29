<?php
/*
This project is Licenced under The MIT License (MIT)

Copyright (c) 2014 Christopher Seufert

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

 */
namespace Seufert\Hamle\Tag;

use Seufert\Hamle as H;
use Seufert\Hamle\Exception\ParseError;

class DynHtml extends Html
{
  static int $var = 0;
  protected string $varname;
  protected string $baseType;

  function __construct(
    string $tag,
    array $class,
    array $param,
    string $id,
    string $ref
  ) {
    parent::__construct($tag, $class, $param, $id);
    $this->source[] = $ref;
    $this->baseType = $tag;
    self::$var++;
    $this->varname = "\$dynhtml" . self::$var;
  }

  function render(int $indent = 0, bool $minify = false): string
  {
    $data = H\Text::varToCode([
      'base' => $this->baseType,
      'type' => $this->type,
      'opt' => $this->opt,
      'source' => $this->source,
      'content' => $this->content,
    ]);
    $out =
      '<?php ' .
      $this->varname .
      "=$data; echo Hamle\\Tag\\DynHtml::toStTag(" .
      $this->varname .
      ",\$form).";
    $out .= "implode(\"\\n\"," . $this->varname . "['content']).";
    $out .=
      'Hamle\\Tag\\DynHtml::toEnTag(' .
      $this->varname .
      ",\$form)?>" .
      ($minify ? '' : "\n");
    return $out;
  }

  function addChild(H\Tag $tag, $mode = 'append'): void
  {
    throw new ParseError('Unable to display content within a Dynamic Tag');
  }

  static function toStTag(array &$d, H\Form $form): string
  {
    foreach ($d['source'] as $source) {
      $form
        ->getField($source)
        ->getDynamicAtt($d['base'], $d['opt'], $d['type'], $d['content']);
    }
    $out = '<' . $d['type'] . ' ';
    foreach ($d['opt'] as $k => $v) {
      if (is_array($v)) {
        foreach ($v as $k2 => $v2) {
          if ($v[$k2] instanceof Text) {
            /**
             * @psalm-suppress UndefinedMethod
             */
            $v[$k2] = eval('return ' . $v[$k2]->toPHP() . ';');
          }
        }
        $v = implode(' ', $v);
      }
      if ($v instanceof H\Text) {
        $v = eval('return ' . $v->toPHP() . ';');
      }
      $out .= $k . "=\"" . htmlspecialchars($v) . "\" ";
    }
    $out .= in_array($d['type'], self::$selfCloseTags) ? '/>' : '>';
    return $out;
  }

  static function toEnTag(array $d, H\Form $form): string
  {
    return in_array($d['type'], self::$selfCloseTags)
      ? ''
      : '</' . $d['type'] . '>';
  }
}
