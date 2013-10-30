<?php

require_once "base.php";

class fitlerTest extends base {
  public function testStyle() {
    $hamle = "html".PHP_EOL.
             "  head".PHP_EOL.
             '    :css'.PHP_EOL.
             '      div#id.class { color: red; }'.PHP_EOL.
             '      div#id2.class { padding: 5px; }'.PHP_EOL;
    $html = '
<html>
  <head>
    <style>
      div#id.class { color: red; }
      div#id2.class { padding: 5px; }
    </style>
  </head>
</html>
';
    $this->hamle->parse($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
  }

  public function testJavascript() {
    $hamle = "html".PHP_EOL.
             "  head".PHP_EOL.
             '    :javascript'.PHP_EOL.
             '      $(document).ready(function() {'.PHP_EOL.
             '        console.log($("body").html());'.PHP_EOL.
             '      });'.PHP_EOL.
             '  body';
    $html = '
<html>
  <head>
    <script type="text/javascript">
/*<![CDATA[*/
      $(document).ready(function() {
        console.log($("body").html());
      });
/*]]>*/    </script>
  </head>
  <body></body>
</html>
';
    $this->hamle->parse($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
  }
 
    public function testJavascriptVars() {
    $hamle = "head".PHP_EOL.
             '  :javascript'.PHP_EOL.
             '    $(document).ready(function() {'.PHP_EOL.
             '      console.log("{$title}");'.PHP_EOL.
             "      var regExp	= eval('/^aprod_'+grpid+'_\d+$/i\');".PHP_EOL.
             '    });'.PHP_EOL;
    $html = '
<head>
  <script type="text/javascript">
/*<![CDATA[*/
      $(document).ready(function() {
        console.log("This is My TITLE");
      });
/*]]>*/    </script>
</head>
';
    $this->hamle->parse($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
  }

   
}
