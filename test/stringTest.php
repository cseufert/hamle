<?php

require_once "base.php";

class stringTest extends base{
  
  public function testDollarString1() {
    $hs = new hamleStrVar("Hello \$user");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hello <?=hamleScope::get()->hamleGet('user')?>", $html);
    $this->assertEquals("'Hello '.hamleScope::get()->hamleGet('user')", $php);
  }
  public function testDollarString2() {
    $hs = new hamleStrVar("Hello {\$user}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hello <?=hamleScope::get()->hamleGet('user')?>", $html);
    $this->assertEquals("'Hello '.hamleScope::get()->hamleGet('user')", $php);
  }
  public function testDollarString3() {
    $hs = new hamleStrVar("Hello {\$[0]->user}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hello <?=hamleScope::get('0')->hamleGet('user')?>", $html);
    $this->assertEquals("'Hello '.hamleScope::get('0')->hamleGet('user')", $php);
  }
  public function testDollarString4() {
    $hs = new hamleStrVar("Hello {\$(site)->user}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hello <?=hamleRun::modelTypeTags(Array('site'=>Array()),0,'',0,0)->hamleGet('user')?>", $html);
    $this->assertEquals("'Hello '.hamleRun::modelTypeTags(Array('site'=>Array()),0,'',0,0)->hamleGet('user')", $php);
  }
  public function testDollarString5() {
    $hs = new hamleStrVar("Hello {\$(site > address.mail)->state}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hello <?=hamleRun::modelTypeTags(Array('site'=>Array()),0,'',0,0)->hamleRel(1,Array('address'=>Array('0'=>'mail')),0,'',0,0)->hamleGet('state')?>", $html);
    $this->assertEquals("'Hello '.hamleRun::modelTypeTags(Array('site'=>Array()),0,'',0,0)->hamleRel(1,Array('address'=>Array('0'=>'mail')),0,'',0,0)->hamleGet('state')", $php);
  }
  
  
  public function testDollarStringEscape1() {
    $hs = new hamleStrVar("I have \\\$10.00");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("I have $10.00", $html);
    $this->assertEquals("'I have \\\\$10.00'", $php);
  }

  public function testDollarFunc1() {
    $hs = new hamleStrVar("\$(user)", hamleStrVar::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("hamleRun::modelTypeTags(Array('user'=>Array()),0,'',0,0)", $php);
  }
  
  public function testDollarFunc2() {
    $hs = new hamleStrVar("\$(user#3)", hamleStrVar::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("hamleRun::modelTypeID(Array('user'=>'3'),0,'',0,0)", $php);
  }
  public function testDollarFunc3() {
    $hs = new hamleStrVar("\$(#my_page)", hamleStrVar::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("hamleRun::modelID('my_page',0,'',0,0)", $php);
  }
  public function testDollarFunc4() {
    $hs = new hamleStrVar("\$(page:4)", hamleStrVar::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("hamleRun::modelTypeTags(Array('page'=>Array()),0,'',4,0)", $php);
  }
  public function testDollarFunc5() {
    $hs = new hamleStrVar("\$(page^title:1-3)", hamleStrVar::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("hamleRun::modelTypeTags(Array('page'=>Array()),2,'',3,1)", $php);
  }
  public function testDollarFunc6() {
    $hs = new hamleStrVar("\$(photo.heroimage^:1)", hamleStrVar::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("hamleRun::modelTypeTags(Array('photo'=>Array('0'=>'heroimage')),4,'',1,0)", $php);
  }
  public function testDollarFuncChild1() {
    $hs = new hamleStrVar("\$(#my_page > link)", hamleStrVar::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("hamleRun::modelID('my_page',0,'',0,0)".
                      "->hamleRel(1,Array('link'=>Array()),0,'',0,0)", $php);
  }  
  public function testDollarFuncChild2() {
    $hs = new hamleStrVar("\$(#my_page > .gallery)", hamleStrVar::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("hamleRun::modelID('my_page',0,'',0,0)".
                    "->hamleRel(1,Array('*'=>Array('0'=>'gallery')),0,'',0,0)", $php);
  } 
  public function testDollarFuncChild3() {
    $hs = new hamleStrVar("\$(#menu > page,cat)", hamleStrVar::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("hamleRun::modelID('menu',0,'',0,0)".
                "->hamleRel(1,Array('page'=>Array(),'cat'=>Array()),0,'',0,0)", $php);
  } 
  /**
   * @expectedException hamleEx_ParseError
   * @expectedExceptionMessage Unable to specify child by ID in '#me...' 
   */
  public function testDollarFuncChild4() {
    $hs = new hamleStrVar("\$(#my_page > #me)", hamleStrVar::TOKEN_CONTROL);
  }
  public function testDollarFuncChild5() {
    $hs = new hamleStrVar("\$(#heroimage > photo:4^)", hamleStrVar::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("hamleRun::modelID('heroimage',0,'',0,0)".
                    "->hamleRel(1,Array('photo'=>Array()),4,'',4,0)", $php);
  } 
  public function testDollarFuncParent1() {
    $hs = new hamleStrVar("\$( < cat)", hamleStrVar::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("hamleScope::get('0')->hamleRel(2,Array('cat'=>Array()),0,'',0,0)", $php);
  }   
}