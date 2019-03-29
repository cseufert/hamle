(Proposal) Inline If Statements for Strings
===

### Basic Inline If

`{|if $value}\$value is Truthy{|el}\$value is Falsy{|fi}`

Evaluate to is Truthy when $value is not __0__, __null__, __''__ or __false__.

### Basic With Statement

`{|wi $model}\$id exists{|el}Nothing Found{|iw}`

Evaluate |wi -> |el in the scope of the with expresstion (eg `$model` in this case). 
If model is empty list or Zero, output |el -> |iw

### Nested Conditions

`{|if $id == 0}{|if $name}$name{|el}Un-named{|fi} equals {$value}{|fi}`
