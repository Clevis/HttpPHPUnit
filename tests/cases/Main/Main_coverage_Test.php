<?php

/**
 * @covers HttpPHPUnit\Main::coverage
 */
class Main_coverage_Test extends TestCase
{
	private $h;

	protected function setUp()
	{
		$this->h = new HttpPHPUnit\Main(LIBS_DIR . '/PHPUnit');
	}

	public function testSetProcessUncoveredFilesFromWhitelist_True()
	{
		$coverage = $this->h->coverage(__DIR__, __DIR__);
		$this->assertAttributeSame(true, 'processUncoveredFilesFromWhitelist', $coverage);
	}

}
