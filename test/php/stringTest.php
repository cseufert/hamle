<?php

use Seufert\Hamle\String;

require_once "base.php";

class stringTest extends base {
  
  public function testPlainString1() {
    $hs = new String("\"SimpleFileName.hamle\"", String::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("SimpleFileName.hamle", $html);
    $this->assertEquals("'SimpleFileName.hamle'", $php);
  }
  public function testDollarString1() {
    $hs = new String("Hello \$user");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hello <?=Hamle\\Scope::get()->hamleGet('user')?>", $html);
    $this->assertEquals("'Hello '.Hamle\\Scope::get()->hamleGet('user')", $php);
  }
  public function testDollarString2() {
    $hs = new String("Hello {\$user}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hello <?=Hamle\\Scope::get()->hamleGet('user')?>", $html);
    $this->assertEquals("'Hello '.Hamle\\Scope::get()->hamleGet('user')", $php);
  }
  public function testDollarString3() {
    $hs = new String("Hello {\$[0]->user}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hello <?=Hamle\\Scope::get(0)->hamleGet('user')?>", $html);
    $this->assertEquals("'Hello '.Hamle\\Scope::get(0)->hamleGet('user')", $php);
  }
  public function testDollarString4() {
    $hs = new String("Hello {\$(site)->user}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hello <?=Hamle\\Run::modelTypeTags(array('site'=>array()),0,'',0,0)->hamleGet('user')?>", $html);
    $this->assertEquals("'Hello '.Hamle\\Run::modelTypeTags(array('site'=>array()),0,'',0,0)->hamleGet('user')", $php);
  }
  public function testDollarString5() {
    $hs = new String("Hello {\$(site > address.mail@2)->state}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hello <?=".
        "Hamle\\Run::modelTypeTags(array('site'=>array()),0,'',0,0)->".
        "hamleRel(1,array('address'=>array(0=>'mail')),0,'',0,0,2)->".
        "hamleGet('state')?>", $html);
    $this->assertEquals("'Hello '.".
        "Hamle\\Run::modelTypeTags("."array('site'=>array()),0,'',0,0)->".
        "hamleRel(1,array('address'=>array(0=>'mail')),0,'',0,0,2)->".
        "hamleGet('state')", $php);
  }
  public function testDollarString6() {
    $hs = new String("Hello {\$[1]->summary}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hello <?=Hamle\\Scope::get(1)->hamleGet('summary')?>", $html);
    $this->assertEquals("'Hello '.Hamle\\Scope::get(1)->hamleGet('summary')", $php);
  }
  public function testDollarString7() {
    $hs = new String("$[0]->user",String::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("<?=Hamle\\Scope::get(0)->hamleGet('user')?>", $html);
    $this->assertEquals("Hamle\\Scope::get(0)->hamleGet('user')", $php);
  }
  public function testDollarString8() {
    $hs = new String('{$[test]->user}',String::TOKEN_HTML);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("<?=Hamle\\Scope::getName('test')->hamleGet('user')?>", $html);
    $this->assertEquals("Hamle\\Scope::getName('test')->hamleGet('user')", $php);
  }

  public function testDollarCodeString1() {
    $hs = new String("\"My Title = {\$title}\"",String::TOKEN_CODE);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("\"My Title = <?=Hamle\\Scope::get()->hamleGet('title')?>\"", $html);
  }
  public function testDollarCodeString2() {
    $hs = new String("\"My Title = \$title\"",String::TOKEN_CODE);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("\"My Title = \$title\"", $html);
  }
  public function testDollarStringEscape1() {
    $hs = new String("String with \\$ Dollar Sign");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals('String with $ Dollar Sign', $html);
    $this->assertEquals('\'String with \\$ Dollar Sign\'', $php);
  }
  public function testDollarStringEscape2() {
    $hs = new String('Some & "Some" More');
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals('Some &amp; &quot;Some&quot; More', $html);
    $this->assertEquals('\'Some & "Some" More\'', $php);
  }
  public function testDollarStringEscape3() {
    $hs = new String('This < that > this');
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals('This &lt; that &gt; this', $html);
    $this->assertEquals('\'This < that > this\'', $php);
  }

  public function testDollarStringEscape4() {
    $hs = new String("I have \\\$10.00");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("I have $10.00", $html);
    $this->assertEquals("'I have \\$10.00'", $php);
  }

  public function testDollarFunc1() {
    $hs = new String("\$(user)", String::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hamle\\Run::modelTypeTags(array('user'=>array()),0,'',0,0)", $php);
  }
  
  public function testDollarFunc2() {
    $hs = new String("\$(user#3)", String::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hamle\\Run::modelTypeId(array('user'=>array(0=>3)),0,'',0,0)", $php);
  }
  public function testDollarFunc3() {
    $hs = new String("\$(#my_page)", String::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hamle\\Run::modelId('my_page',0,'',0,0)", $php);
  }
  public function testDollarFunc4() {
    $hs = new String("\$(page:4)", String::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hamle\\Run::modelTypeTags(array('page'=>array()),0,'',4,0)", $php);
  }
  public function testDollarFunc5() {
    $hs = new String("\$(page^title:1-3)", String::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hamle\\Run::modelTypeTags(array('page'=>array()),2,'title',3,1)", $php);
  }
  public function testDollarFunc6() {
    $hs = new String("\$(photo.heroimage^:1)", String::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hamle\\Run::modelTypeTags(array('photo'=>array(0=>'heroimage')),4,'',1,0)", $php);
  }
  public function testDollarFunc7() {
    $hs = new String("\$(.hero-image^:1)", String::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hamle\\Run::modelTypeTags(array('*'=>array(0=>'hero-image')),4,'',1,0)", $php);
  }
  public function testDollarFunc8() {
    $hs = new String("\$(#_ga)", String::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hamle\\Run::modelId('_ga',0,'',0,0)", $php);
  }
  public function testDollarFuncChild1() {
    $hs = new String("\$(#my_page > link)", String::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hamle\\Run::modelId('my_page',0,'',0,0)".
                      "->hamleRel(1,array('link'=>array()),0,'',0,0,1)", $php);
  }  
  public function testDollarFuncChild2() {
    $hs = new String("\$(#my_page > .gallery)", String::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hamle\\Run::modelId('my_page',0,'',0,0)".
                    "->hamleRel(1,array('*'=>array(0=>'gallery')),0,'',0,0,1)", $php);
  } 
  public function testDollarFuncChild3() {
    $hs = new String("\$(#menu > page,cat)", String::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hamle\\Run::modelId('menu',0,'',0,0)".
                "->hamleRel(1,array('page'=>array(),'cat'=>array()),0,'',0,0,1)", $php);
  } 
  /**
   * @expectedException \Seufert\Hamle\Exception\ParseError
   * @aexpectedExceptionMessage Unable to specify child by ID in '#me...' 
   */
  public function testDollarFuncChild4() {
    $hs = new String("\$(#my_page > #me)", String::TOKEN_CONTROL);
  }
  public function testDollarFuncChild5() {
    $hs = new String("\$(#heroimage > photo:4^)", String::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hamle\\Run::modelId('heroimage',0,'',0,0)".
                    "->hamleRel(1,array('photo'=>array()),4,'',4,0,1)", $php);
  } 
  public function testDollarFuncParent1() {
    $hs = new String("\$( < cat)", String::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hamle\\Scope::get(0)->".
        "hamleRel(2,array('cat'=>array()),0,'',0,0,1)", $php);
  }   
}