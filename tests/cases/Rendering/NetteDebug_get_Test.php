<?php

/**
 * @covers HttpPHPUnit\Rendering\NetteDebug::__get
 */
class NetteDebug_get_Test extends TestCase
{
	public function test()
	{
		$d = new HttpPHPUnit\Rendering\NetteDebug;
		$this->assertSame('editor://open/?file=%file&line=%line', $d->editor);
	}

}
