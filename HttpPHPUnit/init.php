<?php

class HttpPHPUnit
{
	private $coverage;

	private $arg = array();

	private $testDir;

	public $debug = NULL;

	public function __construct($phpUnitDir = NULL)
	{
		if (!$phpUnitDir) $phpUnitDir = __DIR__ . '/../PHPUnit';
		if (!is_dir($phpUnitDir)) throw new InvalidStateException();

		set_time_limit(0);
		ini_set('memory_limit', '1G');
		if (extension_loaded('xdebug')) xdebug_disable();

		Environment::setMode('console');

		set_include_path($phpUnitDir);

		require_once 'PHPUnit/Autoload.php';
		require_once __DIR__ . '/HttpPHPUnit_TextUI_Command.php';
		require_once __DIR__ . '/HttpPHPUnit_Util_TestDox_ResultPrinter.php';

		$this->testDir = isset($_GET['test']) ? $_GET['test'] : NULL;
		if ($this->testDir AND $pos = strrpos($this->testDir, '::'))
		{
			$this->arg('--filter ' . substr($this->testDir, $pos+2));
			$this->testDir = substr($this->testDir, 0, $pos);
			if ($this->debug === NULL) $this->debug = true;
		}
		if ($this->debug === NULL) $this->debug = false;
	}

	public function coverage($appDir, $coverageDir)
	{
		if ($this->testDir) return $this;
		@mkdir ($coverageDir);
		if (!is_writable($coverageDir)) throw new DirectoryNotFoundException("Report directory is not exist or writable $coverageDir");
		PHP_CodeCoverage_Filter::getInstance()->addDirectoryToWhitelist($appDir);
		$this->coverage = $coverageDir;
		return $this->arg('--coverage-html ' . $coverageDir);
	}

	public function arg($arg)
	{
		$this->arg = array_merge($this->arg, explode(' ', trim($arg)));
		return $this;
	}

	public function run($dir, $arg = '--no-globals-backup --strict')
	{
		$this->arg($arg);
		$arg = $this->arg;
		$dir = realpath($dir);
		$arg[] = $dir . ($this->testDir ? '/' . $this->testDir : '');

		if ($this->coverage AND is_dir($this->coverage))
		{
			foreach (Finder::findFiles('*.html')->from($this->coverage) as $file)
			{
				unlink($file);
			}
		}

		$command = new HttpPHPUnit_TextUI_Command;
		$printer = new HttpPHPUnit_Util_TestDox_ResultPrinter;
		$printer->debug = (bool) $this->debug;
		$printer->dir = $dir . DIRECTORY_SEPARATOR;
		echo "<!DOCTYPE HTML>\n<meta charset='utf-8'>";
		if ($this->testDir) echo "<h1><a href='?'>back</a></h1>";
		$command->run($arg, $printer);
		$printer->render();
		if ($this->coverage)
		{
			$d = str_replace(DIRECTORY_SEPARATOR, '/', $this->dirDiff(dirname($_SERVER['SCRIPT_FILENAME']), $this->coverage));
			echo "<a href='$d'>coverage</a>";
		}
	}

	private function dirDiff($current, $wish)
	{
		$dir1 = explode(DIRECTORY_SEPARATOR, realpath($current));
		$dir2 = explode(DIRECTORY_SEPARATOR, realpath($wish));
		$result = array('.');
		$diferent = array();
		foreach (range(0, max(count($dir1), count($dir2))-1) as $i)
		{
			$part1 = next($dir1); $part2 = next($dir2);
			if (!$diferent AND $part1 === $part2) unset($dir1[$i], $dir2[$i]);
			else $diferent[] = array($part1, $part2);
		}
		foreach ($diferent as $d) if ($d[0]) $result[] = '..';
		foreach ($diferent as $d) if ($d[1]) $result[] = $d[1];
		return implode(DIRECTORY_SEPARATOR, $result);
	}

}
