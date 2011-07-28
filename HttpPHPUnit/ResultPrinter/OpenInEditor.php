<?php

namespace HttpPHPUnit;

use Nette\Object;
use Nette\Diagnostics\Debugger as Debug;

/**
 * @author Petr Prochazka
 */
class OpenInEditor extends Object
{

	/** @var string URL pattern mask to open editor; NULL mean use Nette Debug */
	static public $editor = NULL;

	public function __construct()
	{
		if (self::$editor === NULL AND isset(Debug::$editor))
		{
			self::$editor = Debug::$editor;
		}
	}

	/**
	 * @param string
	 * @param int
	 * @return string|NULL
	 */
	public function link($file, $line)
	{
		if (self::$editor AND is_file($file))
		{
			return strtr(self::$editor, array('%file' => rawurlencode($file), '%line' => (int) $line));
		}
	}
}
