<?php

require_once __DIR__ . '/../libs/Nette/loader.php';
require_once __DIR__ . '/../libs/dump.php';

$configurator = new Nette\Config\Configurator;
$configurator->enableDebugger();
$configurator->setTempDirectory( __DIR__ . '/tmp');
$configurator->createRobotLoader()
	->addDirectory(__DIR__ . '/../libs')
	->addDirectory(__DIR__ . '/cases')
	->register()
;

require_once __DIR__ . '/TestCase.php';

define('LIBS_DIR', __DIR__ . '/../libs');
