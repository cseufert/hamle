Some Examples of All Tags

### HTML Tags
html
body
div
a
meta
link
br
hline
table
tbody
tr
td
ul
li

### HTML Tags with Params
link[href=/css&type=text/css]
meta[name=viewport&content=user-scalable=no, width=device-width, maximum-scale=1.0]
div#content
a#home
.head
#menu.head
a#home[href=/index.php&target=_blank]
p.quote[data-ref=\[12\&3 and \$4\]]

### HTML Tags with Content
div#content.fun.midcol $content
.title $title
a[href=$url] $title
p This is my $name, how are you today
p.quote[data-ref=\[12\&3 and \$4\] \]] Hi There
.welcome Hi {$_SITE->user->name}, how are you {$_TOOL->date->today}

### Filters
:javascript alert('hi');
:css a {color:green;}
:php echo "Just Testing";

### Control Stuctures
|with $(mainmenu)->children()
|else
|if $title = "test"
|else
|switch $type
  |case "page"
  |case $id
  |default
|include "block/$type/main.hamle"

### Commenting
// THis is hidden
/ Hello {$_SITE->user->name}, this is put in a html comment even with vars like ID=$id

### Plain Text
_ THis is some text for you to read, even with $title
_ Hi there,  i like \$\$\$'s

### Plain Variable Substitution
{$_TOOL->date->today}
$(social_media)->children(“link”)

### All will parse with
/^((([a-zA-Z]*)([\.#]\w*)*(\[[^\]]*[\]]*\])?)|([_\/][\/]?)|([\|:\$][\w]+)|({?\$[^}]+}?)|)( .*)?$/
/^((([a-zA-Z]*)([\.#]\w*)*(\[([^\\\]]*(\\.)*)+\])?)|([_\/][\/]?)|([\|:\$][\w]+)|({?\$[^}]+}?)|)( .*)?$/
/^(\s*)(?:(?:([a-zA-Z]*)((?:[\.#]\w+)*)(\[(?:[^\\\]]*(?:\\.)*)+\])?)|([_\/][\/]?)|([\|:\$]\w+)|({?\$[^}]+}?)|)(?: (.*))?$/