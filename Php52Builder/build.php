<?php

require_once __DIR__ . '/../libs/Nette/loader.php';
require_once __DIR__ . '/../libs/dump.php';
require_once __DIR__ . '/../HttpPHPUnit/init.php';



$loader = new HttpPHPUnit\Loaders\IncludePathLoader(__DIR__ . '/../libs/PHPUnit');
$loader->load();

require_once __DIR__ . '/../tests/TestCase.php';

$configurator = new Nette\Config\Configurator;
$configurator->enableDebugger();
$configurator->setTempDirectory( __DIR__ . '/../tests/tmp');
$configurator->createRobotLoader()
	->addDirectory(__DIR__ . '/../libs')
	->addDirectory(__DIR__ . '/../tests/cases')
	->addDirectory(__DIR__)
	->register()
;

$directory = __DIR__ . '/temp/php52';

$builder = new HttpPHPUnit\PHP52Builder\Builder;

$builder->addReplace(__DIR__ . '/../tests/boot.php', 'Nette\Config\Configurator', 'HttpPHPUnit\Nette\Config\Configurator');
$builder->addReplace(__DIR__ . '/../tests/boot.php', "require_once __DIR__ . '/../libs/Nette/loader.php';", 'function callback($callback, $m = NULL) { return new HttpPHPUnit\Nette\Callback($callback, $m); }');
$builder->addReplace(__DIR__ . '/../tests/run.php', "require_once __DIR__ . '/../libs/Nette/loader.php';", '');
$builder->addReplace(__DIR__ . '/../tests/run.php', "require_once __DIR__ . '/../libs/Nette/loader.php';", '');
$builder->addReplace(__DIR__ . '/../libs/dump.php', 'use Nette\Diagnostics\Debugger;', '');
$builder->addReplace(__DIR__ . '/../libs/dump.php', 'Debugger', array('HttpPHPUnit_Nette_Diagnostics_Debugger', 2));
$builder->addReplace(__DIR__ . '/../HttpPHPUnit/init.php', '#\z#', '
class HttpPHPUnit_PHP52_Callback
{

	/** @var array Simulate closure scope in php 5.2 @access private */
	static $vars = array();

	/**
	 * Simulate closure scope in php 5.2
	 * <code>
	 * 	function () use ($foo, $bar) {}
	 * </code>
	 * <code>
	 * 	create_function(\'\', \'extract(HttpPHPUnit_PHP52_Callback::$vars[\'.HttpPHPUnit_PHP52_Callback::uses(array(\'foo\'=>$foo,\'bar\'=>$bar)).\'], EXTR_REFS);\')
	 * </code>
	 * @access private
	 * @see Orm\Builder\PhpParser::replaceClosures()
	 * @param array
	 * @return int
	 */
	static function uses($args)
	{
		self::$vars[] = $args;
		return count(self::$vars)-1;
	}
}
');

$builder->wipe($directory);

$builder->build(__DIR__ . '/../HttpPHPUnit', $directory . '/HttpPHPUnit', array('HttpPHPUnit_PHP52_Callback'));
$builder->build(__DIR__ . '/../tests/cases', $directory . '/tests/cases', array('FooBar'));
$builder->build(__DIR__ . '/../tests/TestCase.php', $directory . '/tests/TestCase.php');

$builder->build(__DIR__ . '/../tests/boot.php', $directory . '/tests/boot.php');
$builder->build(__DIR__ . '/../tests/run.php', $directory . '/tests/run.php');

$builder->copy(__DIR__ . '/../libs/PHPUnit', $directory . '/libs/PHPUnit');
$builder->copy(__DIR__ . '/../libs/dump.php', $directory . '/libs/dump.php');

$builder->copy(__DIR__ . '/../tests/report/.gitignore', $directory . '/tests/report/.gitignore');
$builder->copy(__DIR__ . '/../tests/tmp/.gitignore', $directory . '/tests/tmp/.gitignore');

$builder->createDirectoryRecurcivery($directory . '/HttpPHPUnit/Nette/nette.min.php');
$c = new HttpPHPUnit\PHP52Builder\NetteConvertor;
$c->toPhp53(__DIR__ . '/temp/Nette/nette.min.php53.php', __DIR__ . '/../HttpPHPUnit/Nette/nette.min.php');
$c->toPhp52(__DIR__ . '/temp/Nette/nette.min.php52.php', $directory . '/HttpPHPUnit/Nette/nette.min.php');
