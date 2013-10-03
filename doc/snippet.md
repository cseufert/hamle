HAMLE Snippets
==============

### Snippets Use
The concept behind snippets is to modify hamle templates with plugins/extras.

### Commands
* `|snippet {append|prepend|replace|content} <css selector>`
  * `|snippet append` - Add everything within |snippet block as last child of selected elements
  * `|snippet prepend` - Add everything within |snippet block as first child of selected elements
  * `|snippet replace` - Replace contents of selected elements with this block
    * `|snippet content` - can be used within a replace block, to re-insert the content that was displaced

### Example
I need to add google analytics to all pages generated, but dont want it in my individual template

fancyskin/index.hamle
```
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
```
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
```
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