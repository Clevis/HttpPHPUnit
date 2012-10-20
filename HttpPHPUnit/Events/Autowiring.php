<?php

namespace HttpPHPUnit\Events;

use HttpPHPUnit\Nette\Object;
use ReflectionFunctionAbstract;
use ReflectionParameter;


/**
 * Setup objects.
 * <code>
 * 	$a = new Autowiring;
 * 	$a->addObject(new Foo);
 * 	$a->addObject(new Bar);
 * </code>
 *
 * Basic function autowiring.
 * <code>
 * 	$function = function (Foo $foo, $bar) {};
 *
 * 	$autowiredFunction = $a->autowireFunction($function);
 *
 * 	$autowiredFunction(); // $function will be called and parameters $foo and $bar will be included.
 * </code>
 *
 * First X parameters can be ignored.
 * <code>
 * 	$function = function ($aaa, $bbb, Foo $foo, $bar) {};
 *
 * 	$autowiredFunction = $a->autowireFunction($function, 2);
 *
 * 	$autowiredFunction('aaa', 'bbb');
 * </code>
 *
 * Restricted classes which can be autowired.
 * <code>
 * 	$function = function (Foo $foo) {};
 *
 * 	$allowedClasses = Autowiring::convertAllowedClassesArray(array('Foo'));
 * 	$autowiredFunction = $a->autowireFunction($function, 0, $allowedClasses);
 *
 * 	$autowiredFunction();
 * </code>
 *
 * AutowiringFinder can be autowired always.
 * <code>
 * 	$function = function (AutowiringFinder $finder) {
 * 		$foo = $finder->getByClass('Foo');
 * 		$bar = $finder->getByClass('Bar');
 * 	};
 *
 * 	$autowiredFunction = $a->autowireFunction($function);
 *
 * 	$autowiredFunction();
 * </code>
 *
 * @author Petr Procházka
 */
class Autowiring extends Object
{

	/** @var object[] */
	private $objects = array();

	/** @var array className => object */
	private $cache = array();

	/**
	 * Added object to autowiring.
	 * @param object
	 * @return Autowiring $this
	 */
	public function addObject($object)
	{
		$this->objects[] = $object;
		$this->cache = array();
		return $this;
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
		if (!isset($this->cache[$class]))
		{
			$posible = array();
			foreach ($this->objects as $object)
			{
				if ($object instanceof $class)
				{
					$posible[] = $object;
				}
			}
			if (count($posible) > 1)
			{
				throw new AutowiringException("Autowired class {$class} has more then one object registered.");
			}
			$this->cache[$class] = current($posible);
		}
		if ($this->cache[$class] === false)
		{
			if ($need)
			{
				throw new AutowiringException("Autowired object of type {$class} not found.");
			}
			return NULL;
		}
		return $this->cache[$class];
	}

	/**
	 * Create finder with restricted classes which can be autowired.
	 * @param array|NULL Restricted classes which can be autowired. {@see self::convertAllowedClassesArray()}
	 * 	Null mean no restriction.
	 * 	Array must be converted via {@see self::convertAllowedClassesArray()}.
	 * @return AutowiringFinder
	 * @see self::convertAllowedClassesArray()
	 */
	public function createFinder(array $allowedClasses = NULL)
	{
		return new AutowiringFinder($this, $allowedClasses);
	}

	/**
	 * Autowire function.
	 * @param callable
	 * @param int First X parameters will not be autowired. Parameters will be expected as parameters of returned function.
	 * @param array|NULL Restricted classes which can be autowired. {@see self::convertAllowedClassesArray()}
	 * 	Null mean no restriction.
	 * 	Array must be converted via {@see self::convertAllowedClassesArray()}.
	 * @return callable
	 */
	public function autowireFunction($callback, $numberOfNonAutowiredParameters = 0, array $allowedClasses = NULL)
	{
		$r = $this->callbackToReflection($callback);

		$results = array();
		static $finderClass;
		if ($finderClass === NULL)
		{
			$finderClass = strtolower('HttpPHPUnit\Events\AutowiringFinder');
		}
		foreach ($r->getParameters() as $num => $parameter)
		{
			if ($numberOfNonAutowiredParameters > $num)
			{
				// func_get_arg($num)
			}
			else if ($class = $this->getParameterClassHint($parameter))
			{
				$lcClass = strtolower($class);
				if ($allowedClasses === NULL OR isset($allowedClasses[$lcClass]) OR $lcClass === $finderClass)
				{
					$results[$num] = $class;
				}
				else
				{
					throw AutowiringException::create("Autowired class {$class} is not allowed in parameter \${$parameter->getName()}.", $r->getFileName(), $r->getStartLine());
				}
			}
			else
			{
				throw AutowiringException::create("Extra parameter \${$parameter->getName()}.", $r->getFileName(), $r->getStartLine());
			}

		}

		$finder = $this->createFinder($allowedClasses);
		return function () use ($finder, $results, $callback, $numberOfNonAutowiredParameters) {

			$arguments = func_get_args();

			if ($numberOfNonAutowiredParameters !== ($b = count($arguments)))
			{
				throw new AutowiringException("Autowired function expect {$numberOfNonAutowiredParameters} parameters; {$b} given.");
			}

			foreach ($results as $num => $class)
			{
				$arguments[$num] = $finder->getByClass($class);
			}
			return call_user_func_array($callback, $arguments);
		};
	}

	/**
	 * @param string[] class or interface name
	 * @return array string => string lowercased class names.
	 */
	public static function convertAllowedClassesArray(array $allowedClasses)
	{
		$allowedClasses = array_map('strtolower', $allowedClasses);
		return $allowedClasses ? array_combine($allowedClasses, $allowedClasses) : array();
	}

	/**
	 * @param callable
	 * @return ReflectionFunctionAbstract
	 */
	protected function callbackToReflection($callback)
	{
		if (is_string($callback) AND strpos($callback, '::'))
		{
			return new \ReflectionMethod($callback);
		}
		else if (is_array($callback))
		{
			return new \ReflectionMethod($callback[0], $callback[1]);
		}
		else if (is_object($callback) AND !($callback instanceof \Closure))
		{
			if (method_exists($callback, 'getNative'))
			{
				return $this->callbackToReflection($callback->getNative()); // Nette\Callback support
			}
			return new \ReflectionMethod($callback, '__invoke');
		}
		else
		{
			return new \ReflectionFunction($callback);
		}
	}

	/**
	 * Return class name; even if class not exists.
	 * @param ReflectionParameter
	 * @return string|NULL
	 */
	protected function getParameterClassHint(ReflectionParameter $parameter)
	{
		try {
			return ($ref = $parameter->getClass()) ? $ref->getName() : NULL;
		} catch (\ReflectionException $e) {
			if (preg_match('#Class (.+) does not exist#', $e->getMessage(), $m)) {
				return $m[1];
			}
			throw $e;
		}
	}
}
