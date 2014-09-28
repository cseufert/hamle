<?php
/**
 * PHP HAMLE Autloader
 * 
 * @author Christopher Seufert <chris@seufert.id.au>
 */

/**
 * Main autoload function
 * @param string $class Class name to be autoloaded
 */
spl_autoload_register(function($class) {
  
  if(preg_match("/^hamle(?:([A-Z][a-zA-Z0-9]*)(_[a-zA-Z_]+)?)?$/", $class, $m)) {
    $path = __DIR__."/hamle/".(isset($m[1])?strtolower($m[1]):"_").".php";
    if(is_file($path)) require_once($path);
  }
});

