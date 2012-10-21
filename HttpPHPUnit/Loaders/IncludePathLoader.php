<?php

namespace HttpPHPUnit\Loaders;

use HttpPHPUnit\Nette\Object;
use Exception;

/**
 * Load PHPUnit.
 *
 * Autodetect.
 * <code>
 * 	$loader = new IncludePathLoader;
 * 	$loader->load();
 * </code>
 * Try load PHPUnit from include path.
 * If not found it try path: `HttpPHPUnit/../PHPUnit`
 *
 * Set path.
 * <code>
 * 	$loader = new IncludePathLoader(__DIR__ . '/libs/PHPUnit');
 * 	$loader->load();
 * </code>
 * It set include path.
 *
 * Disable autodetection and disable include path modification.
 * Eg. for composer support.
 * <code>
 * 	$loader = new IncludePathLoader(false);
 * 	$loader->load();
 * </code>
 *
 * @author Petr Prochazka
 */
class IncludePathLoader extends Object implements IPHPUnitLoader
{

	/** @var string|NULL|false */
	private $phpUnitDir;

	/**
	 * @param string|NULL|false
	 * 	string = path to PHPUnit
	 * 	null = autodetect
	 * 	false = disable include path modification
	 */
	public function __construct($phpUnitDir = NULL)
	{
		$this->phpUnitDir = $phpUnitDir;
	}

	/**
	 * Load PHPUnit file.
	 * @param string path
	 */
	public function load($file = 'PHPUnit/Autoload.php')
	{
		if ($this->phpUnitDir === NULL)
		{
			list($setIncludePath, $include) = $this->detectPHPUnit($file);
		}
		else
		{
			$setIncludePath = $this->phpUnitDir;
			$include = $file;
		}

		if ($setIncludePath !== false)
		{
			if (!is_dir($setIncludePath))
			{
				throw new Exception("PHPUnit not found: {$setIncludePath}");
			}
			if (!file_exists($setIncludePath . '/' . $file))
			{
				throw new Exception("PHPUnit not found: {$setIncludePath}/" . $file);
			}

			set_include_path($setIncludePath . PATH_SEPARATOR . get_include_path());
		}

		$this->limitedScopeLoad($include);
		$this->phpUnitDir = false;
	}

	/**
	 * @param string path
	 * @return array (string|false $setIncludePath, string $include)
	 */
	protected function detectPHPUnit($file)
	{
		$include = NULL;
		$setIncludePath = NULL;
		$existsResolveFunction = function_exists('stream_resolve_include_path');
		if (!$existsResolveFunction AND @fopen($file, 'r', true)) // PHP < 5.3.2
		{
			// already in include path
			$include = $file;
			$setIncludePath = false;
		}
		else if ($existsResolveFunction AND ($ipFile = stream_resolve_include_path($file)) !== false)
		{
			// already in include path
			$include = $ipFile;
			$setIncludePath = false;
		}
		else
		{
			$dir = __DIR__ . '/../..';
			if (($dir = realpath($dir)) === false)
			{
				$dir = __DIR__ . '/../..';
			}
			$dir .= '/PHPUnit';
			$dFile = $dir . '/' . $file;
			if (is_dir($dir) AND file_exists($dFile))
			{
				// detect PHPUnit; probaly in libs directory
				$include = $dFile;
				$setIncludePath = $dir;
			}
			else
			{
				throw new Exception("Unable autodetect PHPUnit: {$dFile}");
			}
		}
		return array($setIncludePath, $include);
	}

	/**
	 * Includes script in a limited scope.
	 * @param string
	 */
	protected function limitedScopeLoad(/*$file*/)
	{
		require_once func_get_arg(0);
	}

}
