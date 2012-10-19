<?php

/**
 * @covers HttpPHPUnit\Config\Information
 */
class Config_Information_Test extends TestCase
{

	/**
	 * @dataProvider dataProviderIsRunnedAllTest
	 */
	public function testIsRunnedAllTest($runned, $filter, $result)
	{
		$c = new HttpPHPUnit\Config\Configuration;
		$c->setRunned($runned);
		$c->setFilter($filter);
		$i = new HttpPHPUnit\Config\Information($c);
		$this->assertSame($result, $i->isRunnedAllTest());
	}

	public function dataProviderIsRunnedAllTest()
	{
		return array(
			array(false, NULL, false),
			array(false, 'x', false),
			array(true, NULL, true),
			array(true, 'x', false),
		);
	}

}
