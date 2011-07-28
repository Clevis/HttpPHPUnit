<?php

/**
 * @covers HttpPHPUnit\Main::arg
 */
class Main_arg_Test extends TestCase
{
	private $h;

	protected function setUp()
	{
		$this->h = new HttpPHPUnit\Main(LIBS_DIR . '/PHPUnit');
	}

	public function test1()
	{
		$this->h->arg('aaa bbb');
		$this->assertAttributeSame(array('aaa', 'bbb'), 'arg', $this->h);
	}

	public function test2()
	{
		$this->h->arg('"aaa" "bbb"');
		$this->assertAttributeSame(array('aaa', 'bbb'), 'arg', $this->h);
	}

	public function test3()
	{
		$this->h->arg("'aaa' 'bbb'");
		$this->assertAttributeSame(array('aaa', 'bbb'), 'arg', $this->h);
	}

	public function test4()
	{
		$this->h->arg('"aaa" \'bbb\' ccc');
		$this->assertAttributeSame(array('aaa', 'bbb', 'ccc'), 'arg', $this->h);
	}

	public function test5()
	{
		$this->h->arg('aaa \'bbb\' "ccc"');
		$this->assertAttributeSame(array('aaa', 'bbb', 'ccc'), 'arg', $this->h);
	}

	public function test6()
	{
		$this->h->arg('"a aa" \'b bb\' ccc');
		$this->assertAttributeSame(array('a aa', 'b bb', 'ccc'), 'arg', $this->h);
	}

	public function test7()
	{
		$this->h->arg('aaa \'b bb\' "c cc"');
		$this->assertAttributeSame(array('aaa', 'b bb', 'c cc'), 'arg', $this->h);
	}

	public function test8()
	{
		$this->h->arg('"ss');
		$this->assertAttributeSame(array('"ss'), 'arg', $this->h);
	}

	public function test9()
	{
		$this->h->arg('asdas "A\' "\'sdaSD"as ASD" "\'sdaSDas ASD" aS D"as\'d "s\'d;" \'a\'sd\' a\'sd;a\'"sd;\'as; "g"g"');
		$this->assertAttributeSame(array(
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
		), 'arg', $this->h);
	}

	public function testEmpty()
	{
		$this->setExpectedException('Exception', "Invalid argument: ''");
		$this->h->arg('');
	}

}
