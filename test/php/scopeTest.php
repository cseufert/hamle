<?php

require_once "base.php";

class scopeTest extends base {
  public function testScopeAccessor() {
    $hamle = "html".PHP_EOL.
             "  body".PHP_EOL.
             '    |with $(basetest)'.PHP_EOL.
             '      ul.menu'.PHP_EOL.
             '        |each'.PHP_EOL.
             '          li.menuitem[data-menu=$title]'.PHP_EOL.
             '            a[href=$url] $title'.PHP_EOL.
             '      span $title';
    $html = '
<html>
  <body>
    <ul class="menu">
      <li data-menu="Test.com" class="menuitem">
        <a href="http://www.test.com">Test.com</a>
      </li>
      <li data-menu="Test2.com" class="menuitem">
        <a href="http://www.test2.com">Test2.com</a>
      </li>
      <li data-menu="Test3.com" class="menuitem">
        <a href="http://www.test3.com">Test3.com</a>
      </li>
    </ul>
    <span>Test.com</span>
  </body>
</html>';
    $out = $this->hamle->outputStr($hamle);
    $this->compareXmlStrings($html, $out);
  }

  public function testScopeAccessor2() {
    $hamle = "html".PHP_EOL.
             "  body".PHP_EOL.
             '    ul.menu'.PHP_EOL.
             '      |each $(basetest)'.PHP_EOL.
             '        li.menuitem[data-menu=$title]'.PHP_EOL.
             '          a[href=$url] $title'.PHP_EOL.
             '    span $title';
    $html = '
<html>
  <body>
    <ul class="menu">
      <li data-menu="Test.com" class="menuitem">
        <a href="http://www.test.com">Test.com</a>
      </li>
      <li data-menu="Test2.com" class="menuitem">
        <a href="http://www.test2.com">Test2.com</a>
      </li>
      <li data-menu="Test3.com" class="menuitem">
        <a href="http://www.test3.com">Test3.com</a>
      </li>
    </ul>
    <span>This is My TITLE</span>
  </body>
</html>';
    $out = $this->hamle->outputStr($hamle);
    $this->compareXmlStrings($html, $out);
  }

  public function testIf() {
    $hamle = "html".PHP_EOL.
             "  body".PHP_EOL.
             '    |if $title'.PHP_EOL.
             '      h2 $title'.PHP_EOL.
             '    |if {$[0]->istrue}'.PHP_EOL.
             '      .show This will be visible'.PHP_EOL.
             '    |if $nottrue'.PHP_EOL.
             '      .hide This will not be shown'.PHP_EOL;
    $html = '
<html>
  <body>
    <h2>This is My TITLE</h2>
    <div class="show">This will be visible</div>
  </body>
</html>';
    $out = $this->hamle->outputStr($hamle);
    $this->compareXmlStrings($html, $out);
  }
  
}
