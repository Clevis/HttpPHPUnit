<?php

namespace HttpPHPUnit\Config;

use HttpPHPUnit\Nette\Object;
use HttpPHPUnit\Config;


/**
 * Url specific operations.
 *
 * Load configuration from url.
 * And generate url with configuration.
 *
 * @author Petr Prochazka
 */
class Link extends Object
{

	/** @var string $_GET */
	private $parameters;

	/** @var string $_SERVER['REQUEST_URI'] */
	private $requestUri;

	/** @var string $_SERVER['SCRIPT_FILENAME'] */
	private $scriptFilename;

	/**
	 * @param string $_GET
	 * @param string $_SERVER['REQUEST_URI']
	 * @param string $_SERVER['SCRIPT_FILENAME']
	 */
	public function __construct(array $parameters, $requestUri, $scriptFilename)
	{
		$this->parameters = $parameters;
		$this->requestUri = $requestUri;
		$this->scriptFilename = $scriptFilename;
	}

	/**
	 * Load configuration from url.
	 * @param Config\Configuration
	 */
	public function applyConfiguration(Config\Configuration $configuration)
	{
		$testDir = $this->getParameter('test');
		$method = NULL;
		$debug = false;
		if ($testDir AND $pos = strrpos($testDir, '::'))
		{
			$method = substr($testDir, $pos+2);
			$testDir = substr($testDir, 0, $pos);
			$debug = true;
		}
		$run = ($this->getParameter('run') !== NULL OR $testDir);

		$configuration->setRunned($run);
		$configuration->setFilter($testDir, $method);
		$configuration->setDebug($debug);
	}

	/**
	 * Create link with specific modification of configuration.
	 *
	 * `$set` was array of method which will be called on configuration.
	 * Value can be one argument or array of more arguments.
	 *
	 * <code>
	 * 	$url = $l->createLink($c, array(
	 *		'setDebug' => true,
	 *		'setFilter' => array('Directory', 'testMethod'),
	 * 	));
	 * </code>
	 *
	 * @param Config\Configuration current
	 * @param array method => value
	 * @return string url
	 */
	public function createLink(Config\Configuration $configuration, $set)
	{
		$clone = clone $configuration;
		foreach ($set as $method => $value)
		{
			if (!is_array($value))
			{
				$value = array($value);
			}
			call_user_func_array(array($clone, $method), $value);
		}
		return $this->getLink($clone);
	}

	/**
	 * Return url to configuration.
	 * @param Config\Configuration
	 * @return string url
	 */
	public function getLink(Config\Configuration $configuration)
	{
		$uri = parse_url($this->requestUri);
		$path = $uri['path'];
		$query = array();
		if (isset($uri['query']))
		{
			parse_str($uri['query'], $query);
		}

		$filterDirectory = $configuration->getFilterDirectory();
		$methodDirectory = $configuration->getFilterMethod();
		if ($filterDirectory)
		{
			$query['test'] = $filterDirectory . ($methodDirectory ? '::' . $methodDirectory : '');
			unset($query['run']);
		}
		else if ($configuration->isRunned())
		{
			unset($query['test']);
			$query['run'] = '';
		}
		else if (!$configuration->isRunned())
		{
			unset($query['test']);
			unset($query['run']);
		}
		$configuration->isRunned();

		$tmp = strtr(http_build_query($query, NULL, '&'), array('%5C' => '\\', '%2F' => '/', '%3A' => ':'));
		return $path . '?' . $tmp;
	}

	/**
	 * Return relative url to filesystem path.
	 * @param string path
	 * @param string url
	 */
	public function getLinkToFile($file)
	{
		$relativeDiff = $this->pathDiff(dirname($this->scriptFilename), $file);
		return str_replace(DIRECTORY_SEPARATOR, '/', $relativeDiff);
	}

	/**
	 * @param string
	 * @param mixed
	 * @return mixed
	 */
	protected function getParameter($name, $default = NULL)
	{
		return isset($this->parameters[$name]) ? $this->parameters[$name] : $default;
	}

	/**
	 * Return relative path between two directory
	 * @param string /foo/bar/aaa/bbb
	 * @param string /foo/bar/ccc/ddd/eee
	 * @return string ../../ccc/ddd/eee
	 */
	protected function pathDiff($current, $wish)
	{
		$dir1 = explode(DIRECTORY_SEPARATOR, realpath($current));
		$dir2 = explode(DIRECTORY_SEPARATOR, realpath($wish));
		$result = array('.');
		$diferent = array();
		foreach (range(0, max(count($dir1), count($dir2))-1) as $i)
		{
			$part1 = next($dir1); $part2 = next($dir2);
			if (!$diferent AND $part1 === $part2) unset($dir1[$i], $dir2[$i]);
			else $diferent[] = array($part1, $part2);
		}
		foreach ($diferent as $d) if ($d[0]) $result[] = '..';
		foreach ($diferent as $d) if ($d[1]) $result[] = $d[1];
		return implode(DIRECTORY_SEPARATOR, $result);
	}

}
