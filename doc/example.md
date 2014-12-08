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
// Includ the HAMLE Autoload for the library
require_once("php/autoload.php");
use Seufert\Hamle as H;
// Set the default page to page id = 0
$_REQUEST += array('page'=>0);

// The Page content, this could be loaded more dynamically in a real site
$pages = array(
    0=>array('id'=>0,'title'=>"Home",'body'=>'This is the Home Page','bgcolor'=>'#f2f2f2'),
    1=>array('id'=>1,'title'=>"About",'body'=>'This is the about page','bgcolor'=>'#f2f2ff'),
    2=>array('id'=>2,'title'=>"What is This",'body'=>'This is a hamle demo','bgcolor'=>'#fff2f2'),
  );
//The HAMLE Setup Class, this is where you customise the behaviour of HAMLE
class siteHamleSetup extends H\Setup {

  // Tell HAMLE where to find template files, for this example the root dir is fine
  function templatePath($f) { 
    return __DIR__."/$f";
  }
  
  // Tell HAMLE what Snippets to load
  function snippetFiles() { 
    return array(__DIR__."/bootstrap.hamle-snip");
  }
  
  // Tell HAMLE how to get all Pages Model, this would normally be much more implemented
  function getModelTypeTags($typeTags, $sort = [], $limit = 0, $offset = 0) {
    global $pages;
    // I am using a wrapper class, which makes any array a valid model
    if(in_array("pages",array_keys($typeTags))) 
      return new hamleModel_Array($pages);
  }
}
// Create a new model for the current view (the initial model)
$myModel = new H\Model\WrapArray(array($pages[$_REQUEST['page']]));
// Create a new HAMLE parser instance for the site
$hamle = new H\Hamle($myModel, new siteHamleSetup());
// Output the template file
$hamle->load("index.hamle");
echo $hamle->output();
?>
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
              |if $id equals {$[1]->id}
                a.active[href=?page=$id] $title
              |else
                a[href=?page=$id] $title
    .content $body
    .foot
      _ Designed by Chris to show what HAMLE can do
```

`boostrap.hamle-snip`
```hamle
|snippet append html head
  link[rel=stylesheet&type=text/css&href=/css/bootstrap.css]
  script[src=/js/bootstrap.js]
```

#### HTML Output
`?page=0`
```html
<!DOCTYPE html>
<html>
  <head>
    <title>Home</title>
    <link src="style.css" rel="stylesheet" type="text/css" />
    <style>
      h1 {color:olive;}
      body {background-color: #f2f2f2}
    </style>
    <link rel="stylesheet" type="text/css" href="/css/bootstrap.css" />
    <script src="/js/bootstrap.js"></script>
  </head>
  <body>
    <div class="head">
      <img src="/headimg" />
      <div class="headtext">Home</div>
    </div>
    <div class="mainmenu">
      <ul>
        <li>
          <a href="?page=0" class="active">Home</a>
        </li>
        <li>
          <a href="?page=1">About</a>
        </li>
        <li>
          <a href="?page=2">What is This</a>
        </li>
      </ul>
    </div>
    <div class="content">This is the Home Page</div>
    <div class="foot">
      Designed by Chris to show what HAMLE can do
    </div>
  </body>
</html>
```
`?page=1`
```html
<!DOCTYPE html>
<html>
  <head>
    <title>About</title>
    <link src="style.css" rel="stylesheet" type="text/css" />
    <style>
      h1 {color:olive;}
      body {background-color: #f2f2ff}
    </style>
    <link rel="stylesheet" type="text/css" href="/css/bootstrap.css" />
    <script src="/js/bootstrap.js"></script>
  </head>
  <body>
    <div class="head">
      <img src="/headimg" />
      <div class="headtext">About</div>
    </div>
    <div class="mainmenu">
      <ul>
        <li>
          <a href="?page=0">Home</a>
        </li>
        <li>
          <a href="?page=1" class="active">About</a>
        </li>
        <li>
          <a href="?page=2">What is This</a>
        </li>
      </ul>
    </div>
    <div class="content">This is the about page</div>
    <div class="foot">
      Designed by Chris to show what HAMLE can do
    </div>
  </body>
</html>
```