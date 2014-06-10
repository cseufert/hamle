<?php

require_once "base.php";

class formTest extends base {
  public function testFormBasic1() {
    $hamle = "html".PHP_EOL.
             "  body".PHP_EOL.
             "    |with $(formtest)".PHP_EOL.
             '      |form formTestForm $testform'.PHP_EOL.
             '        div.ftitle'.PHP_EOL.
             '          label!title'.PHP_EOL.
             '          input!title[tabindex=1]'.PHP_EOL.
             '        div.fmessage'.PHP_EOL.
             '          label!message'.PHP_EOL.
             '          input!message[tabindex=2]'.PHP_EOL.
             '        div.fstring'.PHP_EOL.
             '          label!string'.PHP_EOL.
             '          input!string[tabindex=3]'.PHP_EOL.
             '        div.fsave'.PHP_EOL.
             '          label!save[style=display:none;]'.PHP_EOL.
             '          input!save[tabindex=4]'.PHP_EOL;
    $html = '
<html>
  <body>
    <form action="" method="post" name="formTestForm" enctype="multipart/form-data">
      <div class="ftitle">
        <label for="formTestForm_title" class="hamleField">title</label>
        <input type="text" name="formTestForm_title" class="hamleField" value="" tabindex="1" />
      </div>
      <div class="fmessage">
        <label for="formTestForm_message" class="hamleField">message</label>
        <input type="text" name="formTestForm_message" class="hamleField" value="Message goes here" tabindex="2" />
      </div>
      <div class="fstring">
        <label for="formTestForm_string" class="hamleField">string</label>
        <input type="text" name="formTestForm_string" class="hamleField" value="Tricky String \'&quot;" tabindex="3" />
      </div>
      <div class="fsave">
        <label for="formTestForm_save" style="display:none;" class="hamleField_Button">save</label>
        <input type="submit" name="formTestForm_save" class="hamleField_Button" value="" tabindex="4" />
      </div>
    </form>
  </body>
</html>
';
    $this->hamle->parse($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
  }

  public function testFormBasic2() {
    $hamle = "html".PHP_EOL.
             "  body".PHP_EOL.
             '    |with $(formtest)'.PHP_EOL.
             '      |form formTestForm $testform'.PHP_EOL.
             '        div.fmessage'.PHP_EOL.
             '          label!message'.PHP_EOL.
             '          input!message[tabindex=2]'.PHP_EOL;
    $html = '
<html>
  <body>
    <form action="" method="post" name="formTestForm" enctype="multipart/form-data">
      <div class="fmessage">
        <label for="formTestForm_message" class="hamleField">message</label>
        <input type="text" name="formTestForm_message" class="hamleField" value="Message goes here" tabindex="2" />
      </div>
      <label for="formTestForm_title" class="hamleField" >title</label>
      <input type="text" name="formTestForm_title" class="hamleField" value="" />
      <label for="formTestForm_string" class="hamleField" >string</label>
      <input type="text" name="formTestForm_string" class="hamleField" value="Tricky String \'&quot;" />
      <input type="submit" name="formTestForm_save" class="hamleField_Button" value="" />
    </form>
  </body>
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
