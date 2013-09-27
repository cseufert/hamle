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
             '            a[href=$url] $title'.PHP_EOL;
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
  </body>
</html>';
    $out = $this->hamle->outputStr($hamle);
    $this->compareXmlStrings($html, $out);
  }
   
}
