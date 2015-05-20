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
use Seufert\Hamle\Tag;
use Seufert\Hamle\Exception\ParseError;
use Seufert\Hamle\Field\Button;

class Form extends Tag {
  protected static $sForm, $sCount;
  protected $var;
  /**
   * @var H\Form $form Hamle Form Instance for configuring template
   */
  protected $form;

  function __construct($param) {
    parent::__construct();
    $param = explode(' ', $param);
    if (count($param) < 2) throw new ParseError("|form requires 2 arguments, form type, and instance");
    $this->var = new H\Text($param[1]);
    if (preg_match('/^(.*)\((.*)\)/', $param[0], $m))
      $this->form = new $m[1]($m[2]);
    else
      $this->form = new $param[0];
  }

  function renderStTag() {
    self::$sForm[] = $this;
    self::$sCount = count(self::$sForm);
    $out = array();
    foreach ($this->form->getHTMLProp() as $k => $v) {
      $out[] = "$k=\"$v\"";
    }
    $fields = $this->form->getFields();
    $labelTags = $this->find(array(array("type" => "label")));
    foreach ($labelTags as $tag)
      if ($tag instanceOf Html)
        foreach ($tag->source as $source) {
          if(isset($fields[$source]))
            $fields[$source]->getLabelAttStatic($tag->opt, $tag->type, $tag->content);
          else
            $tag->opt['style'] = "display:none;";
        }
    $inputTags = $this->find(array(array("type" => "hint")));
    foreach ($inputTags as $tag)
      if ($tag instanceOf Html)
        foreach ($tag->source as $source) {
          if(isset($fields[$source]))
            $fields[$source]->getHintAttStatic($tag->opt, $tag->type, $tag->content);
          else
            $tag->opt['style'] = "display:none;";
        }
    $inputTags = $this->find(array(array("type" => "input")));
    foreach ($inputTags as $tag)
      if ($tag instanceOf Html)
        foreach ($tag->source as $source) {
          if(isset($fields[$source])) {
            $fields[$source]->getInputAttStatic($tag->opt, $tag->type, $tag->content);
            unset($fields[$source]);
          } else
            $tag->opt['style'] = "display:none;";
        }
    foreach ($fields as $n => $f) {
      if (!$f instanceOf Button) {
        $this->addChild($label = new DynHtml("label", [],[],"",$n));
        $f->getLabelAttStatic($label->opt, $label->type, $label->content);
      }
      $this->addChild($input = new DynHtml("input", [],[],"",$n));
      $f->getInputAttStatic($input->opt, $input->type, $input->content);
    }
    return "<form " . implode(" ", $out) . "><?php \$form = " . $this->var->toPHP() . "; \$form->process(); ?>";
  }

  function renderEnTag() {
    return "<?php echo \$form->preEndTag(); unset(\$form); ?></form>";
//    array_pop(self::$sForm);
//    self::$sCount = count(self::$sForm);
  }
}