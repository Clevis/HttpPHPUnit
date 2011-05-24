<?php

use Nette\Diagnostics\Debugger as Debug;
use Nette\Utils\Html;

/**
 * @author Petr Prochazka
 */
class HttpPHPUnit_Util_TestDox_ResultPrinter extends PHPUnit_Util_TestDox_ResultPrinter
{
	const FAILURE = 'Failure';
	const ERROR = 'Error';
	const INCOMPLETE = 'Incomplete';
	const SKIPPED = 'Skipped';

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

	/** @var OpenInEditor */
	private $editor;

	/** @var array */
	private $endInfo = array(self::INCOMPLETE => array(), self::SKIPPED => array());

	public function __construct()
	{
		$this->file = tempnam(sys_get_temp_dir(), 'test');
		parent::__construct(fopen($this->file, 'w'));
		$this->editor = new OpenInEditor;
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
		$this->endInfo[self::INCOMPLETE][] = array($test, $e);
		parent::addIncompleteTest($test, $e, $time);
	}

	public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
	{
		$this->endInfo[self::SKIPPED][] = array($test, $e);
		parent::addSkippedTest($test, $e, $time);
	}

	/** Vypise chybu */
	protected function error(PHPUnit_Framework_Test $test, Exception $e, $state)
	{
		if ($this->debug)
		{
			Debug::toStringException($e);
		}
		$this->write("<h2>{$state} ");
		$this->renderInfo($test, $e);
		$this->write('</h2>');
		$message = $e->getMessage();
		if (!$message) $message = '(no message)';
		if ($state === self::ERROR) $message = get_class($e) . ': ' . $message;
		$this->write(Html::el('p', $message));
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
		foreach (array(
			array(self::INCOMPLETE, $this->incomplete),
			array(self::SKIPPED, $this->skipped),
		) as $tmp)
		{
			list($state, $count) = $tmp;
			if ($count)
			{
				$this->write("{$state}: {$count}<br><small>");
				foreach ($this->endInfo[$state] as $tmp)
				{
					list($test, $e) = $tmp;
					$this->renderInfo($test, $e);
					$this->write(" " . htmlspecialchars($e->getMessage()) . "\n");
				}
				$this->write("</small><br><br>");
			}
		}
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
		if ($editor = $this->getEditorLink($path, $e))
		{
			$editor = Html::el('a', '(open in editor)')->href($editor);
			$this->write(" <small><small>$editor</small></small>");
		}
	}

	/**
	 * @see OpenInEditor
	 * @param string
	 * @param Exception
	 * @return string|NULL
	 */
	private function getEditorLink($path, Exception $e)
	{
		if ($e->getFile() === $path)
		{
			return $this->editor->link($path, $e->getLine());
		}
		foreach ($e->getTrace() as $trace)
		{
			if (isset($trace['file']) AND $trace['file'] === $path)
			{
				return $this->editor->link($path, $trace['line']);
			}
		}
		return $this->editor->link($path, 1);
	}

}
