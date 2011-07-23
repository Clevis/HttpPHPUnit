<?php

define('LIBS_DIR', __DIR__ . '/../libs');

require_once LIBS_DIR . '/Nette/loader.php';
require_once LIBS_DIR . '/dump.php';

use Nette\Diagnostics\Debugger as Debug;
use Nette\Environment;
use Nette\Loaders\RobotLoader;

Debug::enable(false);
Debug::$strictMode = true;

Environment::setVariable('tempDir', __DIR__ . '/tmp');

$r = new RobotLoader;
$r->setCacheStorage(Environment::getContext()->cacheStorage);
$r->addDirectory(LIBS_DIR);
$r->addDirectory(__DIR__ . '/../HttpPHPUnit');
$r->addDirectory(__DIR__ . '/cases');
$r->register();

require_once __DIR__ . '/TestCase.php';

