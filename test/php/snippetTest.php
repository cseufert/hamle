<?php
/**
 * snippetTest - Test Snippet functionality
 *
 * @author Chris Seufert <chris@seufert.id.au>
 */
require_once("base.php");

class snippetTest extends base {
  protected $hamleSnip;
  function model() {
    return new hamleModel_array(array(array(
                        'url'=>'/img/1',
                        'title'=>'The TITLE',
                        'titlebar'=>"My Page",
                        'alt'=>'My Image #1',
                        'nottrue'=>false,
                        'istrue'=>true),array(
                        'url'=>'/img/2',
                        'title'=>'The TITLE',
                        'titlebar'=>"My Page",
                        'alt'=>'My Image #2'),
    ));
  }
  
  function testHeadSnippet() {
    $he = new hamle($this->model(), new snippetHeadSetup());
    $hamle = "html\n".
             "  head\n".
             "    title \$titlebar\n".
             "  body\n".
             "    .content\n";
    $html = "<html><head>\n".
            "   <title>My Page</title>\n".
            '    <link rel="stylesheet" type="text/css" href="/css/bootstrap.css" />'."\n".
            '    <script src="/js/bootstrap.js"></script>'."\n".
            "</head><body>".
            "<div class=\"content\"></div>".
            "</body></html>";
    $he->parse($hamle);
    $out = $he->output();
    $this->compareXmlStrings($html, $out);
  }
  function testHead2Snippet() {
    $he = new hamle($this->model(), new snippetHead2Setup());
    $hamle = "html\n".
        "  head\n".
        "    title \$titlebar\n".
        "  body\n".
        "    .content\n";
    $html = "<html><head>\n".
        '    <script src="/js/jquery.min.js"></script>'."\n".
        "   <title>My Page</title>\n".
        '    <link rel="stylesheet" type="text/css" href="/css/bootstrap.css" />'."\n".
        '    <info loaded="JQuery"></info>'."\n".
        '    <script src="/js/bootstrap.js"></script>'."\n".
        "</head><body>".
        "<div class=\"content\"></div>".
        "</body></html>";
    $he->parse($hamle);
    $out = $he->output();
    $this->compareXmlStrings($html, $out);
  }
  function testTypeClassIDSnippet() {
    $he = new hamle($this->model(), new snippetTypeClassIDSetup());
    $hamle = "html\n".
             "  head\n".
             "    title \$titlebar\n".
             "  body\n".
             "    .content\n".
             "      #new\n".
             "        .test\n";
    $html = "<html><head>\n".
            "   <title>My Page</title>\n".
            "</head><body>".
            "<div class=\"content\"><div id=\"new\"><div class=\"test\">".
            "<div id=\"newtest\">New Div Box</div>".
            "</div></div></div>".
            "</body></html>";
    $he->parse($hamle);
    $out = $he->output();
    $this->compareXmlStrings($html, $out);
  }
  function testReplaceImgSnippet() {
    $he = new hamle($this->model(), new snippetReplaceImgSetup());
    $hamle = "html\n".
             "  head\n".
             "    title \$titlebar\n".
             "  body\n".
             "    |each\n".
             "      .enlarge\n".
             "        img[src={\$url}/thumb&alt=\$alt]\n";
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
  </body>
</html>
TESTHTML;
    $he->parse($hamle);
    $out = $he->output();
    $this->compareXmlStrings($html, $out);
  }
}

class snippetHeadSetup extends hamleSetup {
  function templatePath($f) { return __DIR__."/hamle/$f"; }
  function snippetFiles() { return array(__DIR__."/hamle/snippets/bootstrap.hamle-snip"); }
}
class snippetHead2Setup extends hamleSetup {
  function templatePath($f) { return __DIR__."/hamle/$f"; }
  function snippetFiles() { return array(__DIR__."/hamle/snippets/bootstrap.hamle-snip",
                                      __DIR__."/hamle/snippets/jquery.hamle-snip"); }
}
class snippetTypeClassIDSetup extends hamleSetup {
  function templatePath($f) { return __DIR__."/hamle/$f"; }
  function snippetFiles() { return array(__DIR__."/hamle/snippets/typeclassid.hamle-snip"); }
}
class snippetReplaceImgSetup extends hamleSetup {
  function templatePath($f) { return __DIR__."/hamle/$f"; }
  function snippetFiles() { return array(__DIR__."/hamle/snippets/replace-img.hamle-snip"); }
}
