<?php

/**
 * @covers HttpPHPUnit\Main::coverage
 */
class Main_coverage_Test extends TestCase
{
	private $h;

	protected function setUp()
	{
		$this->h = new HttpPHPUnit\Main(new HttpPHPUnit\Loaders\IncludePathLoader(LIBS_DIR . '/PHPUnit'));
	}

	public function testSetProcessUncoveredFilesFromWhitelist_True()
	{
		$test = $this;
		$called = false;

		$this->h->coverage(__DIR__, __DIR__, function (PHP_CodeCoverage $coverage) use ($test, & $called) {
			$coverage->filter()->removeDirectoryFromWhitelist(__DIR__);
			$test->assertAttributeSame(true, 'processUncoveredFilesFromWhitelist', $coverage);
			$called = true;
		});

		$a = $this->h->getConfigurator()->createApplication();
		$c = $this->readAttribute($a, 'configuration');
		$c->setFilter(NULL);
		$c->isRunned(TRUE);
		$e = $this->readAttribute($a, 'events');
		$e->triggerListener('onStart');
		$this->assertSame(true, $called);
	}

}
