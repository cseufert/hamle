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
namespace Seufert\Hamle;

use Seufert\Hamle\Text\FormField;

/**
 * HAMLE Form Field
 * Basic form field to extend
 * @author Chris Seufert <chris@seufert.id.au>
 * @package hamle
 *
 * @property string $label
 * @method $this label(string $label) Set Label
 * @property string $name
 * @property string $value
 * @method $this value(string $value) Set field value
 * @property string $default
 * @method $this default(string $defaultValue) Set field default value
 * @property string $help
 * @method $this help(string $help) Set Help Message
 * @property Form $form
 * @property bool $required
 * @property bool $disabled
 * @method $this disabled(boolean $disabled) Set field disabled
 * @property bool $readonly
 */
class Field
{
  protected $opt;

  protected $name;

  protected $setValue = null;

  protected $valid = true;

  function __construct($name, $options = [])
  {
    $this->name = $name;
    $this->hint = '';
    $this->opt = $options + [
      'label' => "$name",
      'regex' => '',
      'required' => false,
      'default' => '',
      'error' => "$name is Required",
      'help' => '',
      'test' => null,
      'form' => 'noForm',
      'readonly' => false,
      'hinttext' => '',
      'disabled' => false,
    ];
  }

  function __call($name, $valarray)
  {
    if (count($valarray) < 1) {
      return $this->__get($name);
    }
    $val = count($valarray) == 1 ? current($valarray) : $valarray;
    switch ($name) {
      case 'name':
        throw new Exception("Unable to change $name after object is created");
      case 'valid':
        return $this->valid;
      case 'val':
      case 'value':
        $this->setValue = $val;
        break;
      default:
        $this->opt[$name] = $val;
    }
    return $this;
  }

  function __get($name)
  {
    switch ($name) {
      case 'name':
        return $this->name;
      case 'valid':
        return $this->valid;
      case 'val':
      case 'value':
        return $this->getValue();
      default:
        return isset($this->opt[$name]) ? $this->opt[$name] : null;
    }
  }

  function __set($name, $val)
  {
    switch ($name) {
      case 'name':
      case 'valid':
        throw new Exception("Unable to change $name after object is created");
      case 'val':
      case 'value':
        return $this->setValue = $val;
      default:
        return isset($this->opt[$name]) ? $this->opt[$name] : null;
    }
  }

  function getValue()
  {
    if (!is_null($this->setValue)) {
      return $this->setValue;
    }
    if (isset($_REQUEST[$this->form . '_' . $this->name])) {
      return $_REQUEST[$this->form . '_' . $this->name];
    }
    return $this->opt['default'];
  }

  function getInputAttStatic(&$atts, &$type, &$content)
  {
    $atts['id'] = $atts['name'] = $this->form . '_' . $this->name;
    $atts['type'] = 'text';
    $atts['class'][] = str_replace(
      ['Seufert\\', '\\'],
      ['', '_'],
      get_class($this),
    );
  }

  function getInputAttDynamic(&$atts, &$type, &$content)
  {
    $type = 'input';
    $atts['value'] = new FormField($this->name);
    if (!$this->valid) {
      $atts['class'][] = 'hamleFormError';
    }
    if ($this->opt['disabled']) {
      $atts['disabled'] = 'disabled';
    }
    if ($this->opt['required']) {
      $atts['required'] = 'required';
    }
    if ($this->opt['help']) {
      $atts['title'] = $this->opt['help'];
    }
  }

  function getLabelAttStatic(&$atts, &$type, &$content)
  {
    $atts['class'][] = str_replace(
      ['Seufert\\', '\\'],
      ['', '_'],
      get_class($this),
    );
    $atts['for'] = $this->form . '_' . $this->name;
    $content = [$this->opt['label']];
  }

  function getLabelAttDynamic(&$atts, &$type, &$content)
  {
  }

  function getHintAttStatic(&$atts, &$type, &$content)
  {
    $atts['class'][] = str_replace(
      ['Seufert\\', '\\'],
      ['', '_'],
      get_class($this),
    );
    $atts['class'][] = 'hamleFormHint';
  }

  function getHintAttDynamic(&$atts, &$type, &$content)
  {
    $type = 'div';
    if (!$this->valid) {
      $content = [$this->opt['error']];
      $atts['class'][] = 'hamleFormError';
    }
  }

  function getDynamicAtt($base, &$atts, &$type, &$content)
  {
    if ($base == 'input') {
      $this->getInputAttDynamic($atts, $type, $content);
    } elseif ($base == 'hint') {
      $this->getHintAttDynamic($atts, $type, $content);
    } elseif ($base == 'label') {
      $this->getLabelAttDynamic($atts, $type, $contnet);
    }
  }

  function doProcess($submit)
  {
    if ($submit) {
      $value = $this->getValue();
      if ($this->opt['required']) {
        $this->valid = $this->valid && strlen($value);
      }
      if ($this->opt['regex']) {
        $this->valid = $this->valid && preg_match($this->opt['regex'], $value);
      }
    }
  }
}
