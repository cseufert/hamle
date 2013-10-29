Example HAMLE Implementation
============================
1. Basic Website Example Implementation
  1. index.php
  2. index.hamle
  3. bootstrap.hamle-snip
2. HTML Output


#### Very Basic Website Example

`index.php`
```php
<?php
require_once("/hamle/php/autoload.php");
$_REQUEST += array('id'=>0);

$pages = array(
    0=>array('id'=>0,'title'=>"Home",'body'=>'This is the Home Page','bgcolor'=>'#f2f2f2'),
    1=>array('id'=>1,'title'=>"About",'body'=>'This is the about page','bgcolor'=>'#f2f2ff'),
    2=>array('id'=>2,'title'=>"What is This",'body'=>'This is a hamle demo','bgcolor'=>'#fff2f2'),
  );

class siteHamleSetup extends hamleSetup() {
  function themePath($f) { return __DIR__."/$f"; }
  function getSnippets() { return array(__DIR__."/bootstrap.hamle-snip"); }
  function getModelTypeTags($typeTags, $sortDir = 0, $sortField = "", $limit = 0, $offset = 0) {
    if(in_array("pages",array_keys($typeTags))) 
      return new hamleModel_Array($pages);
  }
}

$myModel = new hamleModel_Array(array($pages[$_REQEST['id']]));
$hamle = new hamle($myModel, new siteHamleSetup());
$hamle->outputFile("index.hamle");

```

`index.hamle`
```hamle
_ <!DOCTYPE html>
html
  head
    title $title
    link[src=style.css&rel=stylesheet&type=text/css]
    :css
      h1 {color:olive;}
      body {background-color: {$bgcolor}}
  body
    .head
      img[src=/headimg]
      .headtext $title
    .mainmenu
      |with $(pages)
        ul
          |each
            li
              |if $id equals $[1]->id
                a.active[href=?page=$id] $title
              |else
                a[href=?page=$id] $title
    .content
      $body
    .foot
      _ Designed by Chris to show what HAMLE can do
```

`boostrap.hamle-snip`
```hamle
|snippet append html head
  link[rel=stylesheet&type=text/css&href=/css/bootstrap.css]
  script[src=/js/bootstrap.js]
```