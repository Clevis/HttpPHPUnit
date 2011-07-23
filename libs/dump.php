<?php

use \Nette\Diagnostics\Debugger;

/** Dump */
function d($var)
{
	if (func_num_args() > 1) $var = func_get_args();
	Debugger::dump($var);
	return func_get_arg(0);
}

/** Bar dump */
function dd($var)
{
	if (func_num_args() > 1) $var = func_get_args();
	else if (is_array($var)) $var = array(NULL => $var);
	Debugger::barDump($var);
	return func_get_arg(0);
}
