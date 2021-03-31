<?php

require_once "base.php";

class fitlerTest extends base
{
  protected function setUp(): void
  {
    $this->hamle->setup->minify = false;
  }

  public function testStyle()
  {
    $hamle =
      "html" .
      PHP_EOL .
      "  head" .
      PHP_EOL .
      "    :css" .
      PHP_EOL .
      "      div#id.class { color: red; }" .
      PHP_EOL .
      "      div#id2.class { padding: 5px; }" .
      PHP_EOL;
    $html = '<html>
  <head>
    <style type="text/css">
      div#id.class { color: red; }
      div#id2.class { padding: 5px; }
    </style>
  </head>
</html>
';
    $this->hamle->string($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
  }

  public function testJavascript()
  {
    $this->hamle->setup->minify = false;
    $hamle =
      "html" .
      PHP_EOL .
      "  head" .
      PHP_EOL .
      "    title \$title" .
      PHP_EOL .
      "    :javascript" .
      PHP_EOL .
      '      $(document).ready(function() {' .
      PHP_EOL .
      '        var $body = $("body");' .
      PHP_EOL .
      '        console.log($body.html());' .
      PHP_EOL .
      '        console.log("{$title}");' .
      PHP_EOL .
      "      });" .
      PHP_EOL .
      "  body";
    $html =
      "<html>\n" .
      "  <head>\n" .
      "    <title>This is My TITLE</title>\n" .
      "    <script type=\"text/javascript\">\n" .
      "/*<![CDATA[*/\n" .
      "      $(document).ready(function() {\n" .
      '        var $body = $("body");' .
      "\n" .
      '        console.log($body.html());' .
      "\n" .
      "        console.log(\"This is My TITLE\");\n" .
      "      });\n" .
      "/*]]>*/    </script>\n" .
      "  </head>\n" .
      "  <body></body>\n" .
      "</html>";

    $this->hamle->string($hamle);
    $out = $this->hamle->output();
    $this->assertSame(trim($html), trim($out));
    $this->hamle->setup->minify = true;
  }

  public function testJavascriptVars()
  {
    $hamle =
      "head" .
      PHP_EOL .
      "  :javascript" .
      PHP_EOL .
      '    $(document).ready(function() {' .
      PHP_EOL .
      '      console.log("{$title}");' .
      PHP_EOL .
      "      var regExp	= eval('/^aprod_'+grpid+'_\d+$/i\');" .
      PHP_EOL .
      "    });" .
      PHP_EOL;
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
    $this->hamle->string($hamle);
    $out = $this->hamle->output();
    $this->compareXmlStrings($html, $out);
  }
}
