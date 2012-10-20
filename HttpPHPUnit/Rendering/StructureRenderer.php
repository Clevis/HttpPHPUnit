<?php

namespace HttpPHPUnit\Rendering;

use HttpPHPUnit\Nette\Object;
use HttpPHPUnit\Nette\Utils\Finder;
use HttpPHPUnit\Nette\Templating\FileTemplate;
use HttpPHPUnit\Config;


/**
 * @author Petr Prochazka
 */
class StructureRenderer extends Object
{

	/** @var string dir */
	private $testDirectory;

	/** @var string dir or file */
	private $filterDirectory;

	/** @var NULL|string */
	private $filterMethod = NULL;

	/** @var FileTemplate */
	private $template;

	/** @var callable (string|NULL $filterDirectory, string|NULL $filterMethod = NULL) => string */
	private $createFilterLink;

	public function __construct(Config\Configuration $configuration, Config\Link $link)
	{
		$filterMethod = $configuration->getFilterMethod();
		if ($filterMethod)
		{
			$tmp = explode(' ', $filterMethod, 2); // data provider
			$this->filterMethod = $tmp[0];
		}
		$this->testDirectory = realpath($configuration->getTestDirectory());
		$this->filterDirectory = realpath($this->testDirectory . '/' . $configuration->getFilterDirectory());
		if (!$this->testDirectory)
		{
			throw new \Exception("Directory not found: {$configuration->getTestDirectory()}");
		}
		$this->createFilterLink = function ($filterDirectory, $filterMethod = NULL) use ($link, $configuration) {
			return $link->createLink($configuration, array(
				'setFilter' => array($filterDirectory, $filterMethod),
			));
		};
		$this->template = TemplateFactory::create(__DIR__ . '/StructureRenderer.latte');
	}

	/**
	 * Render structure
	 */
	public function render()
	{
		$editor = new OpenInEditor;
		$structure = (object) array('structure' => array());
		$isAll = true;
		foreach (Finder::findFiles('*Test.php')->from($this->testDirectory) as $file)
		{
			$relative = substr($file, strlen($this->testDirectory) + 1);
			$cursor = & $structure;
			foreach (explode(DIRECTORY_SEPARATOR, $relative) as $d)
			{
				$r = isset($cursor->relative) ? $cursor->relative . DIRECTORY_SEPARATOR : NULL;
				$cursor = & $cursor->structure[$d];
				$path = $this->testDirectory . DIRECTORY_SEPARATOR . $r . $d;
				$open = $path === $this->filterDirectory;
				if ($open) $isAll = false;
				$cursor = (object) array(
					'relative' => $r . $d,
					'name' => $d,
					'open' => $open,
					'structure' => isset($cursor->structure) ? $cursor->structure : array(),
					'editor' => $editor->link($path, 1),
					'mode' => is_file($path) ? 'file' : 'folder',
				);
				$cursor->link = call_user_func($this->createFilterLink, $cursor->relative);
				if (!$cursor->structure AND $cursor->mode === 'file')
				{
					foreach ($this->loadMethods($path) as $l => $m)
					{
						$cursor->structure[$m] = (object) array(
							'relative' => $cursor->relative . '::' . $m,
							'link' => call_user_func($this->createFilterLink, $cursor->relative, $m),
							'name' => $m,
							'open' => $cursor->open AND $this->filterMethod === $m,
							'structure' => array(),
							'editor' => $editor->link($path, $l),
							'mode' => 'method',
						);
					}
				}
			}
			$cursor->name = $file->getBasename();
		}

		$this->template->backToAllLink = !($isAll AND $this->filterDirectory !== false) ? call_user_func($this->createFilterLink, NULL) : NULL;
		$this->template->structure = $structure->structure;
		$this->template->render();
	}

	/**
	 * Load TestCase methods.
	 * @param string
	 * @return array of line => testName
	 */
	private function loadMethods($path)
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
