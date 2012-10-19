<?php

/**
 * @covers HttpPHPUnit\Rendering\NetteDebug::__construct
 */
class NetteDebug_construct_Test extends TestCase
{
	public function test()
	{
		$d = new HttpPHPUnit\Rendering\NetteDebug;
		$this->assertTrue(class_exists('Nette\Diagnostics\Debugger'));
		$this->assertAttributeSame('Nette\Diagnostics\Debugger', 'class', $d);
	}

}
