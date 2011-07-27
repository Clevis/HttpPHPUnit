<?php

/**
 * @covers HttpPHPUnit_Util_TestDox_ResultPrinter::endRun
 */
class ResultPrinter_endRun_Test extends TestCase
{
	/** @var HttpPHPUnit_Util_TestDox_ResultPrinter */
	private $r;
	private $t;

	protected function setUp()
	{
		$this->r = new HttpPHPUnit_Util_TestDox_ResultPrinter;
		$this->r->setAutoFlush(false);
		$this->t = new self;
	}

	private function getContent()
	{
		$refl = new ReflectionMethod($this->r, 'endRun');
		$refl->setAccessible(true);
		$refl->invoke($this->r);
		return file_get_contents($this->readAttribute($this->r, 'file'));
	}

	private function t($state, $m)
	{
		$this->assertContains('<p id="sentence" data-state="'.$state.'">' . $m . '</p>', $this->getContent());
	}

	public function testOneIncomplete()
	{
		$this->r->addIncompleteTest($this->t, new Exception, 0);
		$this->t('unknown', 'Test was incomplete.');
	}

	public function testMoreIncomplete()
	{
		$this->r->addIncompleteTest($this->t, new Exception, 0);
		$this->r->addIncompleteTest($this->t, new Exception, 0);
		$this->t('unknown', '2 tests were incomplete.');
	}

	public function testOneSkipped()
	{
		$this->r->addSkippedTest($this->t, new Exception, 0);
		$this->t('unknown', 'Test was skipped.');
	}

	public function testMoreSkipped()
	{
		$this->r->addSkippedTest($this->t, new Exception, 0);
		$this->r->addSkippedTest($this->t, new Exception, 0);
		$this->t('unknown', '2 tests were skipped.');
	}

	public function testOneSuccess()
	{
		$this->r->endTest($this->t, 0);
		$this->t('ok', 'Test was successful.');
	}

	public function testMoreSuccess()
	{
		$this->r->endTest($this->t, 0);
		$this->r->endTest($this->t, 0);
		$this->t('ok', '2 tests were successful.');
	}

	public function testOneFail()
	{
		$this->r->addFailure($this->t, new PHPUnit_Framework_AssertionFailedError, 0);
		$this->t('failure', 'Test failed!');
	}

	public function testMoreFail()
	{
		$this->r->addFailure($this->t, new PHPUnit_Framework_AssertionFailedError, 0);
		$this->r->addFailure($this->t, new PHPUnit_Framework_AssertionFailedError, 0);
		$this->t('failure', '2 tests failed!');
	}

	public function testSuccessAndFaile()
	{
		$this->r->endTest($this->t, 0);
		$this->r->addFailure($this->t, new PHPUnit_Framework_AssertionFailedError, 0);
		$this->t('failure', '1 test failed!');
	}

	public function testSuccessAndSkippedAndIncomplete()
	{
		$this->r->endTest($this->t, 0);
		$this->r->addSkippedTest($this->t, new Exception, 0);
		$this->r->addIncompleteTest($this->t, new Exception, 0);
		$this->t('ok', '1 test was successful. 1 test was incomplete. 1 test was skipped.');
	}

	public function testFailAndSkippedAndIncomplete()
	{
		$this->r->endTest($this->t, 0);
		$this->r->addFailure($this->t, new PHPUnit_Framework_AssertionFailedError, 0);
		$this->r->addSkippedTest($this->t, new Exception, 0);
		$this->r->addIncompleteTest($this->t, new Exception, 0);
		$this->t('failure', '1 test failed! 1 test was incomplete. 1 test was skipped.');
	}

}
