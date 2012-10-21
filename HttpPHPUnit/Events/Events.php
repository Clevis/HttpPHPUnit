<?php

namespace HttpPHPUnit\Events;

use HttpPHPUnit\Nette\Object;


/**
 *
 * Setup.
 * <code>
 * 	$e = new Events;
 * 	$events->getAutowiring()
 * 		->addObject(new Setup\Configuration)
 * 		->addObject(new Setup\Link)
 *	;
 * </code>
 *
 * Module usage.
 * <code>
 * 	$em = $e->createModuleEvents('moduleName');
 *
 * 	$em->onStart(function () use ($em) {
 * 		// do samething
 * 		// You can register enother listeners.
 * 		$em->onRunnerStart(function () { });
 * 	});
 *
 * </code>
 *
 * Autowiring.
 * <code>
 * 	$em->onStart(function (Setup\Configuration $config, Setup\Link $link) {});
 * </code>
 *
 * Parameters and autowiring.
 * <code>
 * 	$em->onRendererPrinterSuccess(function (PHPUnit_Framework_Test $test, Setup\Configuration $config, Setup\Link $link) {});
 * </code>
 *
 * If listeners has name, they can be ovewriten.
 * <code>
 * 	$em = $e->createModuleEvents('moduleName');
 *
 * 	$em->onStart(function () {
 * 		// not called
 * 	}, 'listenerName');
 *
 * 	$em->onStart(function () {
 * 		// called
 * 	}, 'listenerName');
 *
 * 	// First listener never will be called.
 * 	// But if has no name it will not be overwritten.
 *
 * 	$em->onStart(function () {
 * 		// called
 * 	});
 *
 * 	$em->onStart(function () {
 * 		// called
 * 	});
 * </code>
 *
 * @author Petr Procházka
 */
class Events extends Object
{

	/** Event can be triggered only once. */
	const ONCE = 1;

	/**
	 * @var array eventName => object(
	 * 	name => string
	 * 	setup => int // self::ONCE
	 * 	numberOfParameters => int
	 * 	autowireAllowedClasses => array {@see Autowiring::convertAllowedClassesArray()}
	 * )
	 */
	private $types = array();

	/**
	 * @var array eventName => array of object(
	 * 	moduleName => string
	 * 	listenerName => string|NULL
	 * 	callback => callable
	 * 	autowired => bool // is callback autowired?
	 * )
	 */
	private $listeners = array();

	/** @var array eventName => moduleName => listenerName => int */
	private $find = array();

	/** @var Autowiring */
	private $autowiring;

	public function __construct()
	{
		$always = array(
			'HttpPHPUnit\Config\Information',
			'HttpPHPUnit\Config\Configuration',
			'HttpPHPUnit\Config\Link',
			'HttpPHPUnit\Loaders\IPHPUnitLoader',
		);
		$this->autowiring = new Autowiring;

		$this->addType('onStart', self::ONCE, $always);
			$this->addType('onRendererStart', self::ONCE, $always);

				$this->addType('onRendererStructureStart', self::ONCE, $always);
				$this->addType('onRendererStructureEnd', self::ONCE, $always);

				$this->addType('onRendererRunnerStart', self::ONCE, $always);
					$this->addType('onRunnerStart', self::ONCE, $always);

						$this->addType('onRendererPrinterSuccess', 0, $always, 'PHPUnit_Framework_Test $test');
						$this->addType('onRendererPrinterError', 0, $always, 'PHPUnit_Framework_Test $test, Exception $e, Renderer\ResultPrinter::FAILURE|ERROR $state');
						$this->addType('onRendererPrinterInfo', 0, $always, 'PHPUnit_Framework_Test $test, Exception $e, Renderer\ResultPrinter::INCOMPLETE|SKIPPED $state');

					$this->addType('onRunnerEnd', self::ONCE, $always);
				$this->addType('onRendererRunnerEnd', self::ONCE, $always);

				$this->addType('onRendererWaitingStart', self::ONCE, $always);
				$this->addType('onRendererWaitingEnd', self::ONCE, $always);

			$this->addType('onRendererEnd', self::ONCE, $always);
		$this->addType('onEnd', self::ONCE, $always);

	}

	/**
	 * @param string
	 * @param string
	 * @param callable
	 * @param string|NULL
	 * @return Events $this
	 */
	public function registerListener($eventName, $moduleName, $callback, $listenerName = NULL)
	{
		if (!isset($this->listeners[$eventName]))
		{
			throw new \Exception("Event '{$eventName}' is not exists.");
		}
		if ($this->listeners[$eventName] === false)
		{
			throw new \Exception("Event '{$eventName}' was already triggered.");
		}
		if ($listenerName !== NULL)
		{
			$exists = & $this->find[$eventName][$moduleName][$listenerName];
			if ($exists !== NULL)
			{
				$this->listeners[$eventName][$exists] = NULL;
				$exists = NULL;
			}
		}
		$this->find[$eventName][$moduleName][$listenerName] = count($this->listeners[$eventName]);
		$this->listeners[$eventName][] = (object) array(
			'moduleName' => $moduleName,
			'listenerName' => $listenerName,
			'callback' => $callback,
			'autowired' => false,
		);
		return $this;
	}

	/**
	 * @param string
	 * @param array
	 */
	public function triggerListener($eventName, array $parameters = array())
	{
		if (!isset($this->listeners[$eventName]))
		{
			throw new \Exception("Event '{$eventName}' is not exists.");
		}
		if ($this->listeners[$eventName] === false)
		{
			throw new \Exception("Event '{$eventName}' was triggered twice. Only once is allowed.");
		}
		$listeners = $this->listeners[$eventName];
		if ($this->types[$eventName]->setup & self::ONCE)
		{
			$this->listeners[$eventName] = false;
		}
		if (($a = $this->types[$eventName]->numberOfParameters) !== ($b = count($parameters)))
		{
			throw new \Exception("Event '{$eventName}' expect {$a} parameters; {$b} given.");
		}

		foreach ($listeners as $event)
		{
			if ($event === NULL)
			{
				continue;
			}
			if ($event->autowired === false)
			{
				$event->callback = $this->autowiring->autowireFunction($event->callback, $this->types[$eventName]->numberOfParameters, $this->types[$eventName]->autowireAllowedClasses);
				$event->autowired = true;
			}
			call_user_func_array($event->callback, $parameters);
		}
	}

	/** @return Autowiring */
	public function getAutowiring()
	{
		return $this->autowiring;
	}

	/**
	 * @param string
	 * @return ModuleEvents
	 */
	public function createModuleEvents($moduleName)
	{
		return new ModuleEvents($this, $moduleName);
	}

	public function __clone()
	{
		$this->autowiring = clone $this->autowiring;
	}

	/**
	 * @param string
	 * @param int {@see self::ONCE}
	 * @param array of class or interface name
	 * @param string not autowired parameters
	 * @return Events $this
	 */
	protected function addType($name, $setup, array $autowireAllowedClasses, $parameters = '')
	{
		if (isset($this->types[$name]))
		{
			throw new \Exception($name);
		}
		$numberOfParameters = 0;
		if (trim($parameters))
		{
			$numberOfParameters = count(explode(',', $parameters));
		}
		$this->listeners[$name] = array();
		$this->types[$name] = (object) array(
			'name' => $name,
			'setup' => $setup,
			'numberOfParameters' => $numberOfParameters,
			'autowireAllowedClasses' => Autowiring::convertAllowedClassesArray($autowireAllowedClasses),
		);
	}

}
