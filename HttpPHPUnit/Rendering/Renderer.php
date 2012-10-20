<?php

namespace HttpPHPUnit\Rendering;

use HttpPHPUnit\Nette\Object;
use HttpPHPUnit\Nette\Utils\Html;
use HttpPHPUnit\Config;
use HttpPHPUnit\Events;
use HttpPHPUnit\Runner;
use PHPUnit_TextUI_TestRunner;


/**
 * @author Petr Prochazka
 */
class Renderer extends Object
{

	/** @var Config\Configuration */
	private $configuration;

	/** @var Events\Events */
	private $events;

	/** @var Runner\Runner */
	private $runner;

	/** @var Config\Link */
	private $link;

	public function __construct(Config\Configuration $configuration, Events\Events $events, Runner\Runner $runner, Config\Link $link)
	{
		$this->configuration = $configuration;
		$this->events = $events;
		$this->link = $link;
		$this->runner = $runner;
	}

	/**
	 * Render layout.
	 */
	public function render()
	{
		$template = TemplateFactory::create(__DIR__ . '/layout.latte');
		$template->renderer = $this;
		$template->filterDirectory = $this->configuration->getFilterDirectory();
		$template->filterMethod = $this->configuration->getFilterMethod();

		$this->events->triggerListener('onRendererStart');
		$template->render();
		$this->events->triggerListener('onRendererEnd');
	}

	/**
	 * Prints PHPUnit version and author text.
	 * @see PHPUnit_TextUI_TestRunner::printVersionString()
	 */
	public function printPhpUnitVersion()
	{
		PHPUnit_TextUI_TestRunner::printVersionString();
	}

	/**
	 * Display structure
	 * @see StructureRenderer
	 */
	public function renderStructure()
	{
		$structure = new StructureRenderer($this->configuration, $this->link);
		$this->events->triggerListener('onRendererStructureStart');
		$structure->render();
		$this->events->triggerListener('onRendererStructureEnd');
	}

	/**
	 * Display wating text, if tests is nod runned.
	 * @see Config\Configuration::isRunned()
	 */
	public function renderWaiting()
	{
		if (!$this->configuration->isRunned())
		{
			$this->events->triggerListener('onRendererWaitingStart');
			$uri = $this->link->createLink($this->configuration, array(
				'setRunned' => true,
			));
			echo Html::el('h2')->add(Html::el('a', 'START')->href($uri));
			echo '<p style="display: none;" id="sentence" data-state="waiting">Waiting for start</p>';
			$this->events->triggerListener('onRendererWaitingEnd');
		}
	}

	/**
	 * Run and display PHPUnit runner.
	 * @see Runner\Runner::run();
	 * @see ResultPrinter;
	 */
	public function renderRunner()
	{
		if ($this->configuration->isRunned())
		{
			$this->events->triggerListener('onRendererRunnerStart');
			echo '<pre>';

			$printer = new ResultPrinter($this->configuration, $this->events, $this->link);
			while (@ob_end_flush()); flush();
			$this->runner->run($printer);
			$printer->render();

			echo '</pre>';
			$this->events->triggerListener('onRendererRunnerEnd');
		}
	}

}
