<?php

namespace HttpPHPUnit\PHP52Builder;

use HttpPHPUnit\Nette\Object;
use /*HttpPHPUnit\*/Nette;


/**
 * @author Petr Prochazka
 */
class Builder extends Object
{

	private $replaces = array();
	
	public function __construct()
	{

	}

	public function __destruct()
	{
		if ($this->replaces) throw new \Exception;
	}

	public function addReplace($file, $replaceFrom, $replaceTo)
	{
		$this->replaces[realpath($file)][$replaceFrom] = $replaceTo;
	}

	public function wipe($dir)
	{
		@mkdir($dir);
		foreach (Nette\Utils\Finder::find('*')->from($dir)->childFirst() as $path)
		{
			if (is_dir($path))
			{
				rmdir($path);
			}
			else
			{
				unlink($path);
			}
		}
	}

	public function copy($from, $to)
	{
		foreach ($this->createFinder($from) as $file)
		{
			$input = file_get_contents($file);
			$input = $this->applyReplaces($file, $input);
			file_put_contents($this->getToPath($from, $to, $file), $input);
		}
	}

	protected function applyReplaces($file, $input)
	{
		$file = realpath($file);
		if ($file AND isset($this->replaces[$file]))
		{
			foreach ($this->replaces[$file] as $replaceFrom => $replacesTo)
			{
				list($replacesTo, $expectedCount) = (array) $replacesTo + array(1 => 1);
				if ($replaceFrom{0} === '#')
				{
					$input = preg_replace($replaceFrom, $replacesTo, $input, -1, $count);
				}
				else
				{
					$input = str_replace($replaceFrom, $replacesTo, $input, $count);
				}
				if ($count !== $expectedCount) throw new \Exception($file . ': ' . $replaceFrom . " $count !== $expectedCount");
			}
			unset($this->replaces[$file]);
		}
		return $input;
	}

	private $ignoreUnexistsClases;

	public function build($from, $to, array $ignoreUnexistsClases = array())
	{
		$ignoreUnexistsClases = array_map('strtolower', $ignoreUnexistsClases);
		$ignoreUnexistsClases = $ignoreUnexistsClases ? array_combine($ignoreUnexistsClases, $ignoreUnexistsClases) : array();
		$this->ignoreUnexistsClases = $ignoreUnexistsClases;
		foreach ($this->createFinder($from) as $file)
		{
			$baseName = basename($file);
			if ($baseName === 'nette.min.php') continue;
			$input = file_get_contents($file);
			$input = $this->applyReplaces($file, $input);
			if ($file->getExtension() === 'php')
			{
				$input = $this->processPhp($input, $baseName);
				$input = PhpParser::replaceClosures($input);
				$input = PhpParser::replaceDirConstant($input);
				$input = PhpParser::replaceLateStaticBinding($input);
			}
			file_put_contents($this->getToPath($from, $to, $file), $input);
		}
		$this->ignoreUnexistsClases = NULL;
	}

	protected function createFinder($path)
	{
		if (is_file($path))
		{
			return array(new \SplFileInfo($path));
		}
		return Nette\Utils\Finder::findFiles('*')->from($path);
	}

	protected function getToPath($from, $to, $file)
	{
		$path = rtrim($to . '/' . preg_replace('#^\.(?:\\\\|/|$)#si', '', $this->pathDiff($from, $file)), '\/');
		$this->createDirectoryRecurcivery($path);
		return $path;
	}

