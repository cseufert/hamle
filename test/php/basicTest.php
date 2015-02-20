<?php

require_once "base.php";

class basicTest extends base {
  public function testTags() {
    $hamle = "html\n".
             "  body\n".
             "    #content[align=center]\n".
             "      ul.menu\n".
             "        li.menuitem[data-menu=home] Home\n".
             "        li.menuitem[data-menu=About]\n".
             "          a[href=/aboutus.php] About Us\n";
    $html = "<html><body><div id=\"content\" align=\"center\">".
            "<ul class=\"menu\">".
            "        <li data-menu=\"home\" class=\"menuitem\">Home</li>".
            "        <li data-menu=\"About\" class=\"menuitem\">".
            "<a href=\"/aboutus.php\">About Us</a>".
            "</li>".
            "</ul>".
            "</div></body></html>";
    $this->hamle->parse($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
    $this->assertEquals($hamle, $this->hamle->outputHamle());
  }
  
  public function testShortTags() {
    $hamle = "html\n  meta\n  link\n";
    $html = "<html><meta /><link /></html>";
    $this->hamle->parse($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
    $this->assertEquals($hamle, $this->hamle->outputHamle());
  }
  
  public function testAttr1() {
    $hamle= "html\n".
            "  meta[name=viewport&content=user-scalable=no,width=device-width,maximum-scale=1.0]\n".
            "  link[href=/css&type=text/css]\n";
    $html = "<html>".
            '<meta name="viewport" content="user-scalable=no,width=device-width,maximum-scale=1.0" />'.
            '<link href="/css" type="text/css" />'.
            "</html>";
    $this->hamle->parse($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
    $this->assertEquals($hamle, $this->hamle->outputHamle());
  }
  public function testWeirdStartIndent() { return;
    $hamle= " div\n".
            "html\n".
            "  meta[name=viewport&content=user-scalable=no,width=device-width,maximum-scale=1.0]\n".
            "  link[href=/css&type=text/css]\n";
    $html = "<div></div>\n".
            "<html>\n".
            '  <meta name="viewport" content="user-scalable=no,width=device-width,maximum-scale=1.0" />'.PHP_EOL.
            '  <link href="/css" type="text/css" />'.PHP_EOL.
            "</html>".PHP_EOL;
    $this->hamle->parse($hamle);
    $out = $this->hamle->output();
    $this->assertEquals($html, $out);
    $this->compareXmlStrings("<r>".$html."</r>", "<r>".$out."</r>");
    $this->assertEquals(trim($hamle), trim($this->hamle->outputHamle()));
  }
  public function testAttrSquareBracket() {
    $hamle = "a[href=/special\[10\]] Hello [Mate] [ ]\n";
    $html = '<a href="/special[10]">Hello [Mate] [ ]</a>';
    $this->hamle->parse($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
    $this->assertEquals($hamle, $this->hamle->outputHamle());
  }
  public function testAngularArray() {
    $hamle = 'input[name=my-input&ng-bind=myval\[\$index\]]';
    $html = '<input name="my-input" ng-bind="myval[$index]" />';
    $this->hamle->parse($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
    $this->assertEquals($hamle."\n", $this->hamle->outputHamle());
  }
  public function testAttrDollar() {
    $hamle = "a[href=\$url&class=\$class] {\$title}\n";
    $html = "<a href=\"https://www.secure.com\" class=\"colored\">This is My TITLE</a>";
    $this->hamle->parse($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
    $this->assertEquals($hamle, $this->hamle->outputHamle());
  }
  public function testAttrMultiDollar() {
    $hamle = 'a[href=$url&class=$class] $title [$url]';
    $html = "<a href=\"https://www.secure.com\" class=\"colored\">This is My TITLE [https://www.secure.com]</a>";
    $this->hamle->parse($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
    $this->assertEquals($hamle, $this->hamle->outputHamle());
  }
  public function testAttrEscDollars() {
    $hamle = "html\n  p.quote[data-ref=12\&3 and \\\$4]\n";
    $html = "<html><p class=\"quote\" data-ref=\"12&amp;3 and \$4\"></p></html>";
    $this->hamle->parse($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
    $this->assertEquals($hamle, $this->hamle->outputHamle());
  }
  public function testAttrQuotes() {
    $hamle = "html\n  p.quote[data-ref=My Quote \"]\n";
    $html = "<html><p class=\"quote\" data-ref=\"My Quote &quot;\"></p></html>";
    $this->hamle->parse($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
    $this->assertEquals($hamle, $this->hamle->outputHamle());
  }
  public function testCommentSingle() {
    $hamle = "html\n".
             "  #box\n".
             "    // Comment is Hidden\n";
    $html = "<html><div id=\"box\"></div></html>";
    $this->hamle->parse($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
    $this->assertEquals($hamle, $this->hamle->outputHamle());
  }
  public function testCommentBlock() {
    $hamle = "html\n".
             "  // #box\n".
             "    .message Comment is Hidden\n";
    $html = "<html></html>";
    $this->hamle->parse($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
    $this->assertEquals($hamle, $this->hamle->outputHamle());
  }
  public function testCommentHTML() {
    $hamle = "html\n".
             "  / Just a Comment\n";
    $html = "<html>\n".
            "  <!-- Just a Comment -->\n".
            "</html>\n";
    $this->hamle->parse($hamle);
    $out = $this->hamle->output();
    $this->assertEquals($html, $out);
    $this->assertEquals($hamle, $this->hamle->outputHamle());
  }
  public function testCommentHTMLMultiLine() {
    $hamle = "html\n".
             "  / Just a Comment\n".
             "    Next line of Comment\n";
    $html = "<html>\n".
            "  <!-- \n".
            "    Just a Comment\n".
            "    Next line of Comment\n".
            "   -->\n".
            "</html>\n";
    $this->hamle->parse($hamle);
    $out = $this->hamle->output();
    $this->assertEquals($html, $out);
    $this->assertEquals($hamle, $this->hamle->outputHamle());
  }
  public function testTextLine() {
    $hamle = "html\n".
             "  #box Content Line 1\n".
             "    br\n".
             "      _ Box Content Line 2\n";
    $html = "<html><div id=\"box\">Content Line 1<br />Box Content Line 2</div></html>";
    $this->hamle->parse($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
    $this->assertEquals($hamle, $this->hamle->outputHamle());
  }
  
}
