<?php

namespace HttpPHPUnit\Events;

use Exception;

/**
 * @see Autowiring
 * @author Petr Procházka
 */
class AutowiringException extends Exception
{

	/**
	 * Some errors during autowiring can be difficult to see what function is autowired.
	 * This method modify exception to show where is function defined.
	 * <code>
	 * 	$r instanceof ReflectionFunctionAbstract
	 * 	throw AutowiringException::create('message', $r->getFileName(), $r->getStartLine());
	 * </code>
	 * @param string
	 * @param string
	 * @param int
	 * @param int
	 * @return AutowiringException
	 */
	public static function create($message, $file, $line, $code = NULL)
	{
		$e = new static($message, $code);
		$e->file = $file;
		$e->line = $line;
		return $e;
	}

}
