<?php

namespace HttpPHPUnit\Events;

use HttpPHPUnit\Nette\Object;


/**
 * @method ModuleEvents onStart(callable)
 * @method ModuleEvents onRendererStart(callable)
 * @method ModuleEvents onRendererStructureStart(callable)
 * @method ModuleEvents onRendererStructureEnd(callable)
 * @method ModuleEvents onRendererRunnerStart(callable)
 * @method ModuleEvents onRunnerStart(callable)
 * @method ModuleEvents onRendererPrinterSuccess(callable (PHPUnit_Framework_Test $test))
 * @method ModuleEvents onRendererPrinterError(callable (PHPUnit_Framework_Test $test, Exception $e, Renderer\ResultPrinter::FAILURE|ERROR $state))
 * @method ModuleEvents onRendererPrinterInfo(callable (PHPUnit_Framework_Test $test, Exception $e, Renderer\ResultPrinter::INCOMPLETE|SKIPPED $state))
 * @method ModuleEvents onRunnerEnd(callable)
 * @method ModuleEvents onRendererRunnerEnd(callable)
 * @method ModuleEvents onRendererWaitingStart(callable)
 * @method ModuleEvents onRendererWaitingEnd(callable)
 * @method ModuleEvents onRendererEnd(callable)
 * @method ModuleEvents onEnd(callable)
 *
 * @see Events
 * @author Petr Procházka
 */
class ModuleEvents extends Object
{

	/** @var Events */
	private $events;

	/** @var string */
	private $moduleName;

	/**
	 * @param Events
	 * @param string
	 */
	public function __construct(Events $events, $moduleName)
	{
		$this->events = $events;
		$this->moduleName = $moduleName;
	}

	/**
	 * @param string
	 * @param array
	 * @return ModuleEvents $this
	 */
	public function __call($name, $arguments)
	{
		$this->events->registerListener($name, $this->moduleName, $arguments[0], isset($arguments[1]) ? $arguments[1] : NULL);
		return $this;
	}

}
