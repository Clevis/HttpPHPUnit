<?php

use HttpPHPUnit\StructureRenderer;

/**
 * @covers HttpPHPUnit\StructureRenderer::__construct
 */
class StructureRenderer_construct_Test extends TestCase
{

	public function testDataProvider1()
	{
		$r = new StructureRenderer(__DIR__, 'StructureRenderer_construct_Test.php::y');
		$this->assertAttributeSame('y', 'method', $r);
		$this->assertAttributeSame(__FILE__, 'open', $r);
	}

	public function testDataProvider2()
	{
		$r = new StructureRenderer(__DIR__, 'StructureRenderer_construct_Test.php::y with data set #0');
		$this->assertAttributeSame('y', 'method', $r);
		$this->assertAttributeSame(__FILE__, 'open', $r);
	}

}
