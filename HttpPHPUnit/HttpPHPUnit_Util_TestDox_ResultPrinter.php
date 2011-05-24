<?php

use Nette\Diagnostics\Debugger as Debug;

/**
 * @author Petr Prochazka
 */
class HttpPHPUnit_Util_TestDox_ResultPrinter extends PHPUnit_Util_TestDox_ResultPrinter
{
	const FAILURE = 'Failure';
	const ERROR = 'Error';
	const INCOMPLETE = 'Incomplete';

	/** @var bool true display Nette\Diagnostics\Debugger */
	public $debug = false;

	/** @var string dir to tests */
	public $dir;

	/** @var string temp file */
	private $file;

	protected $printsHTML = true;

	protected $autoFlush = true;

	/** @var mixed */
	private $netteDebugHandler;

	public function __construct()
	{
		$this->file = tempnam(sys_get_temp_dir(), 'test');
		parent::__construct(fopen($this->file, 'w'));
	}

	/** Po kazdem testu vypise */
	public function incrementalFlush()
	{
		echo file_get_contents($this->file);
		$this->out = fopen($this->file, 'w');
		while (@ob_end_flush());
		flush();
	}

	/** Za vsema testama */
	public function flush()
	{
		parent::flush();
		$this->incrementalFlush();
	}

	/** Dorenderuje zbytek */
	public function render()
	{
		$this->incrementalFlush();
		@unlink($this->file);
	}

	/** Assert error */
	public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
	{
		$this->error($test, $e, self::FAILURE);
		parent::addFailure($test, $e, $time);
	}

	/** Other error */
	public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
	{
		$this->error($test, $e, self::ERROR);
		parent::addError($test, $e, $time);
	}

	/** @see PHPUnit_Framework_Assert::markTestIncomplete() */
	public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
	{
		$this->error($test, $e, self::INCOMPLETE);
		parent::addIncompleteTest($test, $e, $time);
	}

	/** Vypise chybu */
	protected function error(PHPUnit_Framework_Test $test, Exception $e, $state)
	{
		if ($this->debug AND $state !== self::INCOMPLETE)
		{
			Debug::toStringException($e);
		}
		$this->write("<h2>{$state} ");
		$this->renderInfo($test, $e);
		$this->write('</h2>');
		$this->write(
			$state === self::ERROR ?
			'<p><pre>' . htmlspecialchars($e) . '</pre></p>' :
			'<p>' . htmlspecialchars($e->getMessage()) . '</p>'
		);
	}

	/** Vysledek celeho testu */
	protected function endRun()
	{
		parent::endRun();
		if (!$this->failed)
		{
			$this->write("<h1>OK {$this->successful}</h1>");
		}
		else
		{
			$this->write("<h1>FAILURES! {$this->failed}</h1>");
		}
		if ($this->incomplete) $this->write("Incomplete: {$this->incomplete}<br>");
		if ($this->skipped) $this->write("Skipped: {$this->skipped}<br>");
		if ($this->failed) $this->write("Completed: {$this->successful}<br>");
	}

	/** Odregistruje Debug aby chyby chytal PHPUnit */
	public function startTest(PHPUnit_Framework_Test $test)
	{
		parent::startTest($test);
		if (Debug::isEnabled())
		{
			// ziskat posledni registrovany handler a zrusi ho
			$this->netteDebugHandler = set_error_handler(create_function('', ''));
			restore_error_handler(); restore_error_handler();
		}
	}

	/** Zaregistruje zpet Debug */
	public function endTest(PHPUnit_Framework_Test $test, $time)
	{
		if (Debug::isEnabled())
		{
			set_error_handler($this->netteDebugHandler);
		}
		if ($this->testStatus == PHPUnit_Runner_BaseTestRunner::STATUS_PASSED)
		{
			$this->successful++;
		}
		parent::startTest($test, $time);
	}

	/**
	 * @param PHPUnit_Framework_Test
	 * @return array
	 * array(
	 * 		'FooBarTest',
	 * 		'testFooBar',
	 *		'/tests/Foo/FooBarTest.php',
	 * 		'Foo/FooBarTest.php::testFooBar',
	 * )
	 */
	private function getTestInfo(PHPUnit_Framework_Test $test)
	{
		$r = new ReflectionClass($test);
		$path = $r->getFileName();
		$class = preg_replace('#_?Test$#si', '', get_class($test));
		$method = $test->getName(false);
		$filter = NULL;
		if ($this->dir AND strncasecmp($path, $this->dir, strlen($this->dir)) === 0)
		{
			$dir = substr($path, strlen($this->dir));
			$filter = strtr(urlencode($dir), array('%5C' => '\\', '%2F' => '/')) . '::' . urlencode($method);
		}
		return array($class, $method, $path, $filter);
	}

	/**
	 * @param PHPUnit_Framework_Test
	 * @param Exception
	 */
	private function renderInfo(PHPUnit_Framework_Test $test, Exception $e)
	{
		list($class, $method, $path, $filter) = $this->getTestInfo($test);
		$this->write(Html::el($filter ? 'a' : NULL, "$class :: $method")->href("?test=$filter"));
	}

}
