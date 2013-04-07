<?php

//Set the PHP error reporting level. t
error_reporting(E_ALL);

// Short aliase for DIRECTORY_SEPARATOR
define('DS', DIRECTORY_SEPARATOR);

// Set the full path to the docroot
define('BASEDIR', realpath(dirname(__FILE__)).DS);

/**
 * The directory in which your application specific resources 
 * are located.
 */
$application = BASEDIR.'app';
$modules	 = BASEDIR.'mod';
$system 	 = BASEDIR.'sys';
$themes 	 = BASEDIR.'themes';
// Define the absolute paths for configured directories
define('APPPATH', realpath($application).DS);
define('MODPATH', realpath($modules).DS);
define('SYSPATH', realpath($system).DS);
define('THEMEPATH', realpath($themes).DS);
//
unset( $application, $modules, $system );

// Load the core smvc class
require SYSPATH.'classes/smvc/core.php';

if (is_file(APPPATH.'classes/smvc.php'))
{
	// Application extends the core?
	require APPPATH.'classes/smvc.php';
}
else
{
	// Load empty core extension
	require SYSPATH.'classes/smvc.php';
}

// Bootstrap
require APPPATH.'bootstrap.php';
