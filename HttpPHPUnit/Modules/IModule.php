<?php

namespace HttpPHPUnit\Modules;

use HttpPHPUnit\Events;


/**
 * @author Petr Prochazka
 */
interface IModule
{

	/**
	 * Register module events.
	 * @param Events\ModuleEvents
	 */
	public function register(Events\ModuleEvents $events);

}
