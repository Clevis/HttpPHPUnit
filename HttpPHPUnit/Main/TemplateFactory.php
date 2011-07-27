<?php

use Nette\Application\UI\Control;

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
		return $template;
	}
}
