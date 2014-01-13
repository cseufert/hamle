<?php

require_once "base.php";

class stringTest extends base{
  
  public function testDollarString1() {
    $hs = new hamleString("Hello \$user");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hello <?=hamleScope::get()->hamleGet('user')?>", $html);
    $this->assertEquals("'Hello '.hamleScope::get()->hamleGet('user')", $php);
  }
  public function testDollarString2() {
    $hs = new hamleString("Hello {\$user}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hello <?=hamleScope::get()->hamleGet('user')?>", $html);
    $this->assertEquals("'Hello '.hamleScope::get()->hamleGet('user')", $php);
  }
  public function testDollarString3() {
    $hs = new hamleString("Hello {\$[0]->user}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hello <?=hamleScope::get(0)->hamleGet('user')?>", $html);
    $this->assertEquals("'Hello '.hamleScope::get(0)->hamleGet('user')", $php);
  }
  public function testDollarString4() {
    $hs = new hamleString("Hello {\$(site)->user}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hello <?=hamleRun::modelTypeTags(array('site'=>array()),0,'',0,0)->hamleGet('user')?>", $html);
    $this->assertEquals("'Hello '.hamleRun::modelTypeTags(array('site'=>array()),0,'',0,0)->hamleGet('user')", $php);
  }
  public function testDollarString5() {
    $hs = new hamleString("Hello {\$(site > address.mail)->state}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hello <?=hamleRun::modelTypeTags(array('site'=>array()),0,'',0,0)->hamleRel(1,array('address'=>array(0=>'mail')),0,'',0,0)->hamleGet('state')?>", $html);
    $this->assertEquals("'Hello '.hamleRun::modelTypeTags(array('site'=>array()),0,'',0,0)->hamleRel(1,array('address'=>array(0=>'mail')),0,'',0,0)->hamleGet('state')", $php);
  }
  public function testDollarString6() {
    $hs = new hamleString("Hello {\$[1]->summary}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hello <?=hamleScope::get(1)->hamleGet('summary')?>", $html);
    $this->assertEquals("'Hello '.hamleScope::get(1)->hamleGet('summary')", $php);
  }
  public function testDollarStringEscape1() {
    $hs = new hamleString("String with \\$ Dollar Sign");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals('String with $ Dollar Sign', $html);
    $this->assertEquals('\'String with \\$ Dollar Sign\'', $php);
  }
  public function testDollarStringEscape2() {
    $hs = new hamleString('Some & "Some" More');
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals('Some &amp; &quot;Some&quot; More', $html);
    $this->assertEquals('\'Some & "Some" More\'', $php);
  }
  public function testDollarStringEscape3() {
    $hs = new hamleString('This < that > this');
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals('This &lt; that &gt; this', $html);
    $this->assertEquals('\'This < that > this\'', $php);
  }

  public function testDollarStringEscape4() {
    $hs = new hamleString("I have \\\$10.00");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("I have $10.00", $html);
    $this->assertEquals("'I have \\$10.00'", $php);
  }

  public function testDollarFunc1() {
    $hs = new hamleString("\$(user)", hamleString::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("hamleRun::modelTypeTags(array('user'=>array()),0,'',0,0)", $php);
  }
  
  public function testDollarFunc2() {
    $hs = new hamleString("\$(user#3)", hamleString::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("hamleRun::modelTypeId(array('user'=>array(0=>3)),0,'',0,0)", $php);
  }
  public function testDollarFunc3() {
    $hs = new hamleString("\$(#my_page)", hamleString::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("hamleRun::modelId('my_page',0,'',0,0)", $php);
  }
  public function testDollarFunc4() {
    $hs = new hamleString("\$(page:4)", hamleString::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("hamleRun::modelTypeTags(array('page'=>array()),0,'',4,0)", $php);
  }
  
  public function testDollarFunc5() {
    $hs = new hamleString("\$(page^title:1-3)", hamleString::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("hamleRun::modelTypeTags(array('page'=>array()),2,'title',3,1)", $php);
  }
  public function testDollarFunc6() {
    $hs = new hamleString("\$(photo.heroimage^:1)", hamleString::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("hamleRun::modelTypeTags(array('photo'=>array(0=>'heroimage')),4,'',1,0)", $php);
  }
  public function testDollarFunc7() {
    $hs = new hamleString("\$(.hero-image^:1)", hamleString::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("hamleRun::modelTypeTags(array('*'=>array(0=>'hero-image')),4,'',1,0)", $php);
  }
  public function testDollarFuncChild1() {
    $hs = new hamleString("\$(#my_page > link)", hamleString::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("hamleRun::modelId('my_page',0,'',0,0)".
                      "->hamleRel(1,array('link'=>array()),0,'',0,0)", $php);
  }  
  public function testDollarFuncChild2() {
    $hs = new hamleString("\$(#my_page > .gallery)", hamleString::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("hamleRun::modelId('my_page',0,'',0,0)".
                    "->hamleRel(1,array('*'=>array(0=>'gallery')),0,'',0,0)", $php);
  } 
  public function testDollarFuncChild3() {
    $hs = new hamleString("\$(#menu > page,cat)", hamleString::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("hamleRun::modelId('menu',0,'',0,0)".
                "->hamleRel(1,array('page'=>array(),'cat'=>array()),0,'',0,0)", $php);
  } 
  /**
   * @expectedException hamleEx_ParseError
   * @aexpectedExceptionMessage Unable to specify child by ID in '#me...' 
   */
  public function testDollarFuncChild4() {
    $hs = new hamleString("\$(#my_page > #me)", hamleString::TOKEN_CONTROL);
  }
  public function testDollarFuncChild5() {
    $hs = new hamleString("\$(#heroimage > photo:4^)", hamleString::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("hamleRun::modelId('heroimage',0,'',0,0)".
                    "->hamleRel(1,array('photo'=>array()),4,'',4,0)", $php);
  } 
  public function testDollarFuncParent1() {
    $hs = new hamleString("\$( < cat)", hamleString::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("hamleScope::get(0)->hamleRel(2,array('cat'=>array()),0,'',0,0)", $php);
  }   
}