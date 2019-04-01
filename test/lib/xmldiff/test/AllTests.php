<?php
require_once 'XmlDiffTest.php';

class XmlDiff_AllTests
{
	public static function suite()
	{
		$suite = new PHPUnit\Framework\TestSuite('XmlDiff');
		$suite->addTestSuite('XmlDiffTest');
		return $suite;
	}
}
?>