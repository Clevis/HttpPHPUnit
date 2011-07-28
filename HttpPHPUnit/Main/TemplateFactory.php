<?php

namespace HttpPHPUnit;

use Nette\Application\UI\Control;
use Nette\Utils\Strings as String;
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
		$dir = realpath(__DIR__ . '/..');
		$documentRoot = realpath($_SERVER['DOCUMENT_ROOT']);
		if (!$documentRoot) throw new Exception;
		if ($documentRoot != $dir AND !String::startsWith($dir, $documentRoot . DIRECTORY_SEPARATOR)) throw new Exception;
		return str_replace('\\', '/', substr($dir, strlen($documentRoot)));
	}

}
