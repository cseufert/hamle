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

/**
 * HAMLE Form Class
 *
 * @package hamle
 * @author Chris Seufert <chris@seufert.id.au>
 */
class Form
{
  /**
   * @var Field[] Form Fields
   */
  protected $_fields;
  protected $_name;
  public $hint;
  protected $_data;
  /**
   * Setup Method
   * Fills the $this->fields array with hamleFields
   */
  function setup()
  {
    throw new Exception\RunTime(
      'You must configure the form in the setup ' .
        "function, by adding fields to the \$this->fields array",
    );
  }

  function __construct()
  {
    $this->_data = func_get_args();
    $this->_name = get_called_class();
    $this->setup();
    $fields = $this->_fields;
    $this->_fields = [];
    foreach ($fields as $v) {
      $this->_fields[$v->name] = $v;
      $v->form($this->_name);
    }
    $this->postInit();
  }
  function postInit()
  {
  }

  function process()
  {
    $clicked = '';
    foreach ($this->_fields as $f) {
      if ($f instanceof Field\Button) {
        if ($f->isClicked()) {
          $clicked = $f;
        }
      }
    }
    foreach ($this->_fields as $f) {
      $f->doProcess($clicked ? true : false);
    }
    if ($clicked) {
      try {
        $this->onSubmit($clicked);
      } catch (Exception\FormInvalid $e) {
        $this->hint = $e->getMessage();
      }
    }
  }

  function isValid()
  {
    $valid = true;
    foreach ($this->_fields as $f) {
      $valid = $f->valid && $valid;
    }
    return $valid;
  }
  /**
   * Called upon form submission, $button will be assigned to the button that was clicked
   * @param Field\Button $button
   * @throws Exception\NoKey
   */
  function onSubmit($button)
  {
  }

  function getFields()
  {
    return $this->_fields;
  }
  function getField($n)
  {
    if (!isset($this->_fields[$n])) {
      throw new Exception\NoKey("unable to find form field ($n)");
    }
    return $this->_fields[$n];
  }
  function __get($n)
  {
    return $this->getField($n);
  }

  function getHTMLProp()
  {
    return [
      'action' => '',
      'method' => 'post',
      'name' => $this->_name,
      'enctype' => 'multipart/form-data',
    ];
  }

  function preEndTag()
  {
    echo "<input type='hidden' name='{$this->_name}__submit' value='submit' />";
  }
}
