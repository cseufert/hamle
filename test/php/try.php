<?php
use Seufert\Hamle\Setup;
use Seufert\Hamle\Hamle;
use Seufert\Hamle\Text;
use Seufert\Hamle\Model\WrapArray;

require_once("../../php/autoload.php");

echo (new Text("      div#id.class { color: red; }", Text::TOKEN_CODE))->toHTML();
exit();
class snippetSetup extends Setup {
  function themePath($f) {
    return __DIR__."/hamle/$f";
  }
  function getSnippets() {
    return array(__DIR__."/hamle/snippets/replace-img.hamle-snip");
  }

}

$ho = new Hamle(new WrapArray(array(array(
                        'url'=>'/img/1',
                        'title'=>'The TITLE',
                        'titlebar'=>"My Page",
                        'alt'=>'My Image #1'),array(
                        'url'=>'/img/2',
                        'title'=>'The TITLE',
                        'titlebar'=>"My Page",
                        'alt'=>'My Image #2'),
    )),
                new snippetSetup());


    $hamle = "html\n".
             "  head\n".
             "    title \$titlebar\n".
             "  body\n".
             "    |each\n".
             "      .enlarge\n".
             "        img[src={\$url}/thumb&alt=\$alt]\n";
    $html = "<html><head>\n".
            "   <title>The TITLE</title>\n".
            "   <title>The TITLE</title>\n".
            "   <title>The TITLE</title>\n".
            "</head><body>".
            "<div class=\"content\"></div>".
            "</body></html>";
    echo $ho->outputStr($hamle);

