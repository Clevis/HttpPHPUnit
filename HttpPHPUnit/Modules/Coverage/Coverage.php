<?php

namespace HttpPHPUnit\Modules\Coverage;

use HttpPHPUnit\Nette\Object;
use HttpPHPUnit\Nette\Utils\Html;
use HttpPHPUnit\Nette\Utils\Finder;
use HttpPHPUnit\Modules;
use HttpPHPUnit\Events;
use HttpPHPUnit\Config;
use HttpPHPUnit\Loaders;
use PHP_CodeCoverage;


/**
 * Enable code coverage report via PHP_CodeCoverage.
 *
 * @author Petr Procházka
 */
class Coverage extends Object implements Modules\IModule
{

	/** @var application directory */
	private $appDir;

	/** @var coverage report directory */
	private $coverageDir;

	/** @var callable (PHP_CodeCoverage $coverage, string $coverageDir, string $appDir) */
	private $setupCoverage;

	/** @var LazyObject|NULL Lazy PHP_CodeCoverage for back compatibility. */
	private $lazyCoverageObject;

	/**
	 * @param string application directory
	 * @param string coverage report directory
	 * @param callback (PHP_CodeCoverage $coverage, string $coverageDir, string $appDir)
	 */
	public function __construct($appDir, $coverageDir, $setupCoverage = NULL)
	{
		$this->appDir = $appDir;
		$this->coverageDir = $coverageDir;
		$this->setupCoverage = $setupCoverage;
	}

	/**
	 * Register coverage events.
	 * @param Events\ModuleEvents
	 */
	public function register(Events\ModuleEvents $events)
	{
		$_this = $this;
		$coverageDir = $this->coverageDir;
		$events->onStart(function (Config\Information $info, Config\Configuration $configuration, Loaders\IPHPUnitLoader $loader) use ($events, $_this, $coverageDir) {

			if (!$info->isFiltered() AND extension_loaded('xdebug'))
			{
				$events->onRendererWaitingEnd(function (Config\Link $link) use ($_this, $coverageDir) {
					$_this->renderCoverageLink($link, $coverageDir);
				});
				$events->onRendererRunnerEnd(function (Config\Link $link) use ($_this, $coverageDir) {
					$_this->renderCoverageLink($link, $coverageDir);
				});
			}

			if ($info->isRunnedAllTest())
			{
				if (!extension_loaded('xdebug'))
				{
					$events->onRendererRunnerEnd(function () {
						echo 'Coverage: The Xdebug extension is not loaded.';
					});
				}
				else
				{
					$coverage = $_this->createPHPUnitCoverage($loader);
					$configuration->addArgument('--coverage-html ' . $coverageDir);

					$lastModify = array();
					foreach (Finder::findFiles('*.html')->from($coverageDir) as $file)
					{
						$file = (string) $file;
						$lastModify[$file] = filemtime($file);
					}
					$events->onRendererRunnerEnd(function (Config\Link $link) use ($coverageDir, & $lastModify) {
						foreach (Finder::findFiles('*.html')->from($coverageDir) as $file)
						{
							$file = (string) $file;
							if (isset($lastModify[$file]) AND $lastModify[$file] === filemtime($file))
							{
								unlink($file);
							}
						}
					});
				}
			}

		});
	}

	/**
	 * @param Config\Link
	 * @param string
	 * @access protected
	 */
	public function renderCoverageLink(Config\Link $link, $coverageDir)
	{
		$d = $link->getLinkToFile($coverageDir);
		echo Html::el('a', 'Open coverage report.')->href($d);
	}

	/**
	 * @param Loaders\IPHPUnitLoader
	 * @return PHP_CodeCoverage
	 * @access protected
	 */
	public function createPHPUnitCoverage(Loaders\IPHPUnitLoader $loader)
	{
		$loader->load('PHP/CodeCoverage.php');
		if (method_exists('PHP_CodeCoverage', 'getInstance'))
		{
			$coverage = PHP_CodeCoverage::getInstance();
		}
		else
		{
			$coverage = new PHP_CodeCoverage;
		}

		@mkdir($this->coverageDir);
		if (!is_writable($this->coverageDir))
		{
			throw new \Exception("Report directory does not exist or is not writable {$this->coverageDir}");
		}
		if (!is_dir($this->appDir))
		{
			throw new \Exception($this->appDir);
		}

		$appDir = realpath($this->appDir);
		$coverage->filter()->addDirectoryToWhitelist($appDir);

		if ($this->lazyCoverageObject)
		{
			// back compatibility
			$this->lazyCoverageObject->__apply($coverage);
			$this->lazyCoverageObject = NULL;
		}
		if ($this->setupCoverage)
		{
			call_user_func($this->setupCoverage, $coverage, $this->coverageDir, $appDir);
		}

		return $coverage;
	}

	/**
	 * Lazy PHP_CodeCoverage for back compatibility.
	 * @return PHP_CodeCoverage LazyObject
	 */
	public function createLazyCoverageObject()
	{
		$this->lazyCoverageObject = new LazyObject('PHP_CodeCoverage is not available yet. Use third parameter in constructor to set coverage setup callback.');
		return $this->lazyCoverageObject;
	}

}
