<?php

/**
 * @covers HttpPHPUnit\NetteDebug::get
 */
class NetteDebug_singleton_Test extends TestCase
{
	public function testInstance()
	{
		$d = HttpPHPUnit\NetteDebug::get();
		$this->assertInstanceOf('HttpPHPUnit\NetteDebug', $d);
	}

	public function testSingleton()
	{
		$d1 = HttpPHPUnit\NetteDebug::get();
		$d2 = HttpPHPUnit\NetteDebug::get();
		$this->assertSame($d1, $d2);
	}

}
