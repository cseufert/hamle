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

class hamleEx_ParseError extends hamleEx { }

class hamleEx_Filter extends hamleEx { }
class hamleEx_NoFilter extends hamleEx_Filter { }

class hamleEx_Unsupported extends hamleEx { }
class hamleEx_OutOfScope extends hamleEx { }

class hamlEx_Uninplemented extends hamlEx { }
