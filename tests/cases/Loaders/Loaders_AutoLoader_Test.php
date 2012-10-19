<?php

/**
 * @covers HttpPHPUnit\Loaders\AutoLoader
 */
class Loaders_AutoLoader_Test extends TestCase
{

	public function testSingleton()
	{
		$i = HttpPHPUnit\Loaders\AutoLoader::getInstance();
		$this->assertInstanceOf('HttpPHPUnit\Loaders\AutoLoader', $i);
		$this->assertSame($i, $i);
	}

	public function testRegister()
	{
		$originCount = count(spl_autoload_functions());
		$i = new HttpPHPUnit\Loaders\AutoLoader;
		$this->assertFalse(in_array(array($i, 'tryLoad'), spl_autoload_functions(), true));
		$i->register();
		$this->assertSame($originCount + 1, count(spl_autoload_functions()));
		$this->assertTrue(in_array(array($i, 'tryLoad'), spl_autoload_functions(), true));
		$tmp = spl_autoload_functions();
		$this->assertSame(array($i, 'tryLoad'), end($tmp));
		$i->unregister();
		$this->assertSame($originCount, count(spl_autoload_functions()));
		$this->assertFalse(in_array(array($i, 'tryLoad'), spl_autoload_functions(), true));
	}

}
