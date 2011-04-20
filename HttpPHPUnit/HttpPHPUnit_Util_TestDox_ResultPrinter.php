<?php

class HttpPHPUnit_Util_TestDox_ResultPrinter extends PHPUnit_Util_TestDox_ResultPrinter
{
	private $file;

	public $debug = false;

	public $dir;

	protected $printsHTML = TRUE;

	protected $autoFlush = true;

	public function __construct()
	{
		$this->file = tempnam(sys_get_temp_dir(), 'test');
		parent::__construct(fopen($this->file, 'w'));
	}

	public function flush()
	{
		parent::flush();
		$this->incrementalFlush();
	}

	public function incrementalFlush()
	{
		echo file_get_contents($this->file);
		$this->out = fopen($this->file, 'w');
		while (@ob_end_flush());
		flush();
	}

	public function render()
	{
		$this->incrementalFlush();
		@unlink($this->file);
	}

	public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
	{
		if ($this->debug) Debug::toStringException($e);
		$this->ass($test, $e, 'Failure');
		parent::addFailure($test, $e, $time);
	}

	public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
	{
		if ($this->debug) Debug::toStringException($e);
		$this->ass($test, $e, 'Error');
		parent::addError($test, $e, $time);
	}

	public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
	{
		$this->ass($test, $e, 'Incomplete');
		parent::addIncompleteTest($test, $e, $time);
	}

	protected function ass(PHPUnit_Framework_Test $test, Exception $e, $state)
	{
		$r = new ReflectionClass($test);
		$dir = $r->getFileName();
		if ($this->dir) $dir = preg_replace('#^' . preg_quote($this->dir, '#') . '#si', '', $dir);
		$class = preg_replace('#^Tests\\\\(.*)Test$#si', '$1',get_class($test));
		$method = lcfirst($this->currentTestMethodPrettified);
		$this->write("<h2>{$state} <a href='?dir={$dir}'>{$class}::{$method}</a></h2>");
		$this->write(
			$state === 'Error' ?
			'<p><pre>' . htmlspecialchars($e) . '</pre></p>' :
			'<p>' . htmlspecialchars($e->getMessage()) . '</p>'
		);
	}

	protected function endRun()
	{
		if (!$this->failed)
		{
			$this->write('<h1>OK</h1>');
		}
		else
		{
			$this->write("<h1>FAILURES! {$this->failed}</h1>");
		}
		if ($this->incomplete) $this->write("Incomplete: {$this->incomplete}<br>");
		if ($this->skipped) $this->write("Skipped: {$this->skipped}<br>");
	}

}
