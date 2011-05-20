<?php

use Nette\Application\UI\Control;
use Nette\DirectoryNotFoundException;
use Nette\Utils\Finder;

/**
 * @author Petr Prochazka
 */
class StructureRenderer extends Control
{
	/** @var string dir */
	private $dir;

	/** @var string dir or file */
	private $open;

	/** @var NULL|string */
	private $method = NULL;

	/**
	 * @param string
	 * @param string
	 */
	public function __construct($dir, $open)
	{
		$open = explode('::', $open, 2);
		if (isset($open[1]))
		{
			$this->method = $open[1];
		}
		$this->open = realpath($dir . '/' . $open[0]);
		$this->dir = realpath($dir);
		if (!$this->dir)
		{
			throw new DirectoryNotFoundException($dir);
		}
	}

	public function render()
	{
		$structure = (object) array('structure' => array());
		foreach (Finder::findFiles('*Test.php')->from($this->dir) as $file)
		{
			$relative = substr($file, strlen($this->dir) + 1);
			$cursor = & $structure;
			foreach (explode(DIRECTORY_SEPARATOR, $relative) as $d)
			{
				$r = isset($cursor->relative) ? $cursor->relative . DIRECTORY_SEPARATOR : NULL;
				$cursor = & $cursor->structure[$d];
				$cursor = (object) array(
					'relative' => $r . $d,
					'name' => $d,
					'open' => ($this->dir . DIRECTORY_SEPARATOR . $r . $d) === $this->open,
					'structure' => isset($cursor->structure) ? $cursor->structure : array(),
				);
				if ($cursor->open AND !$cursor->structure AND is_file($this->open))
				{
					foreach ($this->loadMethod() as $m)
					{
						$cursor->structure[$m] = (object) array(
							'relative' => $cursor->relative . '::' . $m,
							'name' => $m,
							'open' => $this->method === $m,
							'structure' => array(),
						);
					}
				}
			}
			$cursor->name = $file->getBasename();
		}
		$this->template->structure = $structure->structure;
		$this->template->setFile(__DIR__ . '/StructureRenderer.latte');
		$this->template->render();
	}

	/** @return array of string */
	private function loadMethod()
	{
		if (is_file($this->open) AND preg_match_all('#function\s+(test[^\s\(]*)\s*\(#si', file_get_contents($this->open), $matches))
		{
			return $matches[1];
		}
		return array();
	}

}