	public function createDirectoryRecurcivery($path)
	{
		$directory = dirname($path);
		$toCreate = array();
		while (!is_dir($directory))
		{
			$toCreate[] = $directory;
			$directory = dirname($directory);
		}
		array_map('mkdir', array_reverse($toCreate));
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

	public function processPhp($input, $fileName)
	{
		$parser = new PhpParser($input);
		$namespace = '';
		$uses = array();
		while (($token = $parser->fetch()) !== false)
		{
			if ($parser->isCurrent(T_NAMESPACE))
			{
				$pos = $parser->position;
				$parser->fetchAll(T_WHITESPACE, T_COMMENT);
				$namespace = $parser->fetchAll(T_STRING, T_NS_SEPARATOR);
				$parser->fetchUntil(';');
				$parser->fetch(';');
				$uses = array();
				$parser->replace('', $pos);
			}
			else if ($parser->isCurrent(T_USE))
			{
				do {
					$pos = $parser->position;
					$parser->fetchAll(T_WHITESPACE, T_COMMENT);
					if ($class = $parser->fetchAll(T_STRING, T_NS_SEPARATOR))
					{
						$uses[strtolower($class)] = $class;
						$parser->fetchAll(T_WHITESPACE, T_COMMENT);
						$end = (bool) $parser->fetch(';');
						$parser->replace('', $pos);
						if ($end) break;
					}
				} while ($class AND $parser->fetch(','));
			}
			else if ($parser->isCurrent(T_INSTANCEOF, T_EXTENDS, T_IMPLEMENTS, T_NEW, T_CATCH, T_CLASS, T_INTERFACE))
			{
				do {
					if ($parser->isCurrent(T_CATCH))
					{
						$parser->fetchAll(T_WHITESPACE, T_COMMENT, '(');
					}
					else
					{
						$parser->fetchAll(T_WHITESPACE, T_COMMENT);
					}
					$pos = $parser->position + 1;
					if ($class = $parser->fetchAll(T_STRING, T_NS_SEPARATOR))
					{
						$parser->replace($this->ns($namespace, $uses, $class), $pos);
					}
				} while ($class && $parser->fetch(','));
			}
			else if ($parser->isCurrent(T_STRING, T_NS_SEPARATOR)) // Class:: or typehint
			{
				$pos = $parser->position;
				$identifier = $token . $parser->fetchAll(T_STRING, T_NS_SEPARATOR);
				if ($parser->isNext(T_DOUBLE_COLON, T_VARIABLE))
				{
					$parser->replace($this->ns($namespace, $uses, $identifier), $pos);
				}
			}
			else if ($parser->isCurrent(T_DOC_COMMENT, T_COMMENT))
			{
				// @var Class or \Class or Nm\Class or Class:: (preserves CLASS)
				$that = $this;
				$parser->replace(preg_replace_callback('#((?:@var(?:\s+array of)?|returns?|param|throws|@link|@covers|property[\w-]*)\s+)([\w\\\\|]+)#', function($m) use ($that, $namespace, $uses) {
					$parts = array();
					foreach (explode('|', $m[2]) as $part) {
						$parts[] = preg_match('#^\\\\?[A-Z].*[a-z]#', $part) ? $that->ns($namespace, $uses, $part) : $part;
					}
					return $m[1] . implode('|', $parts);
				}, $token));
			}
			else if ($parser->isCurrent(T_CONSTANT_ENCAPSED_STRING))
			{
				if (preg_match('#(^.)([A-Z][\w\\\\]*[a-z]\w*)(:.*|.\z)#', $token, $m))
				{
					$class = str_replace('\\\\', '\\', $m[2], $double);
					$parser->replace($m[1] . str_replace('\\', $double ? '\\\\' : '\\', $this->ns($namespace, $uses, $class, false)) . $m[3]);
				}
				else
				{
					$that = $this;
					$parser->replace(preg_replace_callback('#(HttpPHPUnit\\\\[a-zA-Z0-9_\\\\]+[a-zA-Z0-9_])#si', function($m) use ($that, $namespace, $uses) {
						return $that->ns($namespace, $uses, $m[1], false);
					}, $token));
				}
			}
			else if ($parser->isCurrent(T_NS_C, T_NS_SEPARATOR))
			{
				throw new \Exception;
			}
		}

		$parser->position = 0;
		return $parser->fetchAll();
	}

	public function ns($namespace, array $uses, $class, $need = true)
	{
		if ($class === 'self' OR $class === 'parent')
		{
			return $class;
		}
		if ($class === 'static')
		{
			return 'self';
		}
		$class = $this->ns2($namespace, $uses, $class, $need);
		if ($class === NULL AND !class_exists($class) AND !interface_exists($class))
		{
			if ($need)
			{
				if (!isset($this->ignoreUnexistsClases[strtolower(func_get_arg(2))]))
				{
					throw new \Exception(func_get_arg(2));
				}
			}
			return func_get_arg(2);
		}
		if (Nette\Utils\Strings::startsWith(strtolower($class), strtolower('HttpPHPUnit\\')))
		{
			return str_replace('\\', '_', $class);
		}
		return $class;
	}

	protected function ns2($namespace, array $uses, $class, $need = true)
	{
		if ($class{0} === '\\')
		{
			// todo aby nebyli zadne
			return ltrim($class, '\\');
		}
		$lcClass = strtolower($class);
		$firstNs = explode('\\', $lcClass, 2); $firstNs = $firstNs[0];
		foreach ($uses as $lcNs => $ns)
		{
			if ($lcNs === $firstNs OR Nette\Utils\Strings::endsWith($lcNs, '\\' . $firstNs))
			{
				$ns = preg_replace('#(^|\\\\)' . preg_quote($firstNs, '#') . '$#si', '',  $ns);
				return ltrim($ns . '\\' . $class, '\\');
			}
		}
		
		if (class_exists($namespace . '\\' . $class) OR interface_exists($namespace . '\\' . $class))
		{
			return ltrim($namespace . '\\' . $class, '\\');
		}

		if (class_exists($class) OR interface_exists($class))
		{
			if ($need)
			{
				throw new \Exception($class);
			}
			return ltrim($class, '\\');
		}
		return NULL;
	}

}
