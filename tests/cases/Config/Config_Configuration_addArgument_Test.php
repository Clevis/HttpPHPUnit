<?php

/**
 * @covers HttpPHPUnit\Config\Configuration
 */
class Config_Configuration_addArgument_Test extends TestCase
{
	private $c;

	protected function setUp()
	{
		$this->c = new HttpPHPUnit\Config\Configuration;
	}

	public function test1()
	{
		$this->c->addArgument('aaa bbb');
		$this->assertSame(array('aaa', 'bbb'), $this->c->getArguments());
	}

	public function test2()
	{
		$this->c->addArgument('"aaa" "bbb"');
		$this->assertSame(array('aaa', 'bbb'), $this->c->getArguments());
	}

	public function test3()
	{
		$this->c->addArgument("'aaa' 'bbb'");
		$this->assertSame(array('aaa', 'bbb'), $this->c->getArguments());
	}

	public function test4()
	{
		$this->c->addArgument('"aaa" \'bbb\' ccc');
		$this->assertSame(array('aaa', 'bbb', 'ccc'), $this->c->getArguments());
	}

	public function test5()
	{
		$this->c->addArgument('aaa \'bbb\' "ccc"');
		$this->assertSame(array('aaa', 'bbb', 'ccc'), $this->c->getArguments());
	}

	public function test6()
	{
		$this->c->addArgument('"a aa" \'b bb\' ccc');
		$this->assertSame(array('a aa', 'b bb', 'ccc'), $this->c->getArguments());
	}

	public function test7()
	{
		$this->c->addArgument('aaa \'b bb\' "c cc"');
		$this->assertSame(array('aaa', 'b bb', 'c cc'), $this->c->getArguments());
	}

	public function test8()
	{
		$this->c->addArgument('"ss');
		$this->assertSame(array('"ss'), $this->c->getArguments());
	}

	public function test9()
	{
		$this->c->addArgument('asdas "A\' "\'sdaSD"as ASD" "\'sdaSDas ASD" aS D"as\'d "s\'d;" \'a\'sd\' a\'sd;a\'"sd;\'as; "g"g"');
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
		), $this->c->getArguments());
	}

	public function testEmpty()
	{
		$this->setExpectedException('Exception', "Invalid argument: ''");
		$this->c->addArgument('');
	}

}
