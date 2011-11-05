<?php

/**
 * @covers HttpPHPUnit\ResultPrinter::getTestInfo
 */
class ResultPrinter_getTestInfo_Test extends TestCase
{

	public function testDataProvider()
	{
		$r = new HttpPHPUnit\ResultPrinter;
		$t = new self('DataProvider', array(1,2,'<b>3</b>'));
		$r->setAutoFlush(false);
		$r->dir = __DIR__;
		$r->addError($t, new Exception, 0);
		$content = file_get_contents($this->readAttribute($r, 'file'));
		$this->assertContains('<a href="?test=/ResultPrinter_getTestInfo_Test.php::DataProvider+with+data+set+%22%22">ResultPrinter_getTestInfo :: DataProvider</a>', $content);
	}

}
