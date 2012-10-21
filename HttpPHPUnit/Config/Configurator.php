<?php

namespace HttpPHPUnit\Config;

use HttpPHPUnit\Nette\Object;
use HttpPHPUnit\Modules;
use HttpPHPUnit\Events;
use HttpPHPUnit\Config;
use HttpPHPUnit\Rendering;
use HttpPHPUnit\Runner;
use HttpPHPUnit\Loaders;


/**
 * Configure HttpPHPUnit application run.
 *
 * <code>
 * 	require_once __DIR__ . '/libs/HttpPHPUnit/init.php';
 *
 * 	$c = new HttpPHPUnit\Config\Configurator;
 * 	$c->configuration->addArgument('--no-globals-backup --strict');
 * 	$c->configuration->setTestDirectory(__DIR__ . '/cases');
 * 	$c->registerModule('coverage', new HttpPHPUnit\Modules\Coverage\Coverage(__DIR__ . '/../app', __DIR__ . '/coverage'));
 * 	$c->createApplication()->run();
 *
 * </code>
 *
 * @property-read Config\Configuration $configuration
 *
 * @author Petr Prochazka
 */
class Configurator extends Object
{

	/** @var Config\Configuration */
	private $configuration;

	/** @var Loaders\IPHPUnitLoader */
	private $loader;

	/** @var array moduleName => object */
	private $modules = array();

	/**
	 * @param Loaders\IPHPUnitLoader|NULL|string|false
	 * 	null = autodetect Loaders\IncludePathLoader
	 * 	string = directory Loaders\IncludePathLoader
	 * 	false = disable include path modification
	 */
	public function __construct($loader = NULL)
	{
		if (!($loader instanceof Loaders\IPHPUnitLoader))
		{
			$loader = new Loaders\IncludePathLoader($loader);
		}
		$loader->load('PHPUnit/Autoload.php');
		$this->loader = $loader;
		$this->configuration = $this->createConfiguration();
	}

	/**
	 * Register Modules\IModule.
	 * @param string
	 * @param Modules\IModule
	 * @return Configurator
	 */
	public function registerModule($name, Modules\IModule $module)
	{
		if (isset($this->modules[$name]))
		{
			throw new \Exception;
		}
		$this->modules[$name] = $module;
		return $this;
	}

	/** @return Config\Configuration */
	public function getConfiguration()
	{
		return $this->configuration;
	}

	/** @return Runner\Application Ready to run. */
	public function createApplication()
	{
		$configuration = clone $this->configuration;
		$events = $this->createEvents();
		foreach ($this->modules as $moduleName => $module)
		{
			$module->register($events->createModuleEvents($moduleName));
		}
		$link = $this->createLink();
		$link->applyConfiguration($configuration);
		$info = $this->createInformation($configuration);
		$runner = $this->createRunner($configuration, $events);
		$renderer = $this->createRenderer($configuration, $events, $runner, $link);

		$events->getAutowiring()
			->addObject($this->loader)
			->addObject($configuration)
			->addObject($link)
			->addObject($info)
			->addObject($renderer)
			->addObject($runner)
		;

		return new Runner\Application($runner, $renderer, $configuration, $events);
	}

	/**
	 * @param Config\Configuration
	 * @param Events\Events
	 * @return Runner\Runner
	 */
	protected function createRunner(Config\Configuration $configuration, Events\Events $events)
	{
		return new Runner\Runner($configuration, $events);
	}

	/**
	 * @param Config\Configuration
	 * @param Events\Events
	 * @param Runner\Runner
	 * @param Config\Link
	 * @return Rendering\Renderer
	 */
	protected function createRenderer(Config\Configuration $configuration, Events\Events $events, Runner\Runner $runner, Config\Link $link)
	{
		return new Rendering\Renderer($configuration, $events, $runner, $link);
	}

	/**
	 * @return Config\Configuration
	 */
	protected function createConfiguration()
	{
		return new Config\Configuration;
	}

	/**
	 * @return Events\Events
	 */
	protected function createEvents()
	{
		return new Events\Events;
	}

	/**
	 * @return Config\Link
	 */
	protected function createLink()
	{
		return new Config\Link($_GET, $_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_FILENAME']);
	}

	/**
	 * @return Config\Information
	 */
	protected function createInformation(Config\Configuration $configuration)
	{
		return new Config\Information($configuration);
	}

}
