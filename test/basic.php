<?php

require_once("../php/autoload.php");

$hamle1 = <<<ENDHAMLE
html
  head
    :javascript //Hi
      console.log("Testing");
    :css
      h1 { color:darkblue; text-decoration:underline;}
  body.test1
    h1 Hello World
    .content
      p.googlelink#special
        a[href=http://www.google.com.au] Try Google
        :javascript document.write("[!]");
      |each $(links)
        p
          a[href=\$url] \$name
      p
        a[href=\$link] \$website
ENDHAMLE;

class mySetup extends hamleSetup {
  function getNamedModel($name, $id = NULL) {
    if($name == "links")
      return new hamleModel_array(array(
              array('url'=>'http://www.test.com',  'name'=>'Test.com'),
              array('url'=>'http://www.test2.com', 'name'=>'Test2.com'),
              array('url'=>'http://www.test3.com', 'name'=>'Test3.com')));
    return parent::getNamedModel($name, $id);
  }
}

$h = new hamle(new hamleModel_array(array(array(
                        'link'=>'https://www.secure.com',
                        'website'=>'Secure.com'))),
                new mySetup());
$h->outputStr($hamle1);
