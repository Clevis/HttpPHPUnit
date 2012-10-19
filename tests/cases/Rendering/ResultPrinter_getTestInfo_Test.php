<?php

/**
 * @covers HttpPHPUnit\Rendering\ResultPrinter::getTestInfo
 */
class ResultPrinter_getTestInfo_Test extends TestCase
{

	public function testDataProvider()
	{
		$configuration = new HttpPHPUnit\Config\Configuration;
		$configuration->setTestDirectory(__DIR__);
		$r = new HttpPHPUnit\Rendering\ResultPrinter($configuration, new HttpPHPUnit\Events\Events, new HttpPHPUnit\Config\Link(array(), NULL, NULL));
		$t = new self('DataProvider', array(1,2,'<b>3</b>'));
		$r->setAutoFlush(false);
		$r->addError($t, new Exception, 0);
		$content = file_get_contents($this->readAttribute($r, 'file'));
		$this->assertContains('<a href="?test=ResultPrinter_getTestInfo_Test.php::DataProvider+with+data+set+%22%22">ResultPrinter_getTestInfo :: DataProvider</a>', $content);
	}

}
