<?php

namespace HttpPHPUnit\Loaders;

use HttpPHPUnit\Nette\Object;
use Exception;

/**
 * Autoload (PSR-0) HttpPHPUnit classes by spl_autoload.
 *
 * <code>
 * 	require_once __DIR__ . '/Loaders/AutoLoader.php';
 * 	AutoLoader::getInstance()->register();
 * </code>
 *
 * @author Petr Prochazka
 * Based on Nette Framework (c) David Grudl (http://davidgrudl.com)
 */
class AutoLoader/* extends Object*/
{

	/** @var AutoLoader */
	private static $instance;

	/** @var array expectedPath => actualPath non PSR-0 */
	protected $list = array(
		'Main' => 'Runner/Main',
		'Nette' => 'Nette/nette.min',
	);

	/**
	 * Returns singleton instance with lazy instantiation.
	 * @return AutoLoader
	 */
	public static function getInstance()
	{
		if (self::$instance === NULL)
		{
			self::$instance = new static;
		}
		return self::$instance;
	}

	/**
	 * Register autoloader.
	 * @return void
	 */
	public function register()
	{
		if (!function_exists('spl_autoload_register'))
		{
			throw new Exception('spl_autoload does not exist in this PHP installation.');
		}

		spl_autoload_register(array($this, 'tryLoad'));
	}

	/**
	 * Unregister autoloader.
	 * @return bool
	 */
	public function unregister()
	{
		return spl_autoload_unregister(array($this, 'tryLoad'));
	}

	/**
	 * Handles autoloading of classes or interfaces.
	 * @param string
	 * @return void
	 */
	public function tryLoad($type)
	{
		$dir = __DIR__ . '/..';
		$type = ltrim($type, '\\');
		if (substr($type, 0, 12) === 'HttpPHPUnit\\')
		{
			$file = strtr(substr($type, 12), '\\', '/');
			if (substr($file, 0, 6) === 'Nette/')
			{
				$file = $this->list['Nette'];
			}
			else if (isset($this->list[$file]))
			{
				$file = $this->list[$file];
			}
			$include = $dir . '/' . $file . '.php';
			if (is_file($include))
			{
				require_once $include;
			}
		}

	}

}
