<?php

namespace HttpPHPUnit\PHP52Builder;

use HttpPHPUnit\Nette\Object;
use /*HttpPHPUnit\*/Nette;


/**
 * @author Petr Prochazka
 */
class NetteConvertor extends Object
{

	public function toPhp53($from, $to)
	{
		$input = file_get_contents($from);
		$input = $this->replace($input, 'namespace Nette', 'namespace HttpPHPUnit\Nette');
		$input = $this->replace($input, "use\nNette", "use\nHttpPHPUnit\\Nette");
		$input = $this->replace($input, "'Nette\\", "'HttpPHPUnit\\Nette\\");
		$input = $this->replace($input, '"Nette\\\\', '"HttpPHPUnit\\\\Nette\\\\');
		$input = $this->replace($input, ' Nette\\\\', ' HttpPHPUnit\\\\Nette\\\\');
		$input = $this->replace($input, ' Nette\\', ' HttpPHPUnit\\Nette\\');
		$input = $this->replace($input, 'NETTE_DIR', '__DIR__');
		$input = $this->replace($input, "define('__DIR__',__DIR__);", "define('NETTE_DIR',__DIR__);", 1);
		$input = $this->replace($input, "PROTOCOL='safe';", "PROTOCOL='HttpPHPUnitsafe';", 1);
		$input = $this->replace($input, '//' . 'netteloader=Nette\Framework', '//' . 'netteloader=HttpPHPUnit\Nette\Framework', 1);
		$input = $this->replace($input, 'namespace {', "namespace HttpPHPUnit\\Nette {use\nHttpPHPUnit\\Nette;\n");
		$input = $this->replace($input, '#' .
			preg_quote("define('NETTE',TRUE);define('NETTE_DIR',__DIR__);define('NETTE_VERSION_ID',", '#') .
			'[0-9]+' .
			preg_quote(");define('NETTE_PACKAGE','5.3');", '#') .
		'#s', '/*$0*/', 1);
		$input = trim($input) . "\n";

		file_put_contents($to, $input);
	}

