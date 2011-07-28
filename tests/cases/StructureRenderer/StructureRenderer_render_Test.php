<?php

use HttpPHPUnit\StructureRenderer;

/**
 * @covers HttpPHPUnit\StructureRenderer::render
 */
class StructureRenderer_render_Test extends TestCase
{

	public function testIsAll()
	{
		$r = new StructureRenderer(__DIR__, 'StructureRenderer_construct_Test.php::y');
		ob_start();
		$r->render();
		$content = ob_get_clean();
		$this->assertContains('Back to all', $content);
	}

	public function testIsAllBad()
	{
		$r = new StructureRenderer(__DIR__, 'XStructureRenderer_construct_Test.php::y');
		ob_start();
		$r->render();
		$content = ob_get_clean();
		$this->assertContains('Back to all', $content);
	}

	public function testIsAllNo()
	{
		$r = new StructureRenderer(__DIR__, '::');
		ob_start();
		$r->render();
		$content = ob_get_clean();
		$this->assertNotContains('Back to all', $content);
	}

}
