<?php

/**
 * @author Petr Prochazka
 */
class HttpPHPUnit_TextUI_Command extends PHPUnit_TextUI_Command
{
	public function run(array $argv, $printer = NULL)
	{
		$this->arguments['printer'] = $printer;
		parent::run($argv, false);
	}
}
