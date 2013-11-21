<?php

/**
 * HAMLE Exception base class
 *
 * @author Chris Seufert <chris@seufert.id.au>
 * @package hamle
 * 
 */
class hamleEx extends Exception {
  //put your code here
}

class hamleEx_ParseError extends hamleEx {
  function __construct($message = "" , $code = 0, $previous = NULL) {
    ///@todo  Include Line number & file name within parse error exceptions
    $message .= ", on line ".hamle::getLineNo()." in file ?.hamle";
    parent::__construct($message, $code, $previous);
  }
}
/**
 * HAMLE Exception - File Not Found
 */
class hamleEx_NotFound extends hamleEx { }

/**
 * Class hamleEx_RunTime - Runtime Exceptions
 */
class hamleEx_RunTime extends hamleEx { }

class hamleEx_Filter extends hamleEx { }
class hamleEx_NoFilter extends hamleEx_Filter { }

class hamleEx_Unsupported extends hamleEx { }
class hamleEx_OutOfScope extends hamleEx { }

class hamleEx_Unimplemented extends hamleEx { }

class hamleEx_NoKey extends hamleEx { }
class hamleEx_NoFunc extends hamleEx { }

