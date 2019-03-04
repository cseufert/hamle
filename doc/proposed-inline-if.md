(Proposal) Inline If Statements for Strings
===

### Basic Inline If

`{|if($value)}\$value is Truthy{|else}\$value is Falsy{|fi}`

Evaluate to is Truthy when $value is not __0__, __null__, __''__ or __false__.

### Nested Inline If

`{|if $id equals 0}{|if $name}$name{|else}Un-named{|fi} equals {$value}{|fi}`