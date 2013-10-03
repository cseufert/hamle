HAMLE Snippets
==============

### Snippets Use
The concept behind snippets is to modify hamle templates with plugins/extras.

### Commands
* `|snippet {append|prepend|replace|content} <css selector>`
  * `|snippet append` - Add everything within |snippet block as last child of selected elements
  * `|snippet prepend` - Add everything within |snippet block as first child of selected elements
  * `|snippet replace` - Replace selected elements with this block
    * `|snippet content` - can be used within a replace block, to re-insert the content that was displaced

### Example #1
I want to embed an image gallery around any list of images with a class of imggal
index.hamle
```hamle
...
    .content
      $text
    .imggal
      |with $( > photo)
        .main
          img[src=$url&alt=$title]
        .thumbs
          |with
            img[src=$thumburl&alt=$title]
...
```

plugins/imggal/hamle/imggal.hamle
```hamle
|snippet append html head
  script[type=text/javascript&src=/js/imggal]
|snippet replace .imggal .main img
  a[id=imggal$id&onclick=imggal.expand($id)&href=#]
    |snippet content
|snippet replace .imggal .thumbs img
  a[id=imggalthumb$id&onclick=imggal.switch($id)&href=#&data-image-url=$url]
    |snippet content
```

Outpt will go soemthing like this with 2 imeages
```html
  <div class="content">
    ...
  </div>
  <div class="imggal">
    <div class="main">
      <a href="#" onclick="imggal.expand(101)" id="imggal101">
        <img src="/img/101" alt="My First Image" />
      </a>
    </div>
    <div class="thumbs">
      <a href="#" onclick="imggal.switch(101)" id="imggalthumb101" data-image-url="/img/101">
        <img src="/imgthumb/101" alt="My First Image" />
      </a>
      <a href="#" onclick="imggal.switch(102)" id="imggalthumb101" data-image-url="/img/102">
        <img src="/imgthumb/102" alt="My First Image" />
      </a>
    </div>
  </div>  
```


### Example #2
I need to add google analytics to all pages generated, but dont want it in my individual template

fancyskin/index.hamle
```hamle
html
  head
    title $titlebar
  body
    #main
      .head
      .content
        $content
      .foot
```

plugins/ga/hamle/ga.hamle
```hamle
|snippet append html head
  |with (plugins#ga)
    :javascript
     var _gaq = _gaq || [];
    _gaq.push(['_setAccount', '$accountno']);
    _gaq.push(['_trackPageview']);

    (function() {
      var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
      ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
      var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    })();
```

The result will look something like this in the final rendered page
```html
<html>
  <head>
    <title>My Website with GA</title>
    <script type="text/javascript">
     var _gaq = _gaq || [];
    _gaq.push(['_setAccount', 'number1234']);
    _gaq.push(['_trackPageview']);

    (function() {
      var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
      ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
      var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    })();
    </script>
  </head>
  <body>
    ...
  </body>
</html
```