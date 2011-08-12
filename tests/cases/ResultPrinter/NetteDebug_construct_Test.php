<?php

/**
 * @covers HttpPHPUnit\NetteDebug::__construct
 */
class NetteDebug_construct_Test extends TestCase
{
	public function test()
	{
		$d = new HttpPHPUnit\NetteDebug;
		$this->assertTrue(class_exists('Nette\Diagnostics\Debugger'));
		$this->assertAttributeSame('Nette\Diagnostics\Debugger', 'class', $d);
	}

}
