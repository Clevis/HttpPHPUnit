<?php

namespace HttpPHPUnit;

use Nette\Application\UI\Control;
use Exception;

/**
 * @author Petr Prochazka
 */
class TemplateFactory extends Control
{

	public function templatePrepareFilters($template)
	{
		$template->registerFilter(new \Nette\Latte\Engine);
	}

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
		$documentRoot = rtrim($documentRoot, DIRECTORY_SEPARATOR);
		$tmp = $documentRoot . DIRECTORY_SEPARATOR;
		if ($documentRoot != $dir AND strncmp($dir, $tmp, strlen($tmp)) !== 0) throw new Exception;
		return str_replace('\\', '/', substr($dir, strlen($documentRoot)));
	}

}
