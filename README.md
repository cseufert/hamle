HAMLE
=====

### Enhanced Version of HAML - HAMLE

This haml port uses slightly different syntax to the haml standard format. The main idea
here is to reduce the number of symbols that are typed. The second issue this port is attempting
to address, is to remove native code from the template. The idea here being that your template 
could be served as html from the server, however where supported, it could be rendered by
javascript, with a small json request to retreive the data to fill the template with. The main
focus for hamle is not on markup, but on page/document structure, so inline tags are not a
consideration at this stage.

* Indented Stucture (Python like), increased indents means inside parent element, decreased implies closing tag
* HTML tags just go straigt in, no symbol required (eg `DIV`, `P`, `A`, etc)
  * Tag attributes are specified in square brackets as url encoded string (eg `A[href=/]` or `.button[data-size=$id&class=$tags]`)
  * Escape & with \&, ] with \], etc
* CSS Like Class and ID, with or without element (eg `.myclass`, `P.quote`, `A#home`, `#content.cols.two` )
  * DIVs are assumed if no html tag is specified
  * There is no specific order to the id and class, however the name, if specified must be first
* All variable substitiution is PHP like, starts with $ (`$title`, `$text`, `{$_THIS[-1]->title}`, etc)
  * `{$...}` over `$...` are required when accessing array/object.
  * Scope History
    * `$[0]` = current model/controller that is in scope
      * Usage #1 `|with $[-1]` - Switch back to last scope
      * Usage #2 `{$[1]->title} - read from initial scope
    * `$[-1]` = Last Scope ; Array array of scopes `$[1]` first scope, `$_LAST[-2]` second last scope
  * jQuery like magic `$` function 
    * `$(#1024)` opens id = 1024
    * `$(#mine)` opens object with id/alias = mine
    * `$(page#3)` opens page type with id = 3
    * `$(cat)` opens a list of all category objects
    * `$(product.onsale)` opens a list of all products with onsale tag
    * `$(cart#summary)
    * `$(#mainmenu > page,cat)` returns list of all children of #mainmenu that are pages, and cats
    * `$( > photo, image)` return list of all photos and images who are children of current scope
    * `$( >g2 image)` return list of all imageswho are children group type 2
    * `$( < cat)` returns all parents of type category within the current scope
    * `$( <f manu)` returns first parents that is of type manu within the current scope
    * `$( >l link)` returns last child link of current scope
* Iterateable model/controller list/array can use special methods
  * `|with $(#mainmenu)->children()` - changes M/C scope to children of mainmenu, if no results skips section
  * `|each` - iterates through each object in the current scope
  * `|each $childred()` - iterate through returned data
  * `|recurse $( > menu,page) #3` Recurse up to 3 levels deep using expression provided
  * `|include "block/$type/list.hamle"` - bring another hamle file into the doc, with the current M/C scope
    * Variable substitution is active within the filename
  * Future Ideas
    * `|if $id = $_VIEW->id` - include section if this id is the view id
    * `|unless $title` - if not shortcut
    * `|else` - else for `|with`, and `|if`
    * `|switch $type` - switch based on $type
      * `|case page` - include section if case matches
      * `|default` - include section if none of the cases matches
    * `|iterate` - iterate until end of list
      * `|iterate 3` - iterate 3 times
* `:filtername` - Use filter named filtername to process section (eg `:css a {color:red}` or `:javascript alert('hi');`)
* `// Comment` - not included in output
* `/ Comment` - included as HTML comment
* `_ This is just plain text`
  * Plain text, can be easily translated
  * `_` is only required when text is the first thing on a new line
  * To escape $ sign, use \$
 

## Example 
```haml
html
  body
    .head
      h1 This is my website
      img[src=/img/$imagename.png]
    .content
      .h2 $title
      |with $(#1012 > text)
        ul#mainmenu
          |each
            li.menuitem
              a[href=$url] $title
                |with $( > text)
                  ul.submenu
                    |each
                      li.menuitem
                        |if $[0]->id = $id
                          a.highlight[href=$url] $title
                        |else
                          a[href=$url] $title
      .body
        |if $text
          $text
	    |else
          p Nothing to see here. Page ID=$id is emtpy           
      .foot
        ul.socialicons
          |each $(#socialmedia > link)
            li.icon
               a[href=$url&class=$code]
                 img[alt=$title] $->child(“img”)->url
        |include footer_$type.hamle
        |if $alias != “home”
          a[href=/] Home
        .powered
          $(site)->poweredby
```
