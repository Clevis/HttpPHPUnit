<?php

namespace HttpPHPUnit\Rendering;

use PHPUnit_Framework_TestCase;


/**
 * PHPUnit visibility hack.
 *
 * @author Petr Prochazka
 */
abstract class ResultPrinterTestCaseHelper extends PHPUnit_Framework_TestCase
{

	/**
	 * Gets the data set description of a TestCase.
	 * @see PHPUnit_Framework_TestCase::getDataSetAsString()
	 * @param PHPUnit_Framework_TestCase
	 * @return string
	 */
	static public function _getDataSetAsString(PHPUnit_Framework_TestCase $test)
	{
		return $test->getDataSetAsString();
	}

}
