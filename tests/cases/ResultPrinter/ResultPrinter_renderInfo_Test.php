<?php

/**
 * @covers HttpPHPUnit\ResultPrinter::renderInfo
 */
class ResultPrinter_renderInfo_Test extends TestCase
{
	public function testDataProvider()
	{
		$r = new HttpPHPUnit\ResultPrinter;
		$t = new self('DataProvider', array(1,2,'<b>3</b>'));
		$r->setAutoFlush(false);
		$r->addError($t, new Exception, 0);
		$content = file_get_contents($this->readAttribute($r, 'file'));
		$this->assertContains('</small><br><small><small> with data set "" (1, 2, \'&lt;b&gt;3&lt;/b&gt;\')</small></small>', $content);
	}

	public function testDataProviderOneLine()
	{
		$r = new HttpPHPUnit\ResultPrinter;
		$t = new self('DataProvider', array(1,2,'<b>3</b>'));
		$r->setAutoFlush(false);
		$r->addIncompleteTest($t, new Exception, 0);
		$refl = new ReflectionMethod($r, 'endRun');
		$refl->setAccessible(true);
		$refl->invoke($r);
		$content = file_get_contents($this->readAttribute($r, 'file'));
		$this->assertContains('</small><small><small> with data set "" (1, 2, \'&lt;b&gt;3&lt;/b&gt;\')</small></small>', $content);
	}

}
