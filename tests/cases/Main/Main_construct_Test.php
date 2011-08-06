<?php

/**
 * @covers HttpPHPUnit\Main::__construct
 */
class Main_construct_Test extends TestCase
{
	public function testDataProvider1()
	{
		$_GET['test'] = 'x::y';
		$h = new HttpPHPUnit\Main(LIBS_DIR . '/PHPUnit');
		$this->assertAttributeSame(array('--filter', '#(^|::)y($| )#'), 'arg', $h);
	}

	public function testDataProvider2()
	{
		$_GET['test'] = 'x::y with data set #0';
		$h = new HttpPHPUnit\Main(LIBS_DIR . '/PHPUnit');
		$this->assertAttributeSame(array('--filter', '#(^|::)y with data set \#0($| )#'), 'arg', $h);
	}

	public function testDataProvider2Name()
	{
		$_GET['test'] = 'x::y with data set "foo"';
		$h = new HttpPHPUnit\Main(LIBS_DIR . '/PHPUnit');
		$this->assertAttributeSame(array('--filter', '#(^|::)y with data set \\x22foo\\x22($| )#'), 'arg', $h);
	}
}
