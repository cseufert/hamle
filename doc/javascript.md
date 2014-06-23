# HAMLE for Javascript

## Usage
Include Library hamle.js

Add templates into `<script>` blocks

```html
<script type="text/x-hamle" data-name="home">
.hamle
  #thisishamle
    a[href=/hamle] This is HAMLE ($hi)
</script>
```

Render using hamle object

```javascript
hamle.autoload();
var model = {hi: "Hi There"};
$("body").append(hamle.tpl.home($model));
```

Working Example (variables and conditions not yet implemented)
```html
<!DOCTYPE html>
<html>
  <head>
      <title></title>
    <script type="text/javascript" src="jquery-2.1.1.js"></script>
    <script type="text/javascript" src="uri.js"></script>
    <script type="text/javascript" src="../hamle/js/hamle.js"></script>
  </head>
  <body>
    <script data-name="home" type="text/x-hamle">
    h1.main Hamle Test - $title
    .row
      |if $title
        .colums.large-6
          a[href=//github.com/cseufert/hamle&id=link-hamle] Hamle
        .colums.large-6
          span#rocks ROCKS!!! {$awe}Plus
    </script>
    <script type="text/javascript">
      hamle.autoload();
      $(document.body).append(hamle.tpl.home({"title":"Hi Fellas","awe":"Awesome"}));
    </script>
  </body>
</html>
```
Outputs (in Chrome via HTML Tidy)
```html
<html><head>
  <title></title>
  <script type="text/javascript" src="jquery-2.1.1.js"></script>
  <script type="text/javascript" src="uri.js"></script>
  <script type="text/javascript" src="../hamle/js/hamle.js"></script>
</head>
<body>
  <script data-name="home" type="text/x-hamle">
    h1.main Hamle Test
    .row
      |if $title
        .colums.large-6
          a[href=//github.com/cseufert/hamle&id=link-hamle] Hamle
        .colums.large-6
          span#rocks ROCKS!!!
  </script>
  <script type="text/javascript">
      hamle.autoload();
      $(document.body).append(hamle.tpl.home(null));
  </script>

  <h1>Hamle Test - <span data-var="title">Hi Fellas</span></h1>

  <div data-control="if" data-condition="$title">
    <div>
      <a href="//github.com/cseufert/hamle" id="link-hamle" name="link-hamle">Hamle</a>
    </div>
  </div>

  <div>
    <span>ROCKS!!! <span data-var="awe">Awesome</span>Plus</span>
  </div>
</body></html>
```
