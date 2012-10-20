<?php

namespace HttpPHPUnit\Runner;

use HttpPHPUnit\Nette\Object;
use HttpPHPUnit\Config;
use HttpPHPUnit\Events;
use HttpPHPUnit\Rendering;


/**
 * Runs PHPUnit.
 *
 * @author Petr Prochazka
 */
class Runner extends Object
{

	/** @var Config\Configuration */
	private $configuration;

	/** @var Events\Events */
	private $events;

	public function __construct(Config\Configuration $configuration, Events\Events $events)
	{
		$this->configuration = $configuration;
		$this->events = $events;
	}

	/**
	 * @param Rendering\ResultPrinter
	 * @see Command
	 */
	public function run(Rendering\ResultPrinter $printer)
	{
		$argumens = $this->configuration->getArguments();
		$command = new Command;
		$command->run($argumens, $printer);
	}

}
