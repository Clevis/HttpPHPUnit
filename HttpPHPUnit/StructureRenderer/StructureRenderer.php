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
		$editor = new OpenInEditor;
		$structure = (object) array('structure' => array());
		foreach (Finder::findFiles('*Test.php')->from($this->dir) as $file)
		{
			$relative = substr($file, strlen($this->dir) + 1);
			$cursor = & $structure;
			foreach (explode(DIRECTORY_SEPARATOR, $relative) as $d)
			{
				$r = isset($cursor->relative) ? $cursor->relative . DIRECTORY_SEPARATOR : NULL;
				$cursor = & $cursor->structure[$d];
				$path = $this->dir . DIRECTORY_SEPARATOR . $r . $d;
				$cursor = (object) array(
					'relative' => $r . $d,
					'name' => $d,
					'open' => $path === $this->open,
					'structure' => isset($cursor->structure) ? $cursor->structure : array(),
					'editor' => $editor->link($path, 1),
				);
				if ($cursor->open AND !$cursor->structure AND is_file($this->open))
				{
					foreach ($this->loadMethod() as $l => $m)
					{
						$cursor->structure[$m] = (object) array(
							'relative' => $cursor->relative . '::' . $m,
							'name' => $m,
							'open' => $this->method === $m,
							'structure' => array(),
							'editor' => $editor->link($this->open, $l),
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

	/** @return array of line => testName */
	private function loadMethod()
	{
		$result = array();
		if (is_file($this->open))
		{
			$data = file_get_contents($this->open);
			foreach (explode("\n", $data) as $line => $lineData)
			{
				if (preg_match('#function\s+(test[^\s\(]*)\s*\(#si', $lineData, $match))
				{
					$result[$line+1] = $match[1];
				}
			}
		}
		return $result;
	}

}
