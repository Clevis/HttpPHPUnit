<?php

/**
 * @covers HttpPHPUnit\Events\Autowiring
 * @covers HttpPHPUnit\Events\AutowiringFinder
 * @covers HttpPHPUnit\Events\AutowiringException
 */
class Events_Autowiring_Test extends TestCase
{

	public function test()
	{
		$aw = new HttpPHPUnit\Events\Autowiring;
		$aw->addObject($aw);
		$aw->addObject($this);

		$called = false;
		$r = callback($aw->autowireFunction(function (Events_Autowiring_Test $test, HttpPHPUnit\Events\Autowiring $autowiring, HttpPHPUnit\Events\AutowiringFinder $finder) use ($aw, & $called) {
			$called = true;
			$test->assertSame($aw, $autowiring);
			$test->assertSame($aw, $finder->getByClass('HttpPHPUnit\Events\Autowiring'));
			$test->assertSame($aw, $finder->getByClass('httpphpunit\events\autowiring'));
			$test->assertSame($aw, $finder->getByClass('HTTPPHPUNIT\EVENTS\AUTOWIRING'));
			return 'return';
		}))->invoke();
		$this->assertSame(true, $called);
		$this->assertSame('return', $r);
	}

	/**
	 * @dataProvider dataProviderMethod
	 */
	public function testMethod($callback)
	{
		$aw = new HttpPHPUnit\Events\Autowiring;
		$aw->addObject($aw);
		$aw->addObject($this);

		$r = callback($aw->autowireFunction($callback))->invoke();
		$this->assertSame('return', $r);
	}

	public static function method(Events_Autowiring_Test $test, HttpPHPUnit\Events\Autowiring $autowiring, HttpPHPUnit\Events\AutowiringFinder $finder)
	{
		$test->assertSame($autowiring, $finder->getByClass('HttpPHPUnit\Events\Autowiring'));
		return 'return';
	}

	public function dataProviderMethod()
	{
		return array(
			array(array($this, 'method')),
			array(array(__CLASS__, 'method')),
			array(__CLASS__ . '::method'),
			array(new Events_Autowiring_Test_Object),
			array(callback(__CLASS__, 'method')),
		);
	}

	public function testMoreObject()
	{
		$aw = new HttpPHPUnit\Events\Autowiring;
		$aw->addObject($aw);
		$aw->addObject($aw);
		$test = $this;
		$this->setExpectedException('HttpPHPUnit\Events\AutowiringException', 'Autowired class HttpPHPUnit\Events\Autowiring has more then one object registered.');
		callback($aw->autowireFunction(function (HttpPHPUnit\Events\Autowiring $autowiring) use ($test) {
			$test->fail();
		}))->invoke();
	}

	public function testNoObject()
	{
		$aw = new HttpPHPUnit\Events\Autowiring;
		$test = $this;
		$this->setExpectedException('HttpPHPUnit\Events\AutowiringException', 'Autowired object of type HttpPHPUnit\Events\Autowiring not found.');
		callback($aw->autowireFunction(function (HttpPHPUnit\Events\Autowiring $autowiring) use ($test) {
			$test->fail();
		}))->invoke();
	}

	public function testAllowedClasses()
	{
		$aw = new HttpPHPUnit\Events\Autowiring;
		$aw->addObject($this);
		$aw->addObject($aw);

		$called = false;
		callback($aw->autowireFunction(function (Events_Autowiring_Test $test) use (& $called) {
			$called = true;
		}, 0, HttpPHPUnit\Events\Autowiring::convertAllowedClassesArray(array(
			'Events_Autowiring_Test'
		))))->invoke();
		$this->assertSame(true, $called);
	}

	public function testNotAllowedClasses1()
	{
		$aw = new HttpPHPUnit\Events\Autowiring;
		$aw->addObject($this);
		$aw->addObject($aw);

		$this->setExpectedException('HttpPHPUnit\Events\AutowiringException', 'Autowired class HttpPHPUnit\Events\Autowiring is not allowed in parameter $autowiring.');
		$aw->autowireFunction(function (HttpPHPUnit\Events\Autowiring $autowiring) {}, 0, HttpPHPUnit\Events\Autowiring::convertAllowedClassesArray(array(
			'Events_Autowiring_Test'
		)));
	}

	public function testNotAllowedClasses2()
	{
		$aw = new HttpPHPUnit\Events\Autowiring;
		$aw->addObject($this);
		$aw->addObject($aw);

		$this->setExpectedException('HttpPHPUnit\Events\AutowiringException', 'Autowired class HttpPHPUnit\Events\Autowiring is not allowed in parameter $autowiring.');
		$aw->autowireFunction(function (Events_Autowiring_Test $test, HttpPHPUnit\Events\Autowiring $autowiring) {}, 0, HttpPHPUnit\Events\Autowiring::convertAllowedClassesArray(array(
			'Events_Autowiring_Test'
		)));
	}

	public function testNotAllowedClasses3()
	{
		$aw = new HttpPHPUnit\Events\Autowiring;
		$aw->addObject($this);
		$aw->addObject($aw);

		$called = true;
		$r = callback($aw->autowireFunction(function (Events_Autowiring_Test $test, HttpPHPUnit\Events\AutowiringFinder $finder) use (& $called) {
			$called = true;
			$test->assertSame(NULL, $finder->getByClass('HttpPHPUnit\Events\Autowiring', false));
			return 'return';
		}, 0, HttpPHPUnit\Events\Autowiring::convertAllowedClassesArray(array(
			'Events_Autowiring_Test'
		))))->invoke();
		$this->assertSame(true, $called);
		$this->assertSame('return', $r);
	}

	public function testNotAllowedClasses4()
	{
		$aw = new HttpPHPUnit\Events\Autowiring;
		$aw->addObject($this);
		$aw->addObject($aw);

		$this->setExpectedException('HttpPHPUnit\Events\AutowiringException', 'Autowired class HttpPHPUnit\Events\Autowiring is not allowed.');
		callback($aw->autowireFunction(function (Events_Autowiring_Test $test, HttpPHPUnit\Events\AutowiringFinder $finder) {
			$finder->getByClass('HttpPHPUnit\Events\Autowiring', true);
		}, 0, HttpPHPUnit\Events\Autowiring::convertAllowedClassesArray(array(
			'Events_Autowiring_Test'
		))))->invoke();
	}

	public function testExtraParameter()
	{
		$aw = new HttpPHPUnit\Events\Autowiring;
		$aw->addObject($this);
		$aw->addObject($aw);

		$this->setExpectedException('HttpPHPUnit\Events\AutowiringException', 'Extra parameter $autowiring.');
		$aw->autowireFunction(function ($autowiring) {});
	}

	public function testNotAutowiredParameters()
	{
		$aw = new HttpPHPUnit\Events\Autowiring;
		$aw->addObject($aw);

		$called = false;
		callback($aw->autowireFunction(function (Events_Autowiring_Test $test, $foo, HttpPHPUnit\Events\Autowiring $autowiring) use ($aw, & $called) {
			$called = true;
			$test->assertSame($aw, $autowiring);
			$test->assertSame('string', $foo);
		}, 2))->invoke($this, 'string');
		$this->assertSame(true, $called);
	}

	public function testNotAutowiredParametersMissing()
	{
		$aw = new HttpPHPUnit\Events\Autowiring;
		$aw->addObject($aw);

		$test = $this;
		$this->setExpectedException('HttpPHPUnit\Events\AutowiringException', 'Autowired function expect 2 parameters; 0 given.');
		callback($aw->autowireFunction(function ($foo) use ($test) {
			$test->fail();
		}, 2))->invoke();
	}

	public function testAutowiredUnixestClass1()
	{
		$aw = new HttpPHPUnit\Events\Autowiring;
		$aw->addObject($this);
		$aw->addObject($aw);

		$this->setExpectedException('HttpPHPUnit\Events\AutowiringException', 'Autowired class FooBar is not allowed in parameter $foo.');
		$aw->autowireFunction(function (FooBar $foo) {
			$test->fail();
		}, 0, HttpPHPUnit\Events\Autowiring::convertAllowedClassesArray(array(
			'Events_Autowiring_Test'
		)));
	}

	public function testAutowiredUnixestClass2()
	{
		$aw = new HttpPHPUnit\Events\Autowiring;
		$aw->addObject($this);
		$aw->addObject($aw);

		$this->setExpectedException('HttpPHPUnit\Events\AutowiringException', 'Autowired object of type FooBar not found.');
		callback($aw->autowireFunction(function (FooBar $foo) {
			$test->fail();
		}))->invoke();
	}

	public function testGetByClassNotNeed()
	{
		$aw = new HttpPHPUnit\Events\Autowiring;
		$aw->addObject($aw);
		$aw->addObject($this);

		$called = false;
		$r = callback($aw->autowireFunction(function (Events_Autowiring_Test $test, HttpPHPUnit\Events\AutowiringFinder $finder) use (& $called) {
			$called = true;
			$test->assertSame(NULL, $finder->getByClass('FooBar', false));
			return 'return';
		}))->invoke();
		$this->assertSame(true, $called);
		$this->assertSame('return', $r);
	}

	public function testGetByClassNeed()
	{
		$aw = new HttpPHPUnit\Events\Autowiring;
		$aw->addObject($aw);
		$aw->addObject($this);

		$this->setExpectedException('HttpPHPUnit\Events\AutowiringException', 'Autowired object of type FooBar not found.');
		callback($aw->autowireFunction(function (Events_Autowiring_Test $test, HttpPHPUnit\Events\AutowiringFinder $finder) {
			$finder->getByClass('FooBar', true);
			$test->fail();
		}))->invoke();
	}

}

class Events_Autowiring_Test_Object
{

	public function __invoke(Events_Autowiring_Test $test, HttpPHPUnit\Events\Autowiring $autowiring, HttpPHPUnit\Events\AutowiringFinder $finder)
	{
		$test->assertSame($autowiring, $finder->getByClass('HttpPHPUnit\Events\Autowiring'));
		return 'return';
	}

}
