<?php defined('SYSPATH') OR die('No direct access allowed.');

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
			'dsn'        => 'mysql:host=localhost;dbname=test',
			'username'   => 'test',
			'password'   => 'test',
			'persistent' => FALSE,
		),
		'table_prefix' => '',
		'charset'      => 'utf8',
	),
	'alternate' => array(
		'type'       => 'mysql',
		'connection' => array(
			/**
			 * The following options are available for MySQL:
			 *
			 * string   hostname
			 * integer  port
			 * string   socket
			 * string   username
			 * string   password
			 * boolean  persistent
			 * string   database
			 */
			'hostname'   => 'localhost',
			'username'   => FALSE,
			'password'   => FALSE,
			'persistent' => FALSE,
			'database'   => 'test',
		),
		'table_prefix' => '',
		'charset'      => 'utf8',
	),
);