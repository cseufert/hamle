<?php
/*
 * This work is licensed under the Creative Commons Attribution 3.0 Unported License. 
 * To view a copy of this license, visit http://creativecommons.org/licenses/by/3.0/
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
function hamleAutoload($class) {
  
  if(preg_match("/^([a-z0-9]+)([A-Z][a-zA-Z0-9]*)(_[a-zA-Z_]+)?$/", $class, $m)) {
    $path = __DIR__."/".$m[1]."/".strtolower($m[2]).".php";
    if(file_exists($path)) require_once($path);
  } elseif(preg_match("/^([a-z0-9]+)$/", $class, $m)) {
    $path = __DIR__."/".$m[1]."/_.php";
    if(file_exists($path)) require_once($path);
  }
}
spl_autoload_register("hamleAutoload");
