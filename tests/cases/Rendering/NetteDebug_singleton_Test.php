<?php

/**
 * @covers HttpPHPUnit\Rendering\NetteDebug::get
 */
class NetteDebug_singleton_Test extends TestCase
{
	public function testInstance()
	{
		$d = HttpPHPUnit\Rendering\NetteDebug::get();
		$this->assertInstanceOf('HttpPHPUnit\Rendering\NetteDebug', $d);
	}

	public function testSingleton()
	{
		$d1 = HttpPHPUnit\Rendering\NetteDebug::get();
		$d2 = HttpPHPUnit\Rendering\NetteDebug::get();
		$this->assertSame($d1, $d2);
	}

}
