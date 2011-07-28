<?php

namespace HttpPHPUnit;

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
			$tmp = explode(' ', $open[1], 2);
			$this->method = $tmp[0];
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
		$isAll = true;
		foreach (Finder::findFiles('*Test.php')->from($this->dir) as $file)
		{
			$relative = substr($file, strlen($this->dir) + 1);
			$cursor = & $structure;
			foreach (explode(DIRECTORY_SEPARATOR, $relative) as $d)
			{
				$r = isset($cursor->relative) ? $cursor->relative . DIRECTORY_SEPARATOR : NULL;
				$cursor = & $cursor->structure[$d];
				$path = $this->dir . DIRECTORY_SEPARATOR . $r . $d;
				$open = $path === $this->open;
				if ($open) $isAll = false;
				$cursor = (object) array(
					'relative' => $r . $d,
					'name' => $d,
					'open' => $open,
					'structure' => isset($cursor->structure) ? $cursor->structure : array(),
					'editor' => $editor->link($path, 1),
					'mode' => is_file($path) ? 'file' : 'folder',
				);
				if (!$cursor->structure AND $cursor->mode === 'file')
				{
					foreach ($this->loadMethod($path) as $l => $m)
					{
						$cursor->structure[$m] = (object) array(
							'relative' => $cursor->relative . '::' . $m,
							'name' => $m,
							'open' => $cursor->open AND $this->method === $m,
							'structure' => array(),
							'editor' => $editor->link($path, $l),
							'mode' => 'method',
						);
					}
				}
			}
			$cursor->name = $file->getBasename();
		}

		$this->template->isAll = ($isAll AND $this->open !== false);
		$this->template->basePath = TemplateFactory::getBasePath();
		$this->template->structure = $structure->structure;
		$this->template->setFile(__DIR__ . '/StructureRenderer.latte');
		$this->template->render();
	}

	/**
	 * @param string
	 * @return array of line => testName
	 */
	private function loadMethod($path)
	{
		$result = array();
		if (is_file($path))
		{
			$data = file_get_contents($path);
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
