<?php

/**
 * @covers HttpPHPUnit::__construct
 */
class HttpPHPUnit_construct_Test extends TestCase
{
	public function testDataProvider1()
	{
		$_GET['test'] = 'x::y';
		$h = new HttpPHPUnit(LIBS_DIR . '/PHPUnit');
		$this->assertAttributeSame(array('--filter', '#(^|::)y($| )#'), 'arg', $h);
	}

	public function testDataProvider2()
	{
		$_GET['test'] = 'x::y with data set #0';
		$h = new HttpPHPUnit(LIBS_DIR . '/PHPUnit');
		$this->assertAttributeSame(array('--filter', '#(^|::)y with data set \#0($| )#'), 'arg', $h);
	}
}
