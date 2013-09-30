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
    $out = $this->hamle->outputStr($hamle);
    $this->compareXmlStrings($html, $out);
  }
  
  public function testShortTags() {
    $hamle = "html\n  meta\n  link\n";
    $html = "<html><meta /><link /></html>";
    $out = $this->hamle->outputStr($hamle);
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
    $out = $this->hamle->outputStr($hamle);
    $this->compareXmlStrings($html, $out);
  }
  public function testAttrSquareBracket() {
    $hamle = "a[href=/special\[10\]] Hello [Mate] [ ]";
    $html = '<a href="/special\[10\]">Hello [Mate] [ ]</a>';
    $out = $this->hamle->outputStr($hamle);
    $this->compareXmlStrings($html, $out);
  }
  public function testAttrDollar() {
    $hamle = "a[href=\$url&class=\$class] \$title";
    $html = "<a href=\"https://www.secure.com\" class=\"colored\">This is My TITLE</a>";
    $out = $this->hamle->outputStr($hamle);
    $this->compareXmlStrings($html, $out);
  }
  public function testAttrMultiDollar() {
    $hamle = 'a[href=$url&class=$class] $title [$url]';
    $html = "<a href=\"https://www.secure.com\" class=\"colored\">This is My TITLE [https://www.secure.com]</a>";
    $out = $this->hamle->outputStr($hamle);
    $this->compareXmlStrings($html, $out);
  }
  public function testAttrEscDollars() {
    $hamle = "html\n  p.quote[data-ref=12\&3 and \\\$4]";
    $html = "<html><p class=\"quote\" data-ref=\"12&amp;3 and \$4\"></p></html>";
    $out = $this->hamle->outputStr($hamle);
    $this->compareXmlStrings($html, $out);
  }
  public function testAttrQuotes() {
    $hamle = "html\n  p.quote[data-ref=My Quote \"]";
    $html = "<html><p class=\"quote\" data-ref=\"My Quote &quot;\"></p></html>";
    $out = $this->hamle->outputStr($hamle);
    $this->compareXmlStrings($html, $out);
  }
  public function testCommentSingle() {
    $hamle = "html\n".
             "  #box\n".
             "    // Comment is Hidden";
    $html = "<html><div id=\"box\"></div></html>";
    $out = $this->hamle->outputStr($hamle);
    $this->compareXmlStrings($html, $out);
  }
  public function testCommentBlock() {
    $hamle = "html\n".
             "  // #box\n".
             "    .message Comment is Hidden";
    $html = "<html></html>";
    $out = $this->hamle->outputStr($hamle);
    $this->compareXmlStrings($html, $out);
  }
  public function testTextLine() {
    $hamle = "html\n".
             "   #box Content Line 1\n".
             "     br\n".
             "      _ Box Content Line 2";
    $html = "<html><div id=\"box\">Content Line 1<br />Box Content Line 2</div></html>";
    $out = $this->hamle->outputStr($hamle);
    $this->compareXmlStrings($html, $out);
  }
  
}

/*
$hamle1 = <<<ENDHAMLE
html
  head
    :javascript //Hi
      console.log("Testing");
    :css
      h1 { color:darkblue; text-decoration:underline;}
  body.test1
    h1 Hello World
    .content
      p.googlelink#special
        a[href=http://www.google.com.au] Try Google
        :javascript document.write("[!]");
      |each $(links)
        p
          a[href=\$url] \$name
      p
        a[href=\$link] \$website
ENDHAMLE;

class mySetup extends hamleSetup {
  function getNamedModel($name, $id = NULL) {
    if($name == "links")
      return new hamleModel_array(array(
              array('url'=>'http://www.test.com',  'name'=>'Test.com'),
              array('url'=>'http://www.test2.com', 'name'=>'Test2.com'),
              array('url'=>'http://www.test3.com', 'name'=>'Test3.com')));
    return parent::getNamedModel($name, $id);
  }
}

$h = new hamle(new hamleModel_array(array(array(
                        'link'=>'https://www.secure.com',
                        'website'=>'Secure.com'))),
                new mySetup());
$h->outputStr($hamle1);
*/