<?php

require_once("../php/autoload.php");

$hamle1 = <<<ENDHAMLE
html
  body.test1
    h1 Hello World
    p.googlelink#special
      a[href=http://www.google.com.au] Try Google
ENDHAMLE;

echo hamleParse::str($hamle1);