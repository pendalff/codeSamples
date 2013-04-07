<?php defined('SYSPATH') or die('No direct script access.'); 

/**
 * Set the default time zone.
 * @see  http://php.net/timezones
 */
date_default_timezone_set('Asia/Novosibirsk');

/**
 * Set the default locale.
 * @see  http://php.net/setlocale
 */
setlocale(LC_ALL, 'ru_RU.utf-8');

/**
 * Enable auto-loader.
 * @see  http://php.net/spl_autoload_register
 */
spl_autoload_register(array('SMVC', 'auto_load'));

/**
 * Enable the auto-loader for unserialization.
 * @see  http://php.net/spl_autoload_call
 * @see  http://php.net/manual/var.configuration.php#unserialize-callback-func
 */
ini_set('unserialize_callback_func', 'spl_autoload_call');

/**
 * - string   base_url    NULL
 * - string   index_file  index.php
 * - string   charset     utf-8
 * - string   cache_dir   APPPATH/cache
 * - boolean  errors      TRUE
 * - boolean  caching     FALSE
 */
$_init=array(
				  'base_url' => '/',
				  'errors'=>TRUE
				  );
				  
SMVC::init($_init);

/**
 * Attach a file reader to config.
 */
SMVC::$config->attach(new SMVC_Config_File);

/**
 * Enable modules.
 */
SMVC::modules(array(
				'database'    		=> MODPATH.'database',
				'application'		=> MODPATH.'app',
				'assets'	  		=> MODPATH.'asset',
				'assets_files'	  	=> THEMEPATH.'default',
				'session'		    => MODPATH.'session',
				'pagination'		=> MODPATH.'pagination',
			));
/**
 * Set the routes. 
 */

Route::set('forum_edit', 'forum/<forum>/<action>(/<id>)',
	array('forum' => '\d+')
	)
	->defaults(array(
		'controller' => 'forum',
		'action'     => 'list'
	));
	
Route::set('forum', '(forum(/<action>(/<id>)(/page<page>)))')
	->defaults(array(
		'controller' => 'forum',
		'action'     => 'list'
	));
Route::set('config', 'config(/<action>)')
	->defaults(array(
		'controller' => 'config',
		'action'     => 'index',
		'install'    => false
	));	
Route::set('default', '(<controller>(/<action>(/<id>)(/page<page>)))')
	->defaults(array(
		'controller' => 'welcome',
		'action'     => 'index'
	));

i18n::$lang = 'ru';

	/**
	 * Execute the main request. 
	 */
	if(!is_writable(APPPATH.'config'.DIRECTORY_SEPARATOR."forum".SMVC::FILE_EXTENTION)){
			throw new SMVC_Exception('File :file must be writable',
				array(':file' => SMVC::debug_path(APPPATH.'config'.DIRECTORY_SEPARATOR."forum".SMVC::FILE_EXTENTION)));		
	}
	$request = Request::instance();

	if(!file_exists(APPPATH.'installed') && $request->controller!='glue'){
		
			Route::get('config')->defaults(array(
				'controller' => 'config',
				'action'     => 'index',
				'install'    => true
			));	
			$request =	Request::factory('config/index');
	}
	
	try{
		//Before Exec
		$request->execute();
		//After Exec
	}
	catch (SMVC_Exception404 $e)
	{
	    $request = Request::factory('error/404');
		$request->e = $e;
		$request->execute();
	}
	catch (SMVC_Exception403 $e)
	{
	    $request = Request::factory('error/403');
		$request->e = $e;
		$request->execute();
	}
	/*
	catch (PDOException $e)
	{
	    $request = Request::factory('error/404');
		$request->e = $e;
		$request->execute();
	}
	*/
	catch (ReflectionException $e)
	{
	    $request = Request::factory('error/404');
		$request->e = $e;
		$request->execute();
	}
	catch (SMVC_Request_Exception $e)
	{
	    $request = Request::factory('error/404');
		$request->e = $e;
		$request->execute();
	}
	catch (Exception $e)
	{
	    if ( SMVC::$environment != SMVC::PRODUCTION )
	    {
	        throw $e;
	    }
		
	    $request = Request::factory('error/500');
		$request->e = $e;
		$request->execute();
	}
	//Before Render
	echo $request->send_headers()->response;
