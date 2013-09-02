hamle
=====

### Enhanced Version of HAML

This haml port uses slightly different syntax to the general format to use less characters, 
and as a templating language to be combined with a model/controller to insert data into the template expects
that you will be entering far less plain text, and focusing on document structure.

* Indented Stucture (Python like), increased indents means inside element, decreased implies closing tag
* HTML tags just go straigt in, no symbol required (eg `DIV`, `P`, `A`, etc)
  * Tag attributes are specified in squate brackets as url string (eg `A[href=/]` or `.button[data-size=$id]`)
* CSS Like Class and ID, with or without element (eg `.myclass`, `P.quote`, `A#home`, `#content.cols.two` )
* All variable substitiution is PHP like, starts with $ (`$title`, `$text`, etc)
  * Super Global like objects
    * `$_THIS` = current model/controller that is in scope
    * `$_LAST` = array of scopes `$_LAST(-1)` last scope, `$_LAST(0)` root scope, etc
    * `$_SITE` = site globals, etc
    * `$_MODS` = Module/Plugins loaded under this
    * `$_TOOL` = Model/Controller Tools (search, special functions, shopping cart, etc)
  * jQuery like magic `$` variable `$(1024)` opens id, `$(mine)` opens object named mine, `$(page=3)` opens page with id=3
* Iteratorable model/controller can use special methods
  * `|with $(mainmenu)->children()` - changes M/C scope to children of mainmenu, if no results skips section
  * `|each` - iterates through each object in the current scope
  * `|if $id = $_VIEW->id` - include section if this id is the view id
  * `|else` - else for `|with`, and `|if`
  * `|switch $type` - switch based on $type
    * `|case "page"` - include section if switch matches
    * `|default` - inclused section if nothing matches
  * `|include "block/$type/list.hamle"` - bring another hamle file into the doc, with the current M/C scope
* `:filtername` - Use filter named filtername to process section (eg `:CSS a {color:red}` or `:JAVASCRIPT alert('hi');`)
* `// Comment` - not included in output
* `/ Comment` - included as HTML comment
* `_This is just plain text` - Plain text, can be easily translated, _ is only required when text is the first thing on a new line
 

## Example 
```haml
html
  body
    .head
      h1 This is my website
      img[src=/img/myimage.png]
    .content
      .h2 $title
      - with $(1012)->children(“text”)
        ul#mainmenu
          - each
            li.menuitem
              a[href=/$url] $title
                - with $children(“text”)
                  ul.submenu
                    - each $
                      li.menuitem
                        |if $_VIEW->id = $id
                          a.highlight[href=$url] $title
                        |else
                          a[href=$url] $title
      .body
        - if $text
          $text
	   - else
          p Nothing to see here.            
      .foot
        ul.socialicons
          - each $(social_media)->children(“link”)
            li.icon
               a[href=$url&class=$code]
                 img[alt=$title] $->child(“img”)->url
        $show(“my_template.haml”)
        - if $alias != “home”
          a[href=/] Home
        .powered
          $_SITE.poweredby
```
