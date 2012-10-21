<?php

namespace HttpPHPUnit\Loaders;


/**
 * Load PHPUnit.
 *
 * @author Petr Prochazka
 */
interface IPHPUnitLoader
{

	/**
	 * Load PHPUnit file.
	 * @param string path
	 */
	public function load($file);

}
