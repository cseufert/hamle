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
      p
        a[href=\$link] \$website
        :javascript document.write("[!]");
ENDHAMLE;

echo hamleParse::str($hamle1);