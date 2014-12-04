<?php
/**
 * Created by PhpStorm.
 * User: Chris
 * Date: 4/12/2014
 * Time: 12:04 PM
 */
namespace Seufert\Hamle\String;

use Seufert\Hamle\String;

class Plain extends String {
  protected $s;
  protected $type;

  function __construct($s, $type = self::TOKEN_HTML) {
    $this->s = str_replace('\\$', "$", $s);
    $this->type = $type;
  }

  function toPHP() {
    return String::varToCode($this->s);
  }

  function toHTML() {
    if ($this->type == self::TOKEN_CODE)
      return $this->s;
    return htmlspecialchars($this->s);
  }
}