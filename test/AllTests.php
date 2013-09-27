<?php
require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'basic.php';

class AllTests extends PHPUnit_Framework_TestSuite {
	/**
	 * Constructs the test suite handler.
	 */
	public function __construct() {
		$this->setName('AllTests');
		$this->addTestSuite('BasicTest');
		//$this->addTestSuite('AttributesTest');
		//$this->addTestSuite('CommentNodeTest');
		//$this->addTestSuite('CompilerTest');
		//$this->addTestSuite('ElementNodeTest');
		//$this->addTestSuite('EvaluateFunctionsTest');
		//$this->addTestSuite('FilterTest');
		//$this->addTestSuite('HamlNodeTest');
		//$this->addTestSuite('HamlPHPClassTest');
		//$this->addTestSuite('HelpersTest');
		//$this->addTestSuite('HtmlStyleAttributesTest');
		//$this->addTestSuite('InterpolationTest');
		//$this->addTestSuite('MarkdownFilterTest');
		//$this->addTestSuite('ObjectReferenceTest');
		//$this->addTestSuite('StringScannerTest');
		//$this->addTestSuite('TagNodeTest');
		//$this->addTestSuite('EvaluateFunctionsTest');
		//$this->addTestSuite('TryHamlTest');
	}

	/**
	 * Creates the suite.
	 */
	public static function suite()
	{
		return new self();
	}
}