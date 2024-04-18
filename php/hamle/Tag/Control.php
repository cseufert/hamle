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

/**
 * HAMLE Control Tag
 * Used for tags starting with the pipe (|) symbol
 */
class Control extends H\Tag
{
  /**
   * @var string Variable passed to Control Tag
   */
  protected string $var;
  public string $o;
  public bool $else = false;
  static int $instCount = 1;

  /**
   * Crate new Control Tag
   * @param string $tag Type of Control Tag
   * @param \Seufert\Hamle\Tag $parentTag
   * @throws ParseError
   */
  function __construct(string $tag, H\Tag $parentTag = null)
  {
    parent::__construct();
    $this->o = "\$o" . self::$instCount++;
    $this->type = strtolower($tag);
    $this->var = '';
    if ($parentTag && $this->type == 'else') {
      $elseTag = $parentTag->tags[count($parentTag->tags) - 1];
      if (
        $elseTag instanceof H\Tag\Control &&
        in_array($elseTag->type, ['with', 'if'])
      ) {
        $elseTag->else = true;
      } else {
        throw new ParseError(
          "You can only use else with |with and |if, you tried |{$parentTag->type}",
        );
      }
    }
  }

  function renderStTag(): string
  {
    $out = '<' . '?php ';
    $scopeName = '';
    if ($this->type === 'if') {
      $hsvcomp = new H\Text\Comparison($this->var);
      $out .= 'if(' . $hsvcomp->toPHP() . ') {';
      return $out . "\n?>";
    } elseif ($this->type === 'else') {
      $out .= '/* else */';
      return $out . "\n?>";
    }
    /** @var H\Text|null $hsv */
    $hsv = null;
    if ($this->var) {
      if (preg_match('/ as ([a-zA-Z]+)$/', $this->var, $m)) {
        $scopeName = $m[1];
        $lookup = substr($this->var, 0, strlen($this->var) - strlen($m[0]));
        $hsv = new H\Text(trim($lookup), H\Text::TOKEN_CONTROL);
      } else {
        $hsv = new H\Text($this->var, H\Text::TOKEN_CONTROL);
      }
    }
    switch ($this->type) {
      case 'each':
        if ($hsv) {
          $out .= 'foreach(' . $hsv->toPHP() . " as {$this->o}) { \n";
        } else {
          $out .= "foreach(\$scope->model() as {$this->o}) { \n";
        }
        $out .= "\$scope = \$scope->withModel({$this->o}); ";
        break;
      case 'with':
        if (!$hsv) {
          throw new \RuntimeException(
            'With requires a parameter for what to include',
          );
        }
        $out .= "\$scope = \$scope->withModel({$this->o} = {$hsv->toPHP()});\n";
        if ($scopeName) {
          $out .= "\$scope->setNamedModel(\"$scopeName\");\n";
        }
        $out .= "if({$this->o}->valid()) {\n";
        break;
      case 'include':
        if (!$hsv) {
          throw new \RuntimeException(
            'Include requires a parameter for what to include',
          );
        }
        $out .= "echo \$ctx->hamleInclude(\$scope, {$hsv->toPHP()});";
    }
    return $out . "\n?>";
  }

  /**
   * @param string $s Variable String for control tag
   */
  function setVar(string $s): void
  {
    $this->var = trim($s);
  }

  function renderEnTag(): string
  {
    $out = '<' . '?php ';
    switch ($this->type) {
      case 'each':
        $out .= '$scope = $scope->lastScope(); ';
        $out .= '}';
        if (!$this->var) {
          $out .= "\$scope->model()->rewind();\n";
        }
        break;
      case 'if':
      case 'else':
        $out .= '}';
        break;
      case 'with':
        $out .= "}\n";
        $out .= "\$scope = \$scope->lastScope();\n";
        break;
      case 'include':
        return '';
    }
    if ($this->else) {
      $out .= 'else{';
    }
    return $out . "\n?>";
  }

  function render(int $indent = 0, bool $minify = false): string
  {
    $ind = $minify ? '' : str_pad('', $indent);
    $oneliner = !(count($this->content) > 1 || $this->tags);
    $out = $this->renderStTag();
    if ($this->content) {
      $out .= $this->renderContent($ind, $oneliner || $minify);
    }
    foreach ($this->tags as $tag) {
      $out .= $tag->render($indent, $minify);
    }
    $out .= $this->renderEnTag();
    return $out;
  }
}
