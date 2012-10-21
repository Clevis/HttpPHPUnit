<?php

namespace HttpPHPUnit\Modules\Coverage;

use HttpPHPUnit\Nette\Object;
use IteratorAggregate;
use Countable;
use ArrayAccess;


/**
 * Lazy object for back compatibility.
 * It's allows do some operation with object before even object exists.
 *
 * <code>
 * 	$object = new LazyObject;
 *
 * 	$o2 = $object->getSameInnerObject();
 * 	$o2->callMethodOnWhateverItReturns('supply some parameters');
 * 	$o2->property = 'value';
 * 	$array = $o2->getArray();
 * 	$array['key'] = 'value';
 *
 *  $object->__apply(new Object); // Performs all operations specified above.
 * </code>
 *
 * Or you can use method '__then' to register lazy callback.
 * <code>
 * 	$object = new LazyObject;
 * 	$object->__then(function (Object $object) {
 * 		// do something
 * 	});
 * 	$object->__apply(new Object);
 * </code>
 *
 * @author Petr Procházka
 */
class LazyObject extends Object implements IteratorAggregate, Countable, ArrayAccess
{

	/** @var array */
	private $actions = array();

	/** @var bool */
	private $destruct = false;

	/** @var bool */
	private $isCloneEnabled = false;

	/** @var string */
	private $errorMessage;

	/**
	 * @param string Part of error message.
	 */
	public function __construct($errorMessage = NULL)
	{
		$this->errorMessage = $errorMessage;
	}

	/**
	 * Register lazy callback. Will be executed when real object is available.
	 * @param callable (object $object)
	 * @return LazyObject $this
	 */
	public function __then($callback)
	{
		$this->add('then', NULL, $callback);
		return $this;
	}

	/**
	 * Performs all operations.
	 * @param object
	 */
	public function __apply($cursor)
	{
		while ($action = array_shift($this->actions))
		{
			if ($action->type === '__call')
			{
				$inner = call_user_func_array(array($cursor, $action->name), $action->argument);
			}
			else if ($action->type === '__get')
			{
				$inner = $cursor->{$action->name};
			}
			else if ($action->type === '__set')
			{
				$cursor->{$action->name} = $action->argument;
				$inner = $action->argument;
			}
			else if ($action->type === '__unset')
			{
				unset($cursor->{$action->name});
				$inner = NULL;
			}
			else if ($action->type === 'offsetGet')
			{
				$inner = $cursor[$action->name];
			}
			else if ($action->type === 'offsetSet')
			{
				$cursor[$action->name] = $action->argument;
				$inner = $action->argument;
			}
			else if ($action->type === 'offsetSet')
			{
				$cursor[$action->name] = $action->argument;
				$inner = $action->argument;
			}
			else if ($action->type === 'offsetUnset')
			{
				unset($cursor[$action->name]);
				$inner = NULL;
			}
			else if ($action->type === 'then')
			{
				call_user_func($action->argument, $cursor);
				$inner = NULL;
			}
			else
			{
				$this->fail($action->type);
			}
			$action->inner->__apply($inner);
		}
		$this->destruct = true;
	}

	/**
	 * @param string
	 * @param string
	 * @param mixed
	 * @return LazyObject cloned
	 */
	protected function add($type, $name, $argument = NULL)
	{
		if ($this->destruct)
		{
			$this->fail(NULL);
		}
		$this->isCloneEnabled = true;
		$inner = clone $this;
		$inner->actions = array();
		$inner->isCloneEnabled = false;
		$this->isCloneEnabled = false;
		$this->actions[] = (object) array(
			'type' => $type,
			'name' => $name,
			'argument' => $argument,
			'inner' => $inner,
		);
		return $inner;
	}

	/**
	 * @param string
	 * @throws \Exception
	 */
	protected function fail($type)
	{
		$message = 'You cannot use this lazy object as follows.';
		if ($type)
		{
			$type = ucfirst($type);
			$message .= " {$type} is not supported.";
		}
		if ($this->errorMessage)
		{
			$message .= " {$this->errorMessage}";
		}
		$message .= " Or you can use method '__then' to register lazy callback.";
		throw new \Exception($message);
	}

	public function & __get($name)
	{
		$tmp = $this->add(__FUNCTION__, $name);
		return $tmp;
	}

	public function __set($name, $value)
	{
		return $this->add(__FUNCTION__, $name, $value);
	}

	public function __call($name, $arguments)
	{
		return $this->add(__FUNCTION__, $name, $arguments);
	}

	public function __unset($name)
	{
		return $this->add(__FUNCTION__, $name);
	}

	public function offsetGet($name)
	{
		return $this->add(__FUNCTION__, $name);
	}

	public function offsetSet($name, $value)
	{
		return $this->add(__FUNCTION__, $name, $value);
	}

	public function offsetUnset($name)
	{
		return $this->add(__FUNCTION__, $name);
	}

	public function __clone()
	{
		if (!$this->isCloneEnabled)
		{
			$this->fail('clone');
		}
	}

	public function __isset($name)
	{
		$this->fail('isset');
	}

	public function offsetExists($name)
	{
		$this->fail('isset');
	}

	public function __toString()
	{
		$this->fail('__toString');
	}

	public function getIterator()
	{
		$this->fail('iteration');
	}

	public function count()
	{
		$this->fail('count');
	}

	public function __sleep()
	{
		$this->fail('serialization');
	}

}
