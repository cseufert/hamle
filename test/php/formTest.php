<?php

require_once "base.php";

class formTest extends base {
  public function testFormBasic1() {
    $hamle = "html" . PHP_EOL .
      "  body" . PHP_EOL .
      "    |with $(formtest)" . PHP_EOL .
      '      |form formTestForm $testform' . PHP_EOL .
      '        div.ftitle' . PHP_EOL .
      '          label!title' . PHP_EOL .
      '          input!title[tabindex=1]' . PHP_EOL .
      '        div.fmessage' . PHP_EOL .
      '          label!message' . PHP_EOL .
      '          input!message[tabindex=2]' . PHP_EOL .
      '        div.fstring' . PHP_EOL .
      '          label!string' . PHP_EOL .
      '          input!string[tabindex=3]' . PHP_EOL .
      '        div.fsave' . PHP_EOL .
      '          label!save[style=display:none;]' . PHP_EOL .
      '          input!save[tabindex=4]' . PHP_EOL;
    $html = '
<html>
  <body>
    <form action="" method="post" name="formTestForm" enctype="multipart/form-data">
      <input type="hidden" name="formTestForm__submit" value="submit" />
      <div class="ftitle">
        <label for="formTestForm_title" class="Hamle_Field">title</label>
        <input type="text" name="formTestForm_title" class="Hamle_Field" value="" tabindex="1" required="required" id="formTestForm_title" />
      </div>
      <div class="fmessage">
        <label for="formTestForm_message" class="Hamle_Field">message</label>
        <input type="text" name="formTestForm_message" class="Hamle_Field" value="Message goes here" tabindex="2" id="formTestForm_message" />
      </div>
      <div class="fstring">
        <label for="formTestForm_string" class="Hamle_Field">string</label>
        <input type="text" name="formTestForm_string" class="Hamle_Field" value="Tricky String \'&quot;" tabindex="3" id="formTestForm_string" />
      </div>
      <div class="fsave">
        <label for="formTestForm_save" style="display:none;" class="Hamle_Field_Button">save</label>
        <input type="submit" name="formTestForm_save" class="Hamle_Field_Button" value="" tabindex="4" id="formTestForm_save" />
      </div>
      <label for="formTestForm_memo" class="Hamle_Field_Memo" >memo</label>
      <textarea name="formTestForm_memo" class="Hamle_Field_Memo" id="formTestForm_memo">Some &lt;Funky&gt; Text&quot;\'</textarea>
    </form>
  </body>
</html>
';
    $this->hamle->string($hamle);
    $out = $this->hamle->output();
    //$this->assertEquals($html, $out);
    $this->compareXmlStrings($html, $out);
  }

  public function testFormBasic2() {
    $hamle = "html" . PHP_EOL .
      "  body" . PHP_EOL .
      '    |with $(formtest)' . PHP_EOL .
      '      |form formTestForm $testform' . PHP_EOL .
      '        div.fmessage' . PHP_EOL .
      '          label!message' . PHP_EOL .
      '          input!message[tabindex=2]' . PHP_EOL;
    $html = '
<html>
  <body>
    <form action="" method="post" name="formTestForm" enctype="multipart/form-data">
      <div class="fmessage">
        <label for="formTestForm_message" class="Hamle_Field">message</label>
        <input type="text" name="formTestForm_message" class="Hamle_Field" value="Message goes here" tabindex="2" id="formTestForm_message" />
      </div>
      <label for="formTestForm_title" class="Hamle_Field" >title</label>
      <input type="text" name="formTestForm_title" class="Hamle_Field" value="" id="formTestForm_title" required="required" />
      <label for="formTestForm_string" class="Hamle_Field" >string</label>
      <input type="text" name="formTestForm_string" class="Hamle_Field" value="Tricky String \'&quot;" id="formTestForm_string" />
      <label for="formTestForm_memo" class="Hamle_Field_Memo" >memo</label>
      <textarea name="formTestForm_memo" class="Hamle_Field_Memo" id="formTestForm_memo">Some &lt;Funky&gt; Text&quot;\'</textarea>
      <input type="submit" name="formTestForm_save" class="Hamle_Field_Button" value="" id="formTestForm_save" />
      <input type="hidden" name="formTestForm__submit" value="submit" />
    </form>
  </body>
</html>
';
    $this->hamle->string($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
  }

  public function testJavascriptVars() {
    $hamle = "head" . PHP_EOL .
      '  :javascript' . PHP_EOL .
      '    $(document).ready(function() {' . PHP_EOL .
      '      console.log("My Title = {$title}");' . PHP_EOL .
      "      var regExp	= eval('/^aprod_'+grpid+'_\d+$/i\');" . PHP_EOL .
      '    });' . PHP_EOL;
    $html = '<head>
  <script type="text/javascript">
/*<![CDATA[*/
    $(document).ready(function() {
      console.log("My Title = This is My TITLE");
      var regExp	= eval(\'/^aprod_\'+grpid+\'_\d+$/i\\\');
    });
/*]]>*/  </script>
</head>
';
    $this->hamle->string($hamle);
    $out = $this->hamle->output();
    $this->assertEquals($html, $out);
  }

}
