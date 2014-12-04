<?php
use Seufert\Hamle\String;
use Seufert\Hamle\Model\WrapArray;

require_once("../../php/autoload.php");

echo (new String("      div#id.class { color: red; }", String::TOKEN_CODE))->toHTML();
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


exit();
class mySetup extends Setup {
  function getNamedModel($name, $id = NULL) {
    if($name == "basetest")
      return new WrapArray(array(
              array('url'=>'http://www.test.com',  'title'=>'Test.com'),
              array('url'=>'http://www.test2.com', 'title'=>'Test2.com'),
              array('url'=>'http://www.test3.com', 'title'=>'Test3.com')));
    return parent::getNamedModel($name, $id);
  }
}

$h = new Hamle(new WrapArray(array(array(
                        'link'=>'https://www.secure.com',
                        'website'=>'Secure.com'))),
                new mySetup());




    $hamle = "html".PHP_EOL.
             "  body".PHP_EOL.
             '    |with $(basetest)'.PHP_EOL.
             '      ul.menu'.PHP_EOL.
             '        |each'.PHP_EOL.
             '          li.menuitem[data-menu=$title]'.PHP_EOL.
             '            a[href=$url] $title'.PHP_EOL;

var_dump($hs = new hamleStrVar("\$(.heroimage^:1)", hamleStrVar::TOKEN_CONTROL));
var_dump($hs->toPHP());
var_dump($hs->toHTML());
exit;
    
$hamle = "html\n  a.quote[onclick=alert(\"Hi There\")] Hi There";
echo $h->outputStr($hamle);