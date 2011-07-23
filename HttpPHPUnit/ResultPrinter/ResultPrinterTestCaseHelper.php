<?php

abstract class ResultPrinterTestCaseHelper extends PHPUnit_Framework_TestCase
{
	static public function _getDataSetAsString(PHPUnit_Framework_TestCase $test)
	{
		return $test->getDataSetAsString();
	}
}
