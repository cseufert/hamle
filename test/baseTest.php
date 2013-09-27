<?php

require_once 'XmlDiff/src/XmlDiff.php';
require_once("../php/autoload.php");

class BaseTest extends PHPUnit_Framework_TestCase {
	/**
	 * @var hamle Hamle Parser
	 */
	protected $hamle;

	public function __construct() {
    $this->hamle = new hamle(new hamleModel_array(array(array(
                        'url'=>'https://www.secure.com',
                        'title'=>'This is My TITLE',
                        'class'=>"colored"))),
                new baseTestSetup());
	}

	public function compareXmlStrings($expected, $actual)
	{
		$docExpected = new DOMDocument();

		try {
			if(!$docExpected->loadXML($expected))
				$this->fail("Couldn't load expected xml into DOMDocument. The xml was: $actual");
		}
		catch (Exception $ex)
		{
			$this->fail("Couldn't load expected xml into DOMDocument. The xml was: $actual");
		}

		$docActual = new DOMDocument();

		try {
			if(!$docActual->loadXML($actual))
				$this->fail("Couldn't load actual xml into DOMDocument. The xml was: $actual");
		}
		catch (Exception $ex)
		{
			$this->fail("Couldn't load actual xml into DOMDocument. The xml was: $actual");
		}

		$differ = new XmlDiff($docExpected, $docActual);

		$delta = (string)$differ->diff();

		$this->assertEmpty($delta, "Differences found: $delta");
	} 
}

class baseTestSetup extends hamleSetup {
  function getNamedModel($name, $id = NULL) {
    if($name == "basetest")
      return new hamleModel_array(array(
              array('url'=>'http://www.test.com',  'title'=>'Test.com'),
              array('url'=>'http://www.test2.com', 'title'=>'Test2.com'),
              array('url'=>'http://www.test3.com', 'title'=>'Test3.com')));
    return parent::getNamedModel($name, $id);
  }
}
