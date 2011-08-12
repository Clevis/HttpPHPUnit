<?php

/**
 * @covers HttpPHPUnit\NetteDebug::__get
 */
class NetteDebug_get_Test extends TestCase
{
	public function test()
	{
		$d = new HttpPHPUnit\NetteDebug;
		$this->assertSame('editor://open/?file=%file&line=%line', $d->editor);
	}

}
