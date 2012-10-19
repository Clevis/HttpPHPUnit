<?php

namespace HttpPHPUnit\Runner;

use PHPUnit_TextUI_Command;
use HttpPHPUnit\Rendering;
use HttpPHPUnit\Config;


/**
 * PHPUnit TestRunner for HttpPHPUnit.
 *
 * @author Petr Prochazka
 */
class Command extends PHPUnit_TextUI_Command
{

	/**
	 * @param array {@see Config\Configuration::getArguments()}
	 * @param Rendering\ResultPrinter
	 */
	public function run(array $argv, $printer = NULL)
	{
		$this->arguments['printer'] = $printer;
		parent::run($argv, false);
	}

}
