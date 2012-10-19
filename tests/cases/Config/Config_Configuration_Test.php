<?php

/**
 * @covers HttpPHPUnit\Config\Configuration
 */
class Config_Configuration_Test extends TestCase
{

	public function testSetTestDirectory()
	{
		$c = new HttpPHPUnit\Config\Configuration;
		$c->setTestDirectory(__DIR__);
		$this->assertSame(array(__DIR__), $c->getArguments());
		$this->assertSame(__DIR__, $c->getTestDirectory());
		$this->assertSame(NULL, $c->getFilterDirectory());
		$this->assertSame(NULL, $c->getFilterMethod());
	}

	public function testSetTestDirectoryNotExists()
	{
		$c = new HttpPHPUnit\Config\Configuration;
		$this->setExpectedException('Exception', "Directory not found: '/not/exists'.");
		$c->setTestDirectory('/not/exists');
	}

	public function testSetFilterDirAndSetTestDirectory()
	{
		$c = new HttpPHPUnit\Config\Configuration;
		$c->setTestDirectory(__DIR__);
		$c->setFilter('x');
		$this->assertSame(array(__DIR__ . '/x'), $c->getArguments());
	}

	public function testSetFilterMethodAndSetTestDirectory()
	{
		$c = new HttpPHPUnit\Config\Configuration;
		$c->setTestDirectory(__DIR__);
		$c->setFilter('x', 'y');
		$this->assertSame(array('--filter', '#(^|::)y($| )#', __DIR__ . '/x'), $c->getArguments());
	}

	public function testSetFilterDir()
	{
		$c = new HttpPHPUnit\Config\Configuration;
		$c->setFilter('x');
		$this->assertSame(array('/x'), $c->getArguments());
		$this->assertSame('x', $c->getFilterDirectory());
		$this->assertSame(NULL, $c->getFilterMethod());
	}

	public function testSetFilterMethod()
	{
		$c = new HttpPHPUnit\Config\Configuration;
		$c->setFilter('x', 'y');
		$this->assertSame(array('--filter', '#(^|::)y($| )#', '/x'), $c->getArguments());
		$this->assertSame('x', $c->getFilterDirectory());
		$this->assertSame('y', $c->getFilterMethod());
	}

	public function testGetArgumentsNotModifyObject()
	{
		$c = new HttpPHPUnit\Config\Configuration;
		$c->setFilter('x', 'y');
		$this->assertSame(array('--filter', '#(^|::)y($| )#', '/x'), $c->getArguments());
		$this->assertSame(array('--filter', '#(^|::)y($| )#', '/x'), $c->getArguments());
		$this->assertSame(array('--filter', '#(^|::)y($| )#', '/x'), $c->getArguments());
	}

	public function testSetFilterMethodDataProvider()
	{
		$c = new HttpPHPUnit\Config\Configuration;
		$c->setFilter('x', 'y with data set #0');
		$this->assertSame(array('--filter', '#(^|::)y with data set \#0($| )#', '/x'), $c->getArguments());
	}

	public function testSetFilterMethodDataProviderName()
	{
		$c = new HttpPHPUnit\Config\Configuration;
		$c->setFilter('x', 'y with data set "foo"');
		$this->assertSame(array('--filter', '#(^|::)y with data set \\x22foo\\x22($| )#', '/x'), $c->getArguments());
	}

	public function testRunned()
	{
		$c = new HttpPHPUnit\Config\Configuration;
		$this->assertSame(false, $c->isRunned());
		$c->setRunned(true);
		$this->assertSame(true, $c->isRunned());
		$c->setRunned(false);
		$this->assertSame(false, $c->isRunned());
	}

	public function testDebug()
	{
		$c = new HttpPHPUnit\Config\Configuration;
		$this->assertSame(false, $c->isDebug());
		$c->setDebug(true);
		$this->assertSame(true, $c->isDebug());
		$c->setDebug(false);
		$this->assertSame(false, $c->isDebug());
	}

}
