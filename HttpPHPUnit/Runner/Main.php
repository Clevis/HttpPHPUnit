<?php

namespace HttpPHPUnit;

use HttpPHPUnit\Nette\Object;
use PHP_CodeCoverage;
use Exception;


/**
 * <code>
 * 	require_once __DIR__ . '/libs/HttpPHPUnit/init.php';
 *
 * 	$http = new HttpPHPUnit;
 * 	require_once __DIR__ . '/boot.php';
 * 	$http->coverage(__DIR__ . '/../app', __DIR__ . '/coverage');
 * 	$http->run(__DIR__ . '/cases');
 *
 * </code>
 *
 * @property-read Config\Configurator $configurator
 *
 * @author Petr Prochazka
 */
class Main extends Object
{

	/** @var Config\Configurator */
	private $configurator;

	/**
	 * @param Loaders\IPHPUnitLoader|NULL|string|false
	 * 	null = autodetect Loaders\IncludePathLoader
	 * 	string = directory Loaders\IncludePathLoader
	 * 	false = disable include path modification
	 */
	public function __construct($loader = NULL)
	{
		$this->configurator = new Config\Configurator($loader);
	}

	/**
	 * Run HttpPHPUnit
	 * @see Runner\Applicatin::run()
	 * @param string Directory where are PHPUnit_Framework_TestCase classes.
	 * @param string Add PHPUnit paramaters like in command-line. {@see Config\Configuration::addArguments()}
	 */
	public function run($testDirectory, $argument = '--no-globals-backup --strict')
	{
		if ($argument) $this->configurator->getConfiguration()->addArgument($argument);
		$this->configurator->getConfiguration()->setTestDirectory($testDirectory);
		$this->configurator->createApplication()->run();
	}

	/** @return Config\Configurator */
	public function getConfigurator()
	{
		return $this->configurator;
	}

	/**
	 * Enable code coverage report via PHP_CodeCoverage.
	 * @see Modules\Coverage\Coverage
	 * @param string application directory
	 * @param string coverage report directory
	 * @param callback (PHP_CodeCoverage $coverage, string $coverageDir, string $appDir)
	 * @return PHP_CodeCoverage Modules\Coverage\LazyObject back compatibility
	 */
	public function coverage($appDir, $coverageDir, $setupCoverage = NULL)
	{
		$coverage = new Modules\Coverage\Coverage($appDir, $coverageDir, $setupCoverage);
		$this->configurator->registerModule('coverage', $coverage);
		return $coverage->createLazyCoverageObject();
	}

	/**
	 * Add PHPUnit paramaters like in command-line.
	 * <code>
	 * 	$http->arg('--no-globals-backup');
	 * </code>
	 * Don't forget sanitize variables via {@see escapeshellarg()}.
	 * @param string
	 * @return Main $this
	 */
	public function arg($argument)
	{
		$this->configurator->getConfiguration()->addArgument($argument);
		return $this;
	}

	/**
	 * @deprecated
	 * Enable/disable debugging.
	 * @see Config\Configuration::setDebug()
	 * If enabled after test fails it call 'Nette\Diagnostics\Debugger' and render error (and stop other tests).
	 * Usualy is enabled if is runned one test.
	 * @param bool
	 * @return Main $this
	 */
	public function setDebug()
	{
		$this->configurator->getConfiguration()->setDebug($debug);
		return $this;
	}

	/**
	 * @deprecated
	 * Is enabled debugging?
	 * @see Config\Configuration::isDebug()
	 * If enabled after test fails it call 'Nette\Diagnostics\Debugger' and render error (and stop other tests).
	 * Usualy is enabled if is runned one test.
	 * @return bool
	 */
	public function isDebug()
	{
		return $this->configurator->getConfiguration()->isDebug();
	}

}
