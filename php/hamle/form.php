<?php
/**
 * HAMLE Form Class
 * 
 * @pachage hamle
 * @author Chris Seufert <chris@seufert.id.au>
 */
class hamleForm {
  /**
   * @var hamleField[] Form Fields
   */
  protected $_fields;
  protected $_name;
  public $hint;
  protected $_data;
  /**
   * Setup Method
   * Fills the $this->fields array with hamleFields
   */
  function setup() {
    throw new hamleForm_ExNoSetup("You must configure the form in the setup ".
      "function, by adding fields to the \$this->fields array");
  }
  
  function __construct() {
    $this->_data = func_get_args();
    $this->_name = get_called_class();
    $this->setup();
    $fields = $this->_fields;
    $this->_fields = array();
    foreach($fields as $v) {
      $this->_fields[$v->name] = $v;
      $v->form($this->_name);
    }
    $this->postInit();
  }
  function postInit() {}
  
  function process() {
    $clicked = "";
    foreach($this->_fields as $f)
      if($f instanceOf hamleField_Button)
        if($f->isClicked())
          $clicked = $f;
    foreach($this->_fields as $f)
      $f->doProcess($clicked?true:false);
    if($clicked)
      try {
        $this->onSubmit($clicked);
      } catch(hamleForm_ExInvalid $e) {
        $this->hint = $e->getMessage();
      }
  }
  
  function isValid() {
    $valid = true;
    foreach($this->_fields as $f)
      $valid = $f->valid && $valid;
    return $valid;
  }
  /**
   * Called upon form submission, $button will be assigned to the button that was clicked
   * @param hamleField_Button $button
   * @throws hamleForm_ExInvalid
   */
  function onSubmit($button) { }
  
  function getFields() {
    return $this->_fields;
  }
  function getField($n) {
    if(!isset($this->_fields[$n]))
      throw new hamleEx_NoKey("unable to find form field ($n)");
    return $this->_fields[$n];
  }
  function __get($n) {
    return $this->getField($n);
  }
  
  function getHTMLProp() {
    return array('action'=>'','method'=>'post','name'=>$this->_name,
                                        'enctype'=>'multipart/form-data');
  }
  
}


class hamleForm_Ex extends hamleEx { }
class hamleForm_ExNoSetup extends hamleForm_Ex { }
class hamleForm_ExInvalid extends hamleForm_Ex { }
