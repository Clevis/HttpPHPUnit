<?php

namespace HttpPHPUnit\Events;

use HttpPHPUnit\Nette\Object;

/**
 * @see Autowiring
 * @author Petr Procházka
 */
class AutowiringFinder extends Object
{

	/** @var Autowiring */
	private $autowiring;

	/** @var array string => string lowercased class names.  */
	private $allowedClasses;

	/**
	 * @param Autowiring
	 * @param array|NULL Restricted classes which can be autowired. {@see Autowiring::convertAllowedClassesArray()}
	 * 	Null mean no restriction.
	 * 	Array must be converted via {@see Autowiring::convertAllowedClassesArray()}.
	 */
	public function __construct(Autowiring $autowiring, array $allowedClasses = NULL)
	{
		$this->autowiring = $autowiring;
		$this->allowedClasses = $allowedClasses;
	}

	/**
	 * Find object by class or interface.
	 * @param strng class or interface name
	 * @param bool false = throw exception if not found; true = return null id not found.
	 * @return object|NULL
	 * @throws AutowiringException
	 */
	public function getByClass($class, $need = true)
	{
		static $finderClass;
		if ($finderClass === NULL)
		{
			$finderClass = strtolower(__CLASS__);
		}
		$lcClass = strtolower($class);
		if ($lcClass === $finderClass)
		{
			return $this;
		}
		if ($this->allowedClasses !== NULL AND !isset($this->allowedClasses[$lcClass]))
		{
			if ($need)
			{
				throw new AutowiringException("Autowired class {$class} is not allowed.");
			}
			return NULL;
		}
		return $this->autowiring->getByClass($class, $need);
	}

}
