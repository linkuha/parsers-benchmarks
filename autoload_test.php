<?php

set_include_path(__DIR__ . DIRECTORY_SEPARATOR . 'classes'
	. PATH_SEPARATOR .  get_include_path() );
spl_autoload_extensions('.php, .class.php');
spl_autoload_register();

function classesAutoload($classname) {

	$classname = strtolower(strtr($classname, '\\', DIRECTORY_SEPARATOR));
	$filename = __DIR__ . "/classes/" . $classname . ".php";
	if ( file_exists( $filename ) && is_readable( $filename ) )
	{
		require $filename;
	} else {
		throw new \Exception('File can not be found');
	}
}

spl_autoload_register('classesAutoload');
if ( version_compare( PHP_VERSION , '5.3.0-dev' , '>=' ) )
	spl_autoload_register('classesAutoload', true, false);
elseif ( version_compare( PHP_VERSION , '5.1.2' , '>=' ) )
	spl_autoload_register('classesAutoload', true);
else
{ function __autoload( $class ){return classesAutoload($class);} }