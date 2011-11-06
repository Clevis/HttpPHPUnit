<?php

require_once __DIR__ . '/../libs/dump.php';
require_once __DIR__ . '/../HttpPHPUnit/init.php';

$http = new HttpPHPUnit(__DIR__ . '/../libs/PHPUnit');

require_once __DIR__ . '/boot.php';

$http->coverage(__DIR__ . '/../HttpPHPUnit', __DIR__ . '/report');

$http->run(__DIR__ . '/cases');
