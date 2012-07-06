<?php

namespace HttpPHPUnit;

use Nette\Application\UI\Control;
use Exception;

/**
 * @author Petr Prochazka
 */
class TemplateFactory extends Control
{
	public static function create($file)
	{
		$control = new self;
		$template = $control->getTemplate();
		$template->control = NULL;
		$template->setFile($file);
		$template->basePath = self::getBasePath();
		return $template;
	}

	public static function getBasePath()
	{
		$dir = str_replace('\\', '/', realpath(__DIR__ . '/..')); // <== even Windows will have /
		$self = $_SERVER['PHP_SELF']; // e.g. /Ticketon/tests/index.php or /Clevis/Ticketon/tests/index.php
		$pathToHttpPHPUnitRoot = substr($self, 0, -9); // e.g. /Ticketon/tests/ or /Clevis/Ticketon/tests/
		$positionOfHttpPHPUnitRoot = strrpos($dir, $pathToHttpPHPUnitRoot);

		return substr($dir, $positionOfHttpPHPUnitRoot); // e.g. /Ticketon/tests/libs/HttpPHPUnit or /Clevis/Ticketon/tests/libs/HttpPHPUnit
	}

}
