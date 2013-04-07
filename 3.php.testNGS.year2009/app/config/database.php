<?php defined('SYSPATH') OR die('No direct access allowed.');
$conf = SMVC_Config::instance()->load('forum');
return array
(
	'default' => array
	(

		'type'       => 'pdo',
		'connection' => array(
			/**
			 * The following options are available for PDO:
			 *
			 * string   dsn
			 * string   username
			 * string   password
			 * boolean  persistent
			 */
			'dsn'        => 'mysql:host='.$conf['host'].';dbname='.$conf['dbname'],
			'username'   => $conf['username'],
			'password'   => $conf['password'],
			'persistent' => FALSE,
		),
		'database'   => 'ngs',
		'table_prefix' => '',
		'charset'      => 'utf8',
	),
);