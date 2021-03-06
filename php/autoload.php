<?php
/*
This project is Licenced under The MIT License (MIT)

Copyright (c) 2014 Christopher Seufert

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

 */
/**
 * PHP HAMLE Autloader
 *
 * @author Christopher Seufert <chris@seufert.id.au>
 */

/**
 * Main autoload function
 * @param string $class Class name to be autoloaded
 */
spl_autoload_register(
  function ($class) {
    if (strpos($class, 'Seufert\\Hamle\\') === 0) {
      $s = DIRECTORY_SEPARATOR;
      $class = str_replace('\\', $s, substr($class, 14));
      $path = __DIR__ . $s . 'hamle' . $s . "$class.php";
      if (is_file($path)) {
        include_once $path;
      }
    }
  },
  true,
  true,
);
