<?php
/**
 * snippetTest - Test Snippet functionality
 *
 * @author Chris Seufert <chris@seufert.id.au>
 */

use Seufert\Hamle;
use Seufert\Hamle\Model\WrapArray;

require_once("base.php");

class snippetTest extends base {
  protected $hamleSnip;

  function model() {
    return new WrapArray([[
      'url' => '/img/1',
      'title' => 'The TITLE',
      'titlebar' => "My Page",
      'alt' => 'My Image #1',
      'nottrue' => false,
      'istrue' => true], [
      'url' => '/img/2',
      'title' => 'The TITLE',
      'titlebar' => "My Page",
      'alt' => 'My Image #2'],
    ]);
  }

  function testHeadSnippet() {
    $he = new Hamle\Hamle($this->model(), new snippetHeadSetup());
    $hamle = "html\n" .
      "  head\n" .
      "    title \$titlebar\n" .
      "  body\n" .
      "    .content\n" .
      "    .head-test\n";
    $html = "<html><head>\n" .
      "   <title>My Page</title>\n" .
      '    <link rel="stylesheet" type="text/css" href="/css/bootstrap.css" />' . "\n" .
      '    <script src="/js/bootstrap.js"></script>' . "\n" .
      "</head><body>" .
      "<div class=\"content\"></div>" .
      "<div class=\"head-test\"></div>" .
      "</body></html>";
    $he->string($hamle);
    $out = $he->output();
    $this->compareXmlStrings($html, $out);
  }

  function testHead2Snippet() {
    $he = new Hamle\Hamle($this->model(), new snippetHead2Setup());
    $hamle = "html\n" .
      "  head\n" .
      "    title \$titlebar\n" .
      "  body\n" .
      "    .content\n" .
      "    .head-test-2\n";
    $html = "<html><head>\n" .
      '    <script src="/js/jquery.min.js"></script>' . "\n" .
      "   <title>My Page</title>\n" .
      '    <link rel="stylesheet" type="text/css" href="/css/bootstrap.css" />' . "\n" .
      '    <info loaded="JQuery"></info>' . "\n" .
      '    <script src="/js/bootstrap.js"></script>' . "\n" .
      "</head><body>" .
      "<div class=\"content\"></div>" .
      "<div class=\"head-test-2\"></div>" .
      "</body></html>";
    $he->string($hamle);
    $out = $he->output();
    $this->compareXmlStrings($html, $out);
  }

  function testTypeClassIDSnippet() {
    $he = new Hamle\Hamle($this->model(), new snippetTypeClassIDSetup());
    $hamle = "html\n" .
      "  head\n" .
      "    title \$titlebar\n" .
      "  body\n" .
      "    .content\n" .
      "      #new\n" .
      "        .test\n";
    $html = "<html><head>\n" .
      "   <title>My Page</title>\n" .
      "</head><body>" .
      "<div class=\"content\"><div id=\"new\"><div class=\"test\">" .
      "<div id=\"newtest\">New Div Box</div>" .
      "</div></div></div>" .
      "</body></html>";
    $he->string($hamle);
    $out = $he->output();
    $this->compareXmlStrings($html, $out);
  }

  function testReplaceImgSnippet() {
    $he = new Hamle\Hamle($this->model(), new snippetReplaceImgSetup());
    $hamle = "html\n" .
      "  head\n" .
      "    title \$titlebar\n" .
      "  body\n" .
      "    |each\n" .
      "      .enlarge\n" .
      "        img[src={\$url}/thumb&alt=\$alt]\n" .
      "    div\n" .
      "      .two.find1\n" .
      "        .stuff\n" .
      "          .more-stuff\n" .
      "            .find2\n" .
      "              .gone\n";
    $html = <<<TESTHTML
<html>
  <head>
    <title>My Page</title>
    <script type="text/javascript" src="/js/lightbox"></script>
  </head>
  <body>
    <div class="enlarge">
      <a href="#" onclick="enlarge('imgid')" data-img="/img/1">
        <img src="/img/1/thumb" alt="My Image #1" />
      </a>
    </div>
    <div class="enlarge">
      <a href="#" onclick="enlarge('imgid')" data-img="/img/2">
        <img src="/img/2/thumb" alt="My Image #2" />
      </a>
    </div>
    <div>
      <div class="two find1">
        <div class="stuff">
          <div class="more-stuff">
            <div class="found">Hi There</div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
TESTHTML;
    $he->string($hamle);
    $out = $he->output();
    $this->assertEquals(trim($html), trim($out));
    $this->compareXmlStrings($html, $out);
  }
}

class snippetHeadSetup extends Hamle\Setup {
  function templatePath($f) {
    return __DIR__ . "/hamle/$f";
  }

  function snippetFiles() {
    return [__DIR__ . "/hamle/snippets/bootstrap.hamle-snip"];
  }
}

class snippetHead2Setup extends Hamle\Setup {
  function templatePath($f) {
    return __DIR__ . "/hamle/$f";
  }

  function snippetFiles() {
    return [__DIR__ . "/hamle/snippets/bootstrap.hamle-snip",
      __DIR__ . "/hamle/snippets/jquery.hamle-snip"];
  }
}

class snippetTypeClassIDSetup extends Hamle\Setup {
  function templatePath($f) {
    return __DIR__ . "/hamle/$f";
  }

  function snippetFiles() {
    return [__DIR__ . "/hamle/snippets/typeclassid.hamle-snip"];
  }
}

class snippetReplaceImgSetup extends Hamle\Setup {
  function templatePath($f) {
    return __DIR__ . "/hamle/$f";
  }

  function snippetFiles() {
    return [__DIR__ . "/hamle/snippets/replace-img.hamle-snip"];
  }
}
