<?php

/**
 * @covers HttpPHPUnit\NetteDebug::__call
 */
class NetteDebug_call_Test extends TestCase
{
	public function test()
	{
		$d = new HttpPHPUnit\NetteDebug;
		$this->assertSame("<pre class=\"nette-dump\">\"xxx\" (3)\n</pre>\n", $d->dump('xxx', true));
	}

}
