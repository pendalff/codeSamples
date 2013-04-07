<?php defined('SYSPATH') or die('No direct script access.');

Route::set('assets/jsglue', 'assets/jsglue/<file>', 
			array(
				'file' => '.+',
			)
		)
		->defaults(array(
			'directory'  => 'assets',
			'controller' => 'glue',
			'action'     => 'process',
			'type'       => 'js'
		));

Route::set('assets/cssglue', 'assets/cssglue/<file>', array('file' => '.+'))
	->defaults(array(
		'directory'  => 'assets',
		'controller' => 'glue',
		'action'     => 'process',
		'type'       => 'css'
	));

Route::set('assets/js', 'assets/js/<file>', array('file' => '.+'))
	->defaults(array(
		'directory'  => 'assets',
		'controller' => 'javascript',
		'action'     => 'process',
		'file'       => NULL
	));

Route::set('assets/css', 'assets/css/<file>', array('file' => '.+'))
	->defaults(array(
		'directory'  => 'assets',
		'controller' => 'css',
		'action'     => 'process',
		'file'       => NULL
	));
