<?php

/**
 * @covers HttpPHPUnit\Rendering\NetteDebug::__call
 */
class NetteDebug_call_Test extends TestCase
{
	public function test()
	{
		$d = new HttpPHPUnit\Rendering\NetteDebug;
		$this->assertSame("<pre class=\"nette-dump\"><span class=\"php-string\">\"xxx\"</span> (3)\n</pre>\n", $d->dump('xxx', true));
	}

}
