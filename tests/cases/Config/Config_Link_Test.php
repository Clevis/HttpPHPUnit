<?php

/**
 * @covers HttpPHPUnit\Config\Link
 */
class Config_Link_Test extends TestCase
{

	public function testDataProvider1()
	{
		$link = new HttpPHPUnit\Config\Link(array(
			'test' => 'x::y',
		), $_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_FILENAME']);
		$c = new HttpPHPUnit\Config\Configuration;
		$link->applyConfiguration($c);
		$this->assertSame(array('--filter', '#(^|::)y($| )#', '/x'), $c->getArguments());
	}

	public function testDataProvider2()
	{
		$link = new HttpPHPUnit\Config\Link(array(
			'test' => 'x::y with data set #0',
		), $_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_FILENAME']);
		$c = new HttpPHPUnit\Config\Configuration;
		$link->applyConfiguration($c);
		$this->assertSame(array('--filter', '#(^|::)y with data set \#0($| )#', '/x'), $c->getArguments());
	}

	public function testDataProvider2Name()
	{
		$link = new HttpPHPUnit\Config\Link(array(
			'test' => 'x::y with data set "foo"',
		), $_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_FILENAME']);
		$c = new HttpPHPUnit\Config\Configuration;
		$link->applyConfiguration($c);
		$this->assertSame(array('--filter', '#(^|::)y with data set \\x22foo\\x22($| )#', '/x'), $c->getArguments());
	}

	public function testGetLinkToFile()
	{
		$link = new HttpPHPUnit\Config\Link(array(), $_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_FILENAME']);
		$this->assertSame('./cases/Config/Config_Link_Test.php', $link->getLinkToFile(__FILE__));
		$this->assertSame('./../libs/dump.php', $link->getLinkToFile(LIBS_DIR . '/dump.php'));
		$this->assertSame('./../libs/PHPUnit', $link->getLinkToFile(LIBS_DIR . '/PHPUnit'));
	}

	public function testApplyConfiguration1()
	{
		$link = new HttpPHPUnit\Config\Link(array(), $_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_FILENAME']);
		$c = new HttpPHPUnit\Config\Configuration;
		$link->applyConfiguration($c);
		$this->assertSame(false, $c->isDebug());
		$this->assertSame(false, $c->isRunned());
		$this->assertSame(NULL, $c->getFilterDirectory());
		$this->assertSame(NULL, $c->getFilterMethod());
	}

	public function testApplyConfiguration2()
	{
		$link = new HttpPHPUnit\Config\Link(array(
			'run' => '',
		), $_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_FILENAME']);
		$c = new HttpPHPUnit\Config\Configuration;
		$link->applyConfiguration($c);
		$this->assertSame(false, $c->isDebug());
		$this->assertSame(true, $c->isRunned());
		$this->assertSame(NULL, $c->getFilterDirectory());
		$this->assertSame(NULL, $c->getFilterMethod());
	}

	public function testApplyConfiguration3()
	{
		$link = new HttpPHPUnit\Config\Link(array(
			'test' => 'x',
		), $_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_FILENAME']);
		$c = new HttpPHPUnit\Config\Configuration;
		$link->applyConfiguration($c);
		$this->assertSame(false, $c->isDebug());
		$this->assertSame(true, $c->isRunned());
		$this->assertSame('x', $c->getFilterDirectory());
		$this->assertSame(NULL, $c->getFilterMethod());
	}

	public function testApplyConfiguration4()
	{
		$link = new HttpPHPUnit\Config\Link(array(
			'test' => 'x::y',
		), $_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_FILENAME']);
		$c = new HttpPHPUnit\Config\Configuration;
		$link->applyConfiguration($c);
		$this->assertSame(true, $c->isDebug());
		$this->assertSame(true, $c->isRunned());
		$this->assertSame('x', $c->getFilterDirectory());
		$this->assertSame('y', $c->getFilterMethod());
	}

}
