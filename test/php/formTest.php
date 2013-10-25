<?php

require_once "base.php";

class formTestForm extends hamleForm {
  function setup() {
    $this->fields = array(
      (new hamleField("title"))->required(true),
      (new hamleField("message"))->default("Message goes here"),
    );
  }
}

class formTest extends base {
  public function testStyle() {
    $hamle = "html".PHP_EOL.
             "  body".PHP_EOL.
             '    |form formTestForm $testform'.PHP_EOL.
             '      div.ftitle'.PHP_EOL.
             '        label!title'.PHP_EOL.
             '        input!title[tabindex=1]'.PHP_EOL.
             '      div.fmessage'.PHP_EOL.
             '        label!message'.PHP_EOL.
             '        input!message[tabindex=2]'.PHP_EOL;
    $html = '
<html>
  <body>
    <form action="" method="post" name="formTestForm" enctype="multipart/form-data">
      <div class="ftitle">
        <label for="formTestForm_title" >title</label>
        <input type="text" name="formTestForm_title" value="" tabindex="1" />
      </div>
      <div class="fmessage">
        <label for="formTestForm_message">message</label>
        <input type="text" name="formTestForm_message" value="Message goes here" tabindex="2" />
      </div>
    </form>
  </body>
</html>
';
    $out = $this->hamle->outputStr($hamle);
    var_dump($out);
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
    $out = $this->hamle->outputStr($hamle);
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
    $out = $this->hamle->outputStr($hamle);
    $this->compareXmlStrings($html, $out);
  }

   
}
