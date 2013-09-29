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
    $hs = new hamleStrVar("\$(user)");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals('hamleRun::modelType("user")', $php);
  }
  
  public function testDollarFunc2() {
    $hs = new hamleStrVar("\$(user#3)");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals('hamleRun::modelTypeID("user","3")', $php);
  }
  public function testDollarFunc3() {
    $hs = new hamleStrVar("\$(#my_page)");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals('hamleRun::modelID("my_page")', $php);
  }
  
}