<?php

/**
 * @covers HttpPHPUnit\Events\Events
 * @covers HttpPHPUnit\Events\ModuleEvents
 */
class Events_Events_Test extends TestCase
{

	public function test()
	{
		$e = new HttpPHPUnit\Events\Events;
		$called = NULL;
		$e->registerListener('onStart', 'module', function () use (& $called) {
			$called = 'onStart';
		});
		$e->registerListener('onEnd', 'module', function () use (& $called) {
			$called = 'onEnd';
		});
		$this->assertSame(NULL, $called);
		$e->triggerListener('onStart');
		$this->assertSame('onStart', $called);
		$e->triggerListener('onEnd');
		$this->assertSame('onEnd', $called);
	}

	public function testModuleEvents()
	{
		$e = new HttpPHPUnit\Events\Events;
		$called = NULL;
		$me = $e->createModuleEvents('module');
		$me->onStart(function () use (& $called) {
			$called = 'onStart';
		});
		$me->onEnd(function () use (& $called) {
			$called = 'onEnd';
		});
		$this->assertSame(NULL, $called);
		$e->triggerListener('onStart');
		$this->assertSame('onStart', $called);
		$e->triggerListener('onEnd');
		$this->assertSame('onEnd', $called);
	}

	public function testMoreListeners()
	{
		$e = new HttpPHPUnit\Events\Events;
		$called = 0;
		$e->createModuleEvents('module1')
			->onStart(function () use (& $called) {
				$called++;
			})
			->onStart(function () use (& $called) {
				$called++;
			})
		;
		$e->createModuleEvents('module2')->onStart(function () use (& $called) {
			$called++;
		});

		$this->assertSame(0, $called);
		$e->triggerListener('onStart');
		$this->assertSame(3, $called);
	}

	public function testOnce()
	{
		$e = new HttpPHPUnit\Events\Events;
		$e->triggerListener('onStart');
		$this->setExpectedException('Exception', "Event 'onStart' was triggered twice. Only once is allowed.");
		$e->triggerListener('onStart');
	}

	public function testAlreadyTrigered()
	{
		$e = new HttpPHPUnit\Events\Events;
		$e->triggerListener('onStart');
		$this->setExpectedException('Exception', "Event 'onStart' was already triggered.");
		$e->createModuleEvents('module')->onStart(function () {});
	}

	public function testInvalidTypeTrigger()
	{
		$e = new HttpPHPUnit\Events\Events;
		$this->setExpectedException('Exception', "Event 'onUnknown' is not exists.");
		$e->triggerListener('onUnknown');
	}

	public function testInvalidTypeRegister()
	{
		$e = new HttpPHPUnit\Events\Events;
		$this->setExpectedException('Exception', "Event 'onUnknown' is not exists.");
		$e->createModuleEvents('module')->onUnknown(function () {});
	}

	public function testNamedListeners()
	{
		$e = new HttpPHPUnit\Events\Events;
		$called = array(0, NULL, 0, NULL);
		$e->createModuleEvents('module1')
			->onStart(function () use (& $called) {
				$called[0]++;
			}, 'name')
			->onStart(function () use (& $called) {
				$called[0]++;
				$called[1] = 'second';
			}, 'name')
		;
		$e->createModuleEvents('module2')
			->onStart(function () use (& $called) {
				$called[0]++;
			}, 'name')
			->onStart(function () use (& $called) {
				$called[2]++;
				$called[3] = 'second';
			}, 'name')
		;
		$e->triggerListener('onStart');
		$this->assertSame(array(1, 'second', 1, 'second'), $called);
	}

	public function testParametersTriggerExtra()
	{
		$e = new HttpPHPUnit\Events\Events;
		$this->setExpectedException('Exception', "Event 'onStart' expect 0 parameters; 1 given.");
		$e->triggerListener('onStart', array(0));
	}

	public function testGetAutowiring()
	{
		$e = new HttpPHPUnit\Events\Events;
		$this->assertInstanceOf('HttpPHPUnit\Events\Autowiring', $e->getAutowiring());
		$this->assertSame($e->getAutowiring(), $e->getAutowiring());
		$ee = clone $e;
		$this->assertInstanceOf('HttpPHPUnit\Events\Autowiring', $ee->getAutowiring());
		$this->assertNotSame($e->getAutowiring(), $ee->getAutowiring());
	}

}
