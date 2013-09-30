<?php

require_once "base.php";

class stringTest extends base{
  
  public function testDollarString1() {
    $hs = new hamleStrVar("Hello \$user");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hello <?=hamleScope::getVal(\"user\")?>", $html);
    $this->assertEquals("\"Hello \".hamleScope::getVal(\"user\")", $php);
  }
  
  public function testDollarStringEscape1() {
    $hs = new hamleStrVar("I have \\\$10.00");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("I have $10.00", $html);
    $this->assertEquals("\"I have \\\$10.00\"", $php);
  }

  public function testDollarFunc1() {
    $hs = new hamleStrVar("\$(user)", hamleStrVar::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals('hamleRun::modelType("user")', $php);
  }
  
  public function testDollarFunc2() {
    $hs = new hamleStrVar("\$(user#3)", hamleStrVar::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals('hamleRun::modelTypeID("user","3")', $php);
  }
  public function testDollarFunc3() {
    $hs = new hamleStrVar("\$(#my_page)", hamleStrVar::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals('hamleRun::modelID("my_page")', $php);
  }
  public function testDollarFuncChild1() {
    $hs = new hamleStrVar("\$(#my_page > link)", hamleStrVar::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals('hamleRun::modelID("my_page")->hamleRel(1, '.
                              'array (  \'link\' =>   array (  ),))', $php);
  }  
  public function testDollarFuncChild2() {
    $hs = new hamleStrVar("\$(#my_page > .gallery)", hamleStrVar::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals('hamleRun::modelID("my_page")->hamleRel(1, '.
                'array (  \'*\' =>   array (    0 => \'gallery\',  ),))', $php);
  } 
  public function testDollarFuncChild3() {
    $hs = new hamleStrVar("\$(#menu > page,cat)", hamleStrVar::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals('hamleRun::modelID("menu")->hamleRel(1, '.
          'array (  \'page\' =>   array (  ),  \'cat\' =>   array (  ),))', $php);
  } 
  /**
   * @expectedException hamleEx_ParseError
   * @expectedExceptionMessage Unable to specify child by ID
   */
  public function testDollarFuncChild4() {
    $hs = new hamleStrVar("\$(#my_page > #me)", hamleStrVar::TOKEN_CONTROL);
  }
  public function testDollarFuncParent1() {
    $hs = new hamleStrVar("\$( < cat)", hamleStrVar::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals('hamleScope::get("0")->hamleRel(2, '.
          'array (  \'cat\' =>   array (  ),))', $php);
  }   
}