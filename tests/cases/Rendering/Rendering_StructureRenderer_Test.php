<?php

use HttpPHPUnit\Rendering\StructureRenderer;

/**
 * @covers HttpPHPUnit\Rendering\StructureRenderer
 */
class Rendering_StructureRenderer_Test extends TestCase
{

	public function testDataProvider1()
	{
		$configuration = new HttpPHPUnit\Config\Configuration;
		$configuration->setTestDirectory(__DIR__);
		$configuration->setFilter('Rendering_StructureRenderer_Test.php', 'y');
		$r = new StructureRenderer($configuration, new HttpPHPUnit\Config\Link(array(), NULL, NULL));
		$this->assertAttributeSame('y', 'filterMethod', $r);
		$this->assertAttributeSame(__FILE__, 'filterDirectory', $r);
		$this->assertAttributeSame(__DIR__, 'testDirectory', $r);
	}

	public function testDataProvider2()
	{
		$configuration = new HttpPHPUnit\Config\Configuration;
		$configuration->setTestDirectory(__DIR__);
		$configuration->setFilter('Rendering_StructureRenderer_Test.php', 'y with data set #0');
		$r = new StructureRenderer($configuration, new HttpPHPUnit\Config\Link(array(), NULL, NULL));
		$this->assertAttributeSame('y', 'filterMethod', $r);
		$this->assertAttributeSame(__FILE__, 'filterDirectory', $r);
		$this->assertAttributeSame(__DIR__, 'testDirectory', $r);
	}

	public function testIsAll()
	{
		$configuration = new HttpPHPUnit\Config\Configuration;
		$configuration->setTestDirectory(__DIR__);
		$configuration->setFilter('Rendering_StructureRenderer_Test.php', 'y');
		$r = new StructureRenderer($configuration, new HttpPHPUnit\Config\Link(array(), NULL, NULL));
		ob_start();
		$r->render();
		$content = ob_get_clean();
		$this->assertContains('Back to all', $content);
	}

	public function testIsAllBad()
	{
		$configuration = new HttpPHPUnit\Config\Configuration;
		$configuration->setTestDirectory(__DIR__);
		$configuration->setFilter('XRendering_StructureRenderer_Test.php', 'y');
		$r = new StructureRenderer($configuration, new HttpPHPUnit\Config\Link(array(), NULL, NULL));
		ob_start();
		$r->render();
		$content = ob_get_clean();
		$this->assertContains('Back to all', $content);
	}

	public function testIsAllNo()
	{
		$configuration = new HttpPHPUnit\Config\Configuration;
		$configuration->setTestDirectory(__DIR__);
		$configuration->setFilter(NULL);
		$r = new StructureRenderer($configuration, new HttpPHPUnit\Config\Link(array(), NULL, NULL));
		ob_start();
		$r->render();
		$content = ob_get_clean();
		$this->assertNotContains('Back to all', $content);
	}

}
