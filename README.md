HAMLE
=====

* [Example Implementation](doc/example.md)

### Enhanced Version of HAML - HAMLE

This haml port uses slightly different syntax to the haml standard format. The main idea
here is to reduce the number of symbols that are typed. The second issue this port is attempting
to address, is to remove native code from the template. The idea here being that your template 
could be served as html from the server, however where supported, it could be rendered by
javascript, with a small json request to retreive the data to fill the template with. The main
focus for hamle is not on markup, but on page/document structure, so inline tags are not a
consideration at this stage. The focus is on clean, readable markup

* CSS Like Class and ID, with or without element (eg `.myclass`, `P.quote`, `A#home`, `#content.cols.two` )
  * DIVs are assumed if no html tag is specified
  * There is no specific order to the id and class, however the name, if specified must be first
  * Multiple IDs (#one#two) will not be recognized, only one will be used
  * Usage #1 `.one Text` becomes `<div class="one">Text</div>`
  * Usage #2 `span.two Foo` becomes `<span class="two">Foo</span>`
  * Usage #3 `#my.mine.ours` becomes `<div id="my" class="mine ours"></div>`
* Indented Structure (Python like), increased indents means inside parent element, decreased implies closing tag
```
html
  body
    div#content
      a[href=/] Home
```
becomes
```
<html>
  <body>
    <div id="content">
      <a href="/">Home</a>
    </div>
  <body>
</html>
```
* HTML tags just go straigt in, no symbol required (eg `DIV`, `P`, `A`, etc)
  * Tag attributes are specified in square brackets as url encoded string
  * Escape & with \&, ] with \], etc
  * Usage #1 `a[href=/] Home` outputs `<a href="/">Home</a>`
  * Usage #2 `.button[data-size=$id&class=$tags]` if $id is 10 and tags = 'Ten Submit' output would be `<button data-size="10" class="Ten Submit" />`
  * Usage #3 `a[data-alt=Button \[1\&2\]] Hi` becomes `<a data-alt="Button [1&amp;2]">Hi</a>`
* All variable substitution is PHP like, starts with $ (`$title`, `$text`, `{$[-1]->title}`, etc)
  * `{$...}` over `$...` are required when inside a filter block, or accessing a property.
  * Scope History
    * `$[0]` = current model/controller that is in scope
      * Usage #1 `|with $[-1]` - Switch back to last scope
      * Usage #2 `{$[1]->title}` - read value `$title` from initial scope
    * `$[-1]` = Last Scope ; Array array of scopes `$[1]` first scope, `$[-2]` second last scope
  * jQuery like magic `$` function 
    * `$({<type>}{@<group>}{#<id>}{.<tags>}{^<sort>}{:{<offset>-}<limit>})`
      * `<type>` is a type that hamleSetup->modelType($type) can find
      * `<group>` is an arbitary id that determines a group type, eg. for differentiating gallery image from header image
      * `<id>` is a unique id, either combined with a type, or globally unique
      * `<tags>` are user defined tags that can be used to help find find data
      * `<sort>` field to sort on, by default ascending, prefix with - for descending, nothing after for random
      * `<limit>` Limit results to n
      * `<offset>` Number of results to skip before return
      * The only required fields are `<type>` or `<id>`, depending on implementation of modelFind
    * `$(#1024)` opens id = 1024
    * `$(#mine)` opens object with id/alias = mine
    * `$(page#3)` opens page type with id = 3
    * `$(cat)` opens a list of all category objects
    * `$(product.onsale)` opens a list of all products with onsale tag
    * `$(cart#summary)` open summary item from cart
    * `$(#mainmenu > page,cat)` returns list of all children of id = mainmenu that are type pages, and cats
    * `$( > photo, image)` return list of all photos and images who are children of current scope
    * `$( < cat)` returns all parents of type category within the current scope
    * `$(page:1)` return first 1 page from  pages
    * `$( > page,cat:3)` return 3 pages or cats (in total) that are children of whats in scope
    * `$(news^postdate)` returns all news posts sorted ascending by postdate
    * `$(news^-postdate)` returns all news posts sorted descending by postdate
    * `$(news^)` returns all news posts sorted in random order
    * `$(link:5-10)` returns links starting at #5 through 10
    * `$(news:4)` return 4 news posts
    * `$(product.featured:4^)` Return 4 randomly selected products with featured tag
    * `$(post:4^postdate)` return first 4 blog posts ordered by postdate
    * `$(post^-postdate:1)` return most recent blog post
* Iterateable model/controller list can use special methods
  * `|with $(#mainmenu > page)` - changes M/C scope to pages under mainmenu, if no results skips section
  * `|each` - iterates through each object in the current scope (set by |with)
  * `|each $(#social > icons)` - iterate through children icons from id = social
  * `|include "block/$type/list.hamle"` - bring another hamle file into the doc, with the current M/C scope
    * Variable substitution is active within the filename
  * `|if $id equals $(view)->id` - include section if this id is the view id
    * `if $title equals a`
    * `if $id notequal 54`
    * `if $tags has sale` - has sale in array
    * `if $title starts Hi`
    * `if $title ends s`
    * `if $title contains Hi` - Contains the string Hi
    * `if $price greater 10`
    * `if $price less 10`
  * `|else` - else for `|with`, and `|if`
  * Future Ideas
    * `|page <key>,<size> <modelIterator>` - eg `|page results,16 $(#gallery > photo)`
      * Special Link Targets: `a!firstpage`; `a!prevpage`; `a!nextpage`; `a!lastpage`; 
      * Special Page Features: Page `div!thispage` of `div!pagecount`; `div!pagelinks`;
    * `|recurse $( > menu,page) #3` Recurse up to 3 levels deep using expression provided
    * `if $price less 10 OR $price greater 20`
    * `|unless $title` - if not shortcut, show block if there is no title
    * `|switch $type` - switch based on $type
      * `|case page` - include section if case matches
      * `|default` - include section if none of the cases matches
    * `|iterateend` - iterate until end of list
      * `|iterate 3` - iterate 3 times
      * eg
```
  / Print a table with 3 column of all the products in the current scope (eg category)
  |with $( > products)
    table
      |iterateend
        tr
          |iterate 3
            td
              a[href=$url] $title
                |with $( > photo)
                  img[src=$url]
          |else
            td
```
* Filters
  * `:filtername` - Use filter named filtername to process section)
  * Usage #1 `:javascript alert('Hi {$(user)->name}');`
  * Usage #2 `:css a {color:{$(site)->linkcolor}}`
  * to use `{$` sequence in css/javascript/etc, you just escape the dollar eg. {\$
* Comments
  * `// Comment` - not included in output
  * `/ Comment` - included as HTML comment
* `_ This is just plain text`
  * Plain text, can be easily translated
  * `_` is only required when text is the first thing on a new line
  * To escape $ sign, use \$
  * use '__' when you do not want to escape any html special chars (ie. you want to include html within your output.
    * eg `__ <!doctype html>`  to print html5 doctype
 

## Example 
```hamle
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
              |if {$[1]->id} = $id
                a.highlight[href=$url] $title
              |else
                a[href=$url] $title
              |with $( > text)
                ul.submenu
                  |each
                    li.menuitem
                      |if {$[1]->id} = $id
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
        |include "footer_$type.hamle"
        |if $alias != “home”
          a[href=/] Home
        .powered
          $(site)->poweredby
```
