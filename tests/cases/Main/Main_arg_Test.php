<?php

/**
 * @covers HttpPHPUnit\Main::arg
 */
class Main_arg_Test extends TestCase
{
	private $h;

	protected function setUp()
	{
		$this->h = new HttpPHPUnit\Main(new HttpPHPUnit\Loaders\IncludePathLoader(LIBS_DIR . '/PHPUnit'));
	}

	public function test1()
	{
		$this->h->arg('aaa bbb');
		$this->assertSame(array('aaa', 'bbb'), $this->h->configurator->configuration->getArguments());
	}

	public function test2()
	{
		$this->h->arg('"aaa" "bbb"');
		$this->assertSame(array('aaa', 'bbb'), $this->h->configurator->configuration->getArguments());
	}

	public function test3()
	{
		$this->h->arg("'aaa' 'bbb'");
		$this->assertSame(array('aaa', 'bbb'), $this->h->configurator->configuration->getArguments());
	}

	public function test4()
	{
		$this->h->arg('"aaa" \'bbb\' ccc');
		$this->assertSame(array('aaa', 'bbb', 'ccc'), $this->h->configurator->configuration->getArguments());
	}

	public function test5()
	{
		$this->h->arg('aaa \'bbb\' "ccc"');
		$this->assertSame(array('aaa', 'bbb', 'ccc'), $this->h->configurator->configuration->getArguments());
	}

	public function test6()
	{
		$this->h->arg('"a aa" \'b bb\' ccc');
		$this->assertSame(array('a aa', 'b bb', 'ccc'), $this->h->configurator->configuration->getArguments());
	}

	public function test7()
	{
		$this->h->arg('aaa \'b bb\' "c cc"');
		$this->assertSame(array('aaa', 'b bb', 'c cc'), $this->h->configurator->configuration->getArguments());
	}

	public function test8()
	{
		$this->h->arg('"ss');
		$this->assertSame(array('"ss'), $this->h->configurator->configuration->getArguments());
	}

	public function test9()
	{
		$this->h->arg('asdas "A\' "\'sdaSD"as ASD" "\'sdaSDas ASD" aS D"as\'d "s\'d;" \'a\'sd\' a\'sd;a\'"sd;\'as; "g"g"');
		$this->assertSame(array(
			'asdas',
			'"A\'',
			'"\'sdaSD"as',
			'ASD"',
			'\'sdaSDas ASD',
			'aS',
			'D"as\'d',
			's\'d;',
			'\'a\'sd\'',
			'a\'sd;a\'"sd;\'as;',
			'"g"g"',
		), $this->h->configurator->configuration->getArguments());
	}

	public function testEmpty()
	{
		$this->setExpectedException('Exception', "Invalid argument: ''");
		$this->h->arg('');
	}

}
