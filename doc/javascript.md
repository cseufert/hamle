# HAMLE for Javascript

## Usage
- Include Library hamle.js
- Add templates into <script> blocks
```html
<script type="text/x-hamle" id="tpl-home">
.hamle
  #thisishamle
    a[href=/hamle] This is HAMLE ($hi)
</script>
```
- Render using hamle object
```js
hamle.autoload();
var model = {hi: "Hi There"};
$("body").append(hamle.tpl.home($model));
```
