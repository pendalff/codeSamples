<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
return array(
	'default' => array(
		'cache' => false,		
	),
	'javascript'   => array(
		'directory' => 'assets/js',
		'compress'  => false,
		'compress_config' => array(
									'type'=>'jsmin'
									),
		'use_preprocessor' => false,
		'gzip' => false,
		'gzip_compression' => 8,
		'cache' => false,
		
	),
	'css'  => array(
		'directory' => 'assets/css',
		'gzip' => false,
		'compress' =>false,
		'compress_config' => array(
				 	'type' => 'strip',
					),
		'cache' => false,	
	),
	'glue' => array(
		'gzip' => false,	
		'cache' => false
	)
);