	public function toPhp52($from, $to)
	{
		$input = file_get_contents($from);

		$renamed = array();

		$f = function ($list, $mustExists = false) use (& $renamed) {
			foreach ($list as $php52ClassName => $originClassName)
			{
				$renamed[$php52ClassName] = $originClassName;
			}
		};

		$ff = function ($list) {
			$nonCanonical = array(
				'NJsonException' => 'Nette\Utils\JsonException',
				'NNeonEntity' => 'Nette\Utils\NeonEntity',
				'NNeonException' => 'Nette\Utils\NeonException',
				'NSmtpException' => 'Nette\Mail\SmtpException',
				'NRegexpException' => 'Nette\Utils\RegexpException',
				'NTokenizerException' => 'Nette\Utils\TokenizerException',
				'NUnknownImageFileException' => 'Nette\UnknownImageFileException',
				'NAssertionException' => 'Nette\Utils\AssertionException',
			);
			foreach ($list as $php52ClassName => $path)
			{
				if (isset($nonCanonical[$php52ClassName]))
				{
					$class = $nonCanonical[$php52ClassName];
				}
				else if ($path === '/common/exceptions')
				{
					$class = "Nette\\" . preg_replace('#^N#', '', $php52ClassName);
				}
				else if ($path === '/Application/exceptions')
				{
					$class = "Nette\\Application\\" . substr($php52ClassName, 1);
				}
				else if ($path === '/Latte/exceptions')
				{
					$class = "Nette\\Latte\\" . substr($php52ClassName, 1);
				}
				else if ($path === '/DI/exceptions')
				{
					$class = "Nette\\DI\\" . substr($php52ClassName, 1);
				}
				else if (Nette\Utils\Strings::startsWith($path, '/common/'))
				{
					$class = str_replace('/', '\\', 'Nette' . substr($path, 7));
				}
				else
				{
					$class = str_replace('/', '\\', "Nette{$path}");
				}
				if (in_array($class, $list))
				{
					throw new \Exception($php52ClassName . ' ' . $class . ' ' . array_search($class, $list));
				}
				$list[$php52ClassName] = $class;
			}
			return $list;
		};

		$fff = function ($list) {
			$result = array();
			foreach ($list as $originClassName => $php52ClassName)
			{
				if ($php52ClassName === 'Nette_MicroPresenter')
				{
					$php52ClassName = 'MicroPresenter';
				}
				else if ($php52ClassName{0} === '\\')
				{
					$php52ClassName = substr($php52ClassName, 1);
				}
				else if (preg_match('#^(?!I[A-Z])[A-Z]#', $php52ClassName) AND substr($originClassName, 0, 6) === 'Nette\\')
				{
					$php52ClassName = "N$php52ClassName";
				}
				if (in_array($originClassName, $result))
				{
					throw new \Exception($php52ClassName . ' ' . $originClassName . ' ' . array_search($originClassName, $result));
				}
				if (isset($result[$php52ClassName]))
				{
					throw new \Exception($originClassName . ' ' . $php52ClassName . ' ' . $result[$php52ClassName]);
				}
				$result[$php52ClassName] = $originClassName;
			}
			return $result;
		};

		$f($ff(include __DIR__ . '/nette52-loaderClasses.php'));
		$f($fff(include __DIR__ . '/nette52-renamedClasses.php'), true);

		unset($renamed['NCFix']);
		foreach ($renamed as $class)
		{
			if (!class_exists($class) AND !interface_exists($class)) throw new \Exception($class);
		}
		$renamed['NCFix'] = 'Nette\\NCFix';
		unset($renamed['NNetteLoader']);

		foreach ($renamed as $class => $original)
		{
			$renamed[$class] = 'HttpPHPUnit_' . str_replace('\\', '_', $original);
		}

		$addClass = array(
			'InvalidArgumentException' => true,
			'OutOfRangeException' => true,
			'UnexpectedValueException' => true,
		);

		foreach ($renamed as $class => $original)
		{
			if (!isset($addClass[$class]))
			{
				//$this->replace($input, "#(interface|class)\n" . preg_quote($class, '#') . "(\n|{)#s", "\$1\n$original\$2", 1);
			}
		}

		$input = $this->replace($input, $x = "#(?<=[^a-zA-Z0-9_]|^)(" . implode('|', array_map(function ($class) {
			return preg_quote($class, '#');
		}, array_keys($renamed))) . ")(?=[^a-zA-Z0-9_]|\$)#s", function ($m) use ($renamed) {
			list(, $class) = $m;
			return $renamed[$class];
		});

		foreach ($addClass as $class => $foo)
		{
			$input .= "class\n{$renamed[$class]}\nextends\n{$class}{}";
		}

		$input = $this->replace($input, ";function\ncallback(\$callback,\$m=NULL)", ";function\nHttpPHPUnit_Nette_callback(\$callback,\$m=NULL)", 1);
		$input = $this->replace($input, "}function\ndump(\$var)", "}function\nHttpPHPUnit_Nette_dump(\$var)", 1);
		$input = $this->replace($input, 'NETTE_DIR', 'dirname(__FILE__)');
		$input = $this->replace($input, "define('dirname(__FILE__)',dirname(__FILE__));", "define('NETTE_DIR',dirname(__FILE__));", 1);
		$input = $this->replace($input, "PROTOCOL='safe';", "PROTOCOL='HttpPHPUnitsafe';", 1);
		$input = $this->replace($input, '//' . 'netteloader=Nette\Framework', '//' . 'netteloader=HttpPHPUnit_Nette_Framework', 1);
		$input = $this->replace($input, '#' .
			preg_quote("define('NETTE',TRUE);define('NETTE_DIR',dirname(__FILE__));define('NETTE_VERSION_ID',", '#') .
			'[0-9]+' .
			preg_quote(");define('NETTE_PACKAGE','PHP 5.2 prefixed');", '#') .
		'#s', '/*$0*/', 1);
		$input = trim($input) . "\n";

		file_put_contents($to, $input);
	}

	protected function replace($input, $from, $to, $exactlyCount = NULL)
	{
		if ($from{0} === '#')
		{
			$f = $to instanceof \Closure ? 'preg_replace_callback' : 'preg_replace';
			$input = $f($from, $to, $input, -1, $count);
		}
		else
		{
			$input = str_replace($from, $to, $input, $count);
		}
		if (!$count) throw new \Exception($from);
		if ($exactlyCount !== NULL AND $count !== $exactlyCount) throw new \Exception($from);
		return $input;
	}
}
