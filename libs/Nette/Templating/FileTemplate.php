<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Templating;

use Nette,
	Nette\Caching;



/**
 * Template stored in file.
 *
 * @author     David Grudl
 */
class FileTemplate extends Template implements IFileTemplate
{
	/** @var Nette\Caching\IStorage */
	private $cacheStorage;

	/** @var string */
	private $file;



	/**
	 * Constructor.
	 * @param  string  template file path
	 */
	public function __construct($file = NULL)
	{
		if ($file !== NULL) {
			$this->setFile($file);
		}
	}



	/**
	 * Sets the path to the template file.
	 * @param  string  template file path
	 * @return FileTemplate  provides a fluent interface
	 */
	public function setFile($file)
	{
		$this->file = realpath($file);
		if (!$this->file) {
			throw new Nette\FileNotFoundException("Missing template file '$file'.");
		}
		return $this;
	}



	/**
	 * Returns the path to the template file.
	 * @return string  template file path
	 */
	public function getFile()
	{
		return $this->file;
	}



	/********************* rendering ****************d*g**/



	/**
	 * Renders template to output.
	 * @return void
	 */
	public function render()
	{
		if ($this->file == NULL) { // intentionally ==
			throw new Nette\InvalidStateException("Template file name was not specified.");
		}

		$this->__set('template', $this);

		$cache = new Caching\Cache($storage = $this->getCacheStorage(), 'Nette.FileTemplate');
		if ($storage instanceof Caching\Storages\PhpFileStorage) {
			$storage->hint = str_replace(dirname(dirname($this->file)), '', $this->file);
		}
		$cached = $content = $cache->load($this->file);

		if ($content === NULL) {
			try {
				$content = $this->compile(file_get_contents($this->file));
				$content = "<?php\n\n// source file: $this->file\n\n?>$content";

			} catch (FilterException $e) {
				$e->setSourceFile($this->file);
				throw $e;
			}

			$cache->save(
				$this->file,
				$content,
				array(
					Caching\Cache::FILES => $this->file,
					Caching\Cache::CONSTS => 'Nette\Framework::REVISION',
				)
			);
			$cache->release();
			$cached = $cache->load($this->file);
		}

		if ($cached !== NULL && $storage instanceof Caching\Storages\PhpFileStorage) {
			Nette\Utils\LimitedScope::load($cached['file'], $this->getParams());
			flock($cached['handle'], LOCK_UN);
			fclose($cached['handle']);

		} else {
			Nette\Utils\LimitedScope::evaluate($content, $this->getParams());
		}
	}



	/********************* caching ****************d*g**/



	/**
	 * Set cache storage.
	 * @param  Nette\Caching\Cache
	 * @return void
	 */
	public function setCacheStorage(Caching\IStorage $storage)
	{
		$this->cacheStorage = $storage;
	}



	/**
	 * @return Nette\Caching\IStorage
	 */
	public function getCacheStorage()
	{
		if ($this->cacheStorage === NULL) {
			return new Caching\Storages\DevNullStorage;
		}
		return $this->cacheStorage;
	}

}
