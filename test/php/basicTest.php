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
    $this->hamle->string($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
  }
  
  public function testShortTags() {
    $hamle = "html\n  meta\n  link\n";
    $html = "<html><meta /><link /></html>";
    $this->hamle->string($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
  }
  
  public function testAttr1() {
    $hamle= "html\n".
            "  meta[name=viewport&content=user-scalable=no,width=device-width,maximum-scale=1.0]\n".
            "  link[href=/css&type=text/css]\n";
    $html = "<html>".
            '<meta name="viewport" content="user-scalable=no,width=device-width,maximum-scale=1.0" />'.
            '<link href="/css" type="text/css" />'.
            "</html>";
    $this->hamle->string($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
  }
  public function testWeirdStartIndent() {
    $hamle= " div\n".
            "html\n".
            "  meta[name=viewport&content=user-scalable=no,width=device-width,maximum-scale=1.0]\n".
            "  link[href=/css&type=text/css]\n";
    $html = "<div></div>\n".
            "<html>\n".
            '  <meta name="viewport" content="user-scalable=no,width=device-width,maximum-scale=1.0" />'.PHP_EOL.
            '  <link href="/css" type="text/css" />'.PHP_EOL.
            "</html>".PHP_EOL;
    $this->hamle->string($hamle);
    $out = $this->hamle->output();
    //$this->assertEquals($html, $out);
    $this->compareXmlStrings("<r>".$html."</r>", "<r>".$out."</r>");
  }
  public function testAttrSquareBracket() {
    $hamle = "a[href=/special\[10\]] Hello [Mate] [ ]";
    $html = '<a href="/special[10]">Hello [Mate] [ ]</a>';
    $this->hamle->string($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
  }
  public function testAttrPlusMinusTimes() {
    $hamle = "a[data-x=a+b&data-y=a*b+c-d/e] Math";
    $html = '<a data-x="a+b" data-y="a*b+c-d/e">Math</a>';
    $this->hamle->string($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
  }
  public function testAttrQuote() {
    $hamle = "a[data-x=\"a\"+'b'] Concat";
    $html = '<a data-x="&quot;a&quot;+\'b\'">Concat</a>';
    $this->hamle->string($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
  }

  public function testAngularArray() {
    $hamle = 'input[name=my-input&ng-bind=myval\[\$index\]]';
    $html = '<input name="my-input" ng-bind="myval[$index]" />';
    $this->hamle->string($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
  }
  public function testAttrDollar() {
    $hamle = "a[href=\$url&class=\$class] {\$title}";
    $html = "<a href=\"https://www.secure.com\" class=\"colored\">This is My TITLE</a>";
    $this->hamle->string($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
  }
  public function testAttrMultiDollar() {
    $hamle = 'a[href=$url&class=$class] $title [$url]';
    $html = "<a href=\"https://www.secure.com\" class=\"colored\">This is My TITLE [https://www.secure.com]</a>";
    $this->hamle->string($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
  }
  public function testAttrEscDollars() {
    $hamle = "html\n  p.quote[data-ref=12\&3 and \\\$4]";
    $html = "<html><p class=\"quote\" data-ref=\"12&amp;3 and \$4\"></p></html>";
    $this->hamle->string($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
  }
  public function testAttrQuotes() {
    $hamle = "html\n  p.quote[data-ref=My Quote \"]";
    $html = "<html><p class=\"quote\" data-ref=\"My Quote &quot;\"></p></html>";
    $this->hamle->string($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
  }
  public function testCommentSingle() {
    $hamle = "html\n".
             "  #box\n".
             "    // Comment is Hidden";
    $html = "<html><div id=\"box\"></div></html>";
    $this->hamle->string($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
  }
  public function testCommentBlock() {
    $hamle = "html\n".
             "  // #box\n".
             "    .message Comment is Hidden";
    $html = "<html></html>";
    $this->hamle->string($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
  }

  public function testAttrZero() {
    $hamle = "html\n  div[width=0]";
    $html = "<html><div width=\"0\"></div></html>";
    $this->hamle->string($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
  }
  public function testCommentHTML() {
    $hamle = "html\n".
             "  / Just a Comment\n";
    $html = "<html>\n".
            "  <!-- Just a Comment -->\n".
            "</html>\n";
    $this->hamle->string($hamle);
    $out = $this->hamle->output();
    $this->assertEquals($html, $out);
  }
  public function testCustomElement() {
    $hamle = "html\n".
        "  cust-elem My Custom Element\n";
    $html = "<html>\n".
        "  <cust-elem>My Custom Element</cust-elem>\n".
        "</html>\n";
    $this->hamle->string($hamle);
    $out = $this->hamle->output();
    $this->assertEquals($html, $out);
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
    $this->hamle->string($hamle);
    $out = $this->hamle->output();
    $this->assertEquals($html, $out);
  }
  public function testTextLine() {
    $hamle = "html\n".
             "   #box Content Line 1\n".
             "     br\n".
             "      _ Box Content Line 2";
    $html = "<html><div id=\"box\">Content Line 1<br />Box Content Line 2</div></html>";
    $this->hamle->string($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
  }

  public function testIncludeFile() {
    $hamle = "|include \"hamle/basic1.hamle\"\n";
    $html = "<html><head><title>This is My TITLE</title></head><body>".
        "<div class='head'></div>".
        "<div class='body'></div>".
        "<div class='foot'></div>".
        "</body></html>";
    $this->hamle->string($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
  }

  /**
   * @expectedException \Seufert\Hamle\Exception
   */
  public function testIncludeFrag() {
    $hamle = "|include \"#frag\"\n";
    $html = "<html><head><title>This is My TITLE</title></head><body>".
        "<div class='head'></div>".
        "<div class='body'></div>".
        "<div class='foot'></div>".
        "</body></html>";
    $this->hamle->string($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
  }

  public function testIterFilter() {
    $hamle = "html\n".
        "  ul\n".
        "    |each \$csv|itersplit\n".
        "      li \$v";
    $html = "<html><ul><li>a</li><li>b</li><li>c</li></ul></html>";
    $this->hamle->string($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
  }
  public function testIterFilterSemi() {
    $hamle = "html\n".
        "  ul\n".
        "    |each \$scsv|itersplit(;)\n".
        "      li[class=item\$k] \$v";
    $html = "<html><ul><li class='item0'>a</li><li class='item1'>b</li><li class='item2'>c</li></ul></html>";
    $this->hamle->string($hamle);
    $out = $this->hamle->output();
//    $this->assertEquals($html, $out);
    $this->compareXmlStrings($html, $out);

  }
  public function testIterFilterEmpty() {
    $hamle = "html\n".
        "  ul\n".
        "    |each \$empty|itersplit(;)\n".
        "      li \$v";
    $html = "<html><ul></ul></html>";
    $this->hamle->string($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
  }


}
