<?php

namespace HttpPHPUnit\Config;

use HttpPHPUnit\Nette\Object;
use HttpPHPUnit\Config;


/**
 * Contains method which performs queries over current configuration.
 * It is intended mostly for modules.
 * @author Petr Prochazka
 */
class Information extends Object
{

	/** @var Config\Configuration */
	private $configuration;

	public function __construct(Config\Configuration $configuration)
	{
		$this->configuration = $configuration;
	}

	/** @return bool */
	public function isRunnedAllTest()
	{
		if ($this->configuration->isRunned() AND !$this->configuration->getFilterDirectory())
		{
			return true;
		}
		return false;
	}

	/** @return bool */
	public function isFiltered()
	{
		if ($this->configuration->getFilterDirectory() OR $this->configuration->getFilterMethod())
		{
			return true;
		}
		return false;
	}

}
