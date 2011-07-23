<?php

use Nette\ObjectMixin;

abstract class TestCase extends PHPUnit_Framework_TestCase
{

	public function __call($name, $args)
	{
		return ObjectMixin::call($this, $name, $args);
	}

	public static function __callStatic($name, $args)
	{
		return ObjectMixin::callStatic(get_called_class(), $name, $args);
	}

	public function &__get($name)
	{
		return ObjectMixin::get($this, $name);
	}

	public function __set($name, $value)
	{
		return ObjectMixin::set($this, $name, $value);
	}

	public function __isset($name)
	{
		return ObjectMixin::has($this, $name);
	}

	public function __unset($name)
	{
		ObjectMixin::remove($this, $name);
	}

}
