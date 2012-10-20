<?php

namespace HttpPHPUnit\Config;

use HttpPHPUnit\Nette\Object;
use Exception;


/**
 * Configuration of HttpPHPUnit.
 *
 * @author Petr Prochazka
 */
class Configuration extends Object
{

	/** @var array phpunit params */
	private $arguments = array();

	/** @var bool */
	private $runned = false;

	/** @var string */
	private $testDirectory;

	/** @var bool */
	private $debug = false;

	/** @var string */
	private $filterDirectory;

	/** @var string|NULL */
	private $filterMethod = NULL;

	/**
	 * Add PHPUnit paramaters like in command-line.
	 * <code>
	 * 	$c->addArgument('--no-globals-backup');
	 * </code>
	 * Don't forget sanitize variables via {@see escapeshellarg()}.
	 * @param string
	 * @return Configuration $this
	 */
	public function addArgument($argument)
	{
		if (!preg_match_all('#((?<=^| )(?:(")[^"]*"|(\')[^\']*\'|[^ ]+))(?:$| )#U', $argument, $tmp))
		{
			throw new Exception("Invalid argument: '$argument'");
		}
		foreach ($tmp[1] as $k => $v)
		{
			$s = strlen($tmp[2][$k])+strlen($tmp[3][$k]);
			$this->arguments[] = substr($v, 0+$s, strlen($v)-$s-$s);
		}
		return $this;
	}

	/**
	 * Directory where are PHPUnit_Framework_TestCase classes.
	 * @param string
	 * @return Configuration $this
	 */
	public function setTestDirectory($directory)
	{
		if (!is_dir($directory))
		{
			throw new Exception("Directory not found: '{$directory}'.");
		}
		$this->testDirectory = realpath($directory);
		return $this;
	}

	/**
	 * Directory where are PHPUnit_Framework_TestCase classes.
	 * @return string
	 */
	public function getTestDirectory()
	{
		return $this->testDirectory;
	}

	/**
	 * Should be PHPUnit running?
	 * @param string
	 * @return Configuration $this
	 * @todo runned nedava smysl
	 */
	public function setRunned($runned)
	{
		$this->runned = (bool) $runned;
		return $this;
	}

	/**
	 * Is PHPUnit running?
	 * @return string
	 */
	public function isRunned()
	{
		return $this->runned;
	}

	/**
	 * Filters which tests to run.
	 *
	 * `$c->setFilter('Events');`
	 * Run all tests in '$testDirectory . '/Events''.
	 *
	 * `$c->setFilter('Events/Events_Autowiring_Test');`
	 * Run all test methods in `$testDirectory . '/Events/Events_Autowiring_Test.php'`.
	 *
	 * `$c->setFilter('Events/Events_Autowiring_Test', 'testNoObject');`
	 * Run only one test `testNoObject` in `$testDirectory . '/Events/Events_Autowiring_Test.php'`.
	 *
	 * `$c->setFilter(NULL);`
	 * Run all tests in '$testDirectory'.
	 *
	 * @param string|NULL relative path
	 * @param string|NULL method
	 * @return Configuration $this
	 * @todo rename filterDirectory > filterPath?
	 */
	public function setFilter($filterDirectory, $filterMethod = NULL)
	{
		$this->filterDirectory = $filterDirectory;
		$this->filterMethod = $filterMethod;
		return $this;
	}

	/**
	 * Get filter path.
	 * @return string|NULL relative path
	 */
	public function getFilterDirectory()
	{
		return $this->filterDirectory;
	}

	/**
	 * Get filter method.
	 * @return string|NULL method
	 */
	public function getFilterMethod()
	{
		return $this->filterMethod;
	}

	/**
	 * Enable/disable debugging.
	 * If enabled after test fails it call 'Nette\Diagnostics\Debugger' and render error (and stop other tests).
	 * Usualy is enabled if is runned one test.
	 * @param bool
	 * @return Configuration $this
	 */
	public function setDebug($debug)
	{
		$this->debug = (bool) $debug;
		return $this;
	}

	/**
	 * Is enabled debugging?
	 * If enabled after test fails it call 'Nette\Diagnostics\Debugger' and render error (and stop other tests).
	 * Usualy is enabled if is runned one test.
	 * @return bool
	 */
	public function isDebug()
	{
		return $this->debug;
	}

	/**
	 * Return arguments in PHPUnit command-line format.
	 * Add filter and test directory if any.
	 * @return array
	 */
	public function getArguments()
	{
		$thisCloned = clone $this;
		if ($this->filterMethod !== NULL)
		{
			$thisCloned->addArgument('--filter ' . escapeshellarg('#(^|::)' . str_replace('"', '\x22', preg_quote($this->filterMethod, '#')) . '($| )#'));
		}

		$arguments = $thisCloned->arguments;

		if ($this->testDirectory OR $this->filterDirectory)
		{
			$arguments[] = $this->testDirectory . ($this->filterDirectory ? '/' . $this->filterDirectory : '');
		}

		return $arguments;
	}

}
