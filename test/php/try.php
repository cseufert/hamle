<?php
require_once("../php/autoload.php");



class mySetup extends hamleSetup {
  function getNamedModel($name, $id = NULL) {
    if($name == "basetest")
      return new hamleModel_array(array(
              array('url'=>'http://www.test.com',  'title'=>'Test.com'),
              array('url'=>'http://www.test2.com', 'title'=>'Test2.com'),
              array('url'=>'http://www.test3.com', 'title'=>'Test3.com')));
    return parent::getNamedModel($name, $id);
  }
}

$h = new hamle(new hamleModel_array(array(array(
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