<?php

namespace HttpPHPUnit\Runner;

use HttpPHPUnit\Nette\Object;
use HttpPHPUnit\Rendering;
use HttpPHPUnit\Config;
use HttpPHPUnit\Events;


/**
 * @author Petr Prochazka
 */
class Application extends Object
{

	/** @var Runner */
	private $runner;

	/** @var Rendering\Renderer */
	private $renderer;

	/** @var Config\Configuration */
	private $configuration;

	/** @var Events\Events */
	private $events;

	public function __construct(Runner $runner, Rendering\Renderer $renderer, Config\Configuration $configuration, Events\Events $events)
	{
		$this->runner = $runner;
		$this->renderer = $renderer;
		$this->configuration = $configuration;
		$this->events = $events;
	}

	/** Run HttpPHPUnit */
	public function run()
	{
		$this->events->triggerListener('onStart');
		$this->renderer->render();
		$this->events->triggerListener('onEnd');
	}

}
