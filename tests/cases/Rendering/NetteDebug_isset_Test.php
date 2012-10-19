<?php

/**
 * @covers HttpPHPUnit\Rendering\NetteDebug::__isset
 */
class NetteDebug_isset_Test extends TestCase
{
	public function testNotExists()
	{
		$d = new HttpPHPUnit\Rendering\NetteDebug;
		$this->assertSame(false, isset($d->foo));
	}

	public function testNull()
	{
		$d = new HttpPHPUnit\Rendering\NetteDebug;
		$tmp = $d->consoleMode;
		$this->assertSame(true, isset($d->consoleMode));
		$d->consoleMode = NULL;
		$this->assertSame(false, isset($d->consoleMode));
		$d->consoleMode = '';
		$this->assertSame(true, isset($d->consoleMode));
		$d->consoleMode = $tmp;
	}
}
