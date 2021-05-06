<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


// All errors are reported by default
error_reporting(-1);
ini_set('display_errors', 1);


// Default class autoload
spl_autoload_register(function($class)
{
	$path = str_replace('\\', '/', $class);

	$path = dirname(__DIR__) . '/' . $path . '.php';

	if(is_file($path))
	{
		require $path . '';
	}
});
