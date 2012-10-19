<?php

/**
 * @covers HttpPHPUnit\Loaders\IncludePathLoader
 */
class Loaders_IncludePathLoader_Test extends TestCase
{

	private $includePath;

	protected function setUp()
	{
		parent::setUp();
		$this->includePath = get_include_path();
	}

	protected function tearDown()
	{
		parent::tearDown();
		set_include_path($this->includePath);
	}

	private function assertIncludePath($changed)
	{
		$ip = get_include_path();
		$ip = str_replace($this->includePath, '...', $ip);
		$this->assertSame($changed, $ip);
	}

	public function testAutoDetectByIncludePath()
	{
		$i = new Loaders_IncludePathLoader_Test_Loader;
		$i->load();
		$this->assertSame(realpath(LIBS_DIR . '\PHPUnit\PHPUnit\Autoload.php'), $i->include);
		$this->assertIncludePath('...');
	}

	public function testAutoDetectByWithoutIncludePath()
	{
		restore_include_path();
		$i = new Loaders_IncludePathLoader_Test_Loader;
		$this->setExpectedException('Exception', 'Unable autodetect PHPUnit: ' . realpath(LIBS_DIR . '/..') . '/PHPUnit/PHPUnit/Autoload.php');
		$i->load();
	}

	public function testSet()
	{
		$i = new Loaders_IncludePathLoader_Test_Loader(LIBS_DIR . '/PHPUnit');
		$i->load();
		$this->assertSame('PHPUnit/Autoload.php', $i->include);
		$this->assertIncludePath(LIBS_DIR . '/PHPUnit' . ';...');
	}

	public function testSetNoDir()
	{
		$i = new Loaders_IncludePathLoader_Test_Loader(LIBS_DIR . '/PHPUnitX');
		$this->setExpectedException('Exception', 'PHPUnit not found: ' . LIBS_DIR . '/PHPUnitX');
		$i->load();
	}

	public function testSetNoFile()
	{
		$i = new Loaders_IncludePathLoader_Test_Loader(__DIR__);
		$this->setExpectedException('Exception', 'PHPUnit not found: ' . __DIR__ . '/PHPUnit/Autoload.php');
		$i->load();
	}

}

class Loaders_IncludePathLoader_Test_Loader extends HttpPHPUnit\Loaders\IncludePathLoader
{

	public $include;

	protected function limitedScopeLoad(/*$file*/)
	{
		$this->include = func_get_arg(0);
	}
}
