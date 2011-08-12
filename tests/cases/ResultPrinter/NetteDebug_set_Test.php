<?php

/**
 * @covers HttpPHPUnit\NetteDebug::__set
 */
class NetteDebug_set_Test extends TestCase
{
	public function test()
	{
		$d = new HttpPHPUnit\NetteDebug;
		$tmp = $d->editor;
		$d->editor = 'xxx';
		$this->assertSame('xxx', $d->editor);
		$d->editor = $tmp;
		$this->assertSame('editor://open/?file=%file&line=%line', $d->editor);
	}

}
