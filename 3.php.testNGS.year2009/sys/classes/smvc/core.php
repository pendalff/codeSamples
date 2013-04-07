<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Translation/internationalization function.
 *
 *    __('Welcome back, :user', array(':user' => $username));
 *
 * @param   string  text to translate
 * @param   array   values to replace in the translated text
 * @return  string
 */
function __($string, array $values = NULL, $lang = 'en')
{
	if ($lang !== I18n::$lang)
	{
		// The message and target languages are different
		// Get the translation for this message
		$string = I18n::get($string);
	}

	return empty($values) ? $string : strtr($string, $values);
}

/**
 * Low-level helpers methods:
 */
class SMVC_Core {

	// Environment constants
	const PRODUCTION  = 'production';
	const STAGING     = 'staging';
	const TESTING     = 'testing';
	const DEVELOPMENT = 'development';
    
	const FILE_EXTENTION = '.php';
	
	/**
	 * @var  array  PHP error code => human readable name
	 */
	public static $php_errors = array(
		E_ERROR              => 'Fatal Error',
		E_USER_ERROR         => 'User Error',
		E_PARSE              => 'Parse Error',
		E_WARNING            => 'Warning',
		E_USER_WARNING       => 'User Warning',
		E_STRICT             => 'Strict',
		E_NOTICE             => 'Notice',
		E_RECOVERABLE_ERROR  => 'Recoverable Error',
	);

	/**
	 * @var  string  current environment name
	 */
	public static $environment = SMVC::DEVELOPMENT;

	/**
	 * @var  boolean  magic quotes enabled?
	 */
	public static $magic_quotes = FALSE;

	/**
	 * @var  string  character set of input and output
	 */
	public static $charset = 'utf-8';

	/**
	 * @var  string  base URL to the application
	 */
	public static $base_url = '/';

	/**
	 * @var  string  application index file
	 */
	public static $index_file = 'index.php';

	/**
	 * @var  string  cache directory
	 */
	public static $cache_dir;

	/**
	 * @var  boolean  enabling internal caching?
	 */
	public static $caching = FALSE;
	
	/**
	 * @var  boolean  enable error handling?
	 */
	public static $errors = TRUE;

	/**
	 * @var  object  config object
	 */
	public static $config;

	// Is the environment initialized?
	protected static $_init = FALSE;

	// Currently active modules
	protected static $_modules = array();

	// Include paths that are used to find files
	public static $_paths = array(APPPATH, SYSPATH);

	// File path cache
	protected static $_files = array();

	private function __construct()
	{
		// This is a static class
	}
	
	/**
	 * Initializes the environment:
	 *
	 * - Disables register_globals and magic_quotes_gpc
	 * - Determines the current environment
	 * - Set global settings
	 * - Sanitizes GET, POST, and COOKIE variables
	 * Any of the global settings can be set here:
	 *
	 * Type      | Setting    | Description                                    | Default Value
	 * ----------|------------|------------------------------------------------|---------------
	 * `boolean` | errors     | use internal error and exception handling?     | `TRUE`
	 * `boolean` | caching    | cache the location of files between requests?  | `FALSE`
	 * `string`  | charset    | character set used for all input and output    | `"utf-8"`
	 * `string`  | base_url   | set the base URL for the application           | `"/"`
	 * `string`  | index_file | set the index.php file name                    | `"index.php"`
	 * `string`  | cache_dir  | set the cache directory path                   | `APPPATH."cache"`
	 *
	 * @throws  SMVC_Exception
	 * @param   array   global settings
	 * @return  void
	 */
	public static function init(array $settings = NULL)
	{
		if (SMVC::$_init)
		{
			// Do not allow execution twice
			return;
		}

		// SMVC is now initialized
		SMVC::$_init = TRUE;
				
		// Load the config
		SMVC::$config = SMVC_Config::instance();

		// Start an output buffer
		ob_start();

		if (isset($settings['errors']))
		{
			// Enable error handling
			SMVC::$errors = (bool) $settings['errors'];
		}

		if (SMVC::$errors === TRUE)
		{
			// Enable SMVC exception handling, adds stack traces and error source.
			set_exception_handler(array('SMVC', 'exception_handler'));

			// Enable SMVC error handling, converts all PHP errors to exceptions.
			set_error_handler(array('SMVC', 'error_handler'));
		}

		// Enable the SMVC shutdown handler
		register_shutdown_function(array('SMVC', 'shutdown_handler'));

		if (ini_get('register_globals'))
		{
			if (isset($_REQUEST['GLOBALS']) OR isset($_FILES['GLOBALS']))
			{
				// Prevent malicious GLOBALS overload attack
				echo "Global variable overload attack detected! Request aborted.\n";
				// Exit with an error status
				exit(1);
			}
			// Get the variable names of all globals
			$global_variables = array_keys($GLOBALS);
			// Remove the standard global variables from the list
			$global_variables = array_diff($global_variables,
				array('GLOBALS', '_REQUEST', '_GET', '_POST', '_FILES', '_COOKIE', '_SERVER', '_ENV', '_SESSION'));
			foreach ($global_variables as $name)
			{
				// Retrieve the global variable and make it null
				global $$name;
				$$name = NULL;

				// Unset the global variable, effectively disabling register_globals
				unset($GLOBALS[$name], $$name);
			}
		}
		if (isset($settings['cache_dir']))
		{
			// Set the cache directory path
			SMVC::$cache_dir = realpath($settings['cache_dir']);
		}
		else
		{
			// Use the default cache directory
			SMVC::$cache_dir = APPPATH.'cache';
		}

		if ( ! is_writable(SMVC::$cache_dir) )
		{
			throw new SMVC_Exception('Directory :dir must be writable',
				array(':dir' => SMVC::debug_path(SMVC::$cache_dir)));
		}
		if ( ! is_writable(APPPATH.'config') )
		{
			throw new SMVC_Exception('Directory :dir must be writable',
				array(':dir' => SMVC::debug_path(APPPATH.'config')));
		}

		if (isset($settings['charset']))
		{
			// Set the system character set
			SMVC::$charset = strtolower($settings['charset']);
		}

		if (isset($settings['base_url']))
		{
			// Set the base URL
			SMVC::$base_url = rtrim($settings['base_url'], '/').'/';
		}

		if (isset($settings['index_file']))
		{
			// Set the index file
			SMVC::$index_file = trim($settings['index_file'], '/');
		}

		// Determine if the extremely evil magic quotes are enabled
		SMVC::$magic_quotes = (bool) get_magic_quotes_gpc();

		// Sanitize all request variables
		$_GET    = SMVC::sanitize($_GET);
		$_POST   = SMVC::sanitize($_POST);
		$_COOKIE = SMVC::sanitize($_COOKIE);
	}

	/**
	 * Cleans up the environment:
	 *
	 * - Restore the previous error and exception handlers
	 * - Destroy the SMVC::$config objects
	 *
	 * @return  void
	 */
	public static function deinit()
	{
		if (SMVC::$_init)
		{
			// Removed the autoloader
			spl_autoload_unregister(array('SMVC', 'auto_load'));

			if (SMVC::$errors)
			{
				// Go back to the previous error handler
				restore_error_handler();

				// Go back to the previous exception handler
				restore_exception_handler();
			}

			// Destroy objects created by init
			SMVC::$config = NULL;

			// Reset internal storage
			SMVC::$_modules = SMVC::$_files = array();
			SMVC::$_paths   = array(APPPATH, SYSPATH);

			// Reset file cache status
			SMVC::$_files_changed = FALSE;

			// SMVC is no longer initialized
			SMVC::$_init = FALSE;
		}
	}

	/**
	 * Recursively sanitizes an input variable:
	 *
	 * - Strips slashes if magic quotes are enabled
	 * - Normalizes all newlines to LF
	 *
	 * @param   mixed  any variable
	 * @return  mixed  sanitized variable
	 */
	public static function sanitize($value)
	{
		if (is_array($value) OR is_object($value))
		{
			foreach ($value as $key => $val)
			{
				// Recursively clean each value
				$value[$key] = SMVC::sanitize($val);
			}
		}
		elseif (is_string($value))
		{
			if (SMVC::$magic_quotes === TRUE)
			{
				// Remove slashes added by magic quotes
				$value = stripslashes($value);
			}

			if (strpos($value, "\r") !== FALSE)
			{
				// Standardize newlines
				$value = str_replace(array("\r\n", "\r"), "\n", $value);
			}
		}

		return $value;
	}

	/**
	 * Provides auto-loading support of SMVC classes, as well as transparent
	 * extension of classes that have a _Core suffix.
	 *
	 * Class names are converted to file names by making the class name
	 * lowercase and converting underscores to slashes:
	 *
	 *     // Loads classes/my/class/name.php
	 *     SMVC::auto_load('My_Class_Name');
	 *
	 * @param   string   class name
	 * @return  boolean
	 */
	public static function auto_load($class)
	{
		// Transform the class name into a path
		$file = str_replace('_', '/', strtolower($class));

		if ($path = SMVC::find_file('classes', $file))
		{
			// Load the class file
			require $path;

			// Class has been found
			return TRUE;
		}

		// Class is not in the filesystem
		return FALSE;
	}

	/**
	 * Changes the currently enabled modules. Module paths may be relative
	 * or absolute, but must point to a directory:
	 *
	 *     SMVC::modules(array('modules/foo', MODPATH.'bar'));
	 *
	 * @param   array  list of module paths
	 * @return  array  enabled modules
	 */
	public static function modules(array $modules = NULL)
	{
		if ($modules === NULL)
			return SMVC::$_modules;

		// Start a new list of include paths, APPPATH first
		$paths = array(APPPATH);

		foreach ($modules as $name => $path)
		{
			if (is_dir($path))
			{
				// Add the module to include paths
				$paths[] = realpath($path).DS;
			}
			else
			{
				// This module is invalid, remove it
				unset($modules[$name]);
			}
		}

		// Finish the include paths by adding SYSPATH
		$paths[] = SYSPATH;

		// Set the new include paths
		SMVC::$_paths = $paths;

		// Set the current module list
		SMVC::$_modules = $modules;

		foreach (SMVC::$_modules as $path)
		{
			$init = $path.DS.'init'.self::FILE_EXTENTION;

			if (is_file($init))
			{
				// Include the module initialization file once
				require_once $init;
			}
		}

		return SMVC::$_modules;
	}

	/**
	 * Returns the the currently active include paths, including the
	 * application and system paths.
	 *
	 * @return  array
	 */
	public static function include_paths()
	{
		return SMVC::$_paths;
	}

	/**
	 * Finds the path of a file by directory, filename, and extension.
	 * If no extension is given, the default self::FILE_EXTENTION extension will be used.
	 *
	 *     // Returns an absolute path to views/template.php
	 *     SMVC::find_file('views', 'template');
	 *
	 *     // Returns an absolute path to media/css/style.css
	 *     SMVC::find_file('media', 'css/style', 'css');
	 *
	 * @param   string   directory name (views, i18n, classes, extensions, etc.)
	 * @param   string   filename with subdirectory
	 * @param   string   extension to search for
	 * @param   boolean  return an array of files?
	 * @return  array    a list of files when $array is TRUE
	 * @return  string   single file path
	 */
	public static function find_file($dir, $file, $ext = NULL, $array = FALSE)
	{

		// Use the defined extension by default
		if($ext === NULL){
			$ext = self::FILE_EXTENTION;
		}elseif($ext === false){
			$ext = '';
		}
		else{
			$ext = '.'.$ext;
		}

		// Create a partial path of the filename
		$path = $dir.DS.$file.$ext;
			

		if (SMVC::$caching === TRUE AND isset(SMVC::$_files[$path]))
		{
			// This path has been cached
			return SMVC::$_files[$path];
		}

		if ($array OR $dir === 'config' OR $dir === 'i18n')
		{
			// Include paths must be searched in reverse
			$paths = array_reverse(SMVC::$_paths);

			// Array of files that have been found
			$found = array();

			foreach ($paths as $dir)
			{
				if (is_file($dir.$path))
				{
					// This path has a file, add it to the list
					$found[] = $dir.$path;
				}
			}
		}
		else
		{
			// The file has not been found yet
			$found = FALSE;

			foreach (SMVC::$_paths as $dir)
			{   		

				if (is_file($dir.$path))
				{ 
					// A path has been found
					$found = $dir.$path;

					// Stop searching
					break;
				}
			}
		}

		if (SMVC::$caching === TRUE)
		{
			// Add the path to the cache
			SMVC::$_files[$path] = $found;
		}

		return $found;
	}

	/**
	 * Recursively finds all of the files in the specified directory.
	 *
	 *     $views = SMVC::list_files('views');
	 *
	 * @param   string  directory name
	 * @param   array   list of paths to search
	 * @return  array
	 */
	public static function list_files($directory = NULL, array $paths = NULL)
	{
		if ($directory !== NULL)
		{
			// Add the directory separator
			$directory .= DS;
		}

		if ($paths === NULL)
		{
			// Use the default paths
			$paths = SMVC::$_paths;
		}

		// Create an array for the files
		$found = array();

		foreach ($paths as $path)
		{
			if (is_dir($path.$directory))
			{
				// Create a new directory iterator
				$dir = new DirectoryIterator($path.$directory);

				foreach ($dir as $file)
				{
					// Get the file name
					$filename = $file->getFilename();

					if ($filename[0] === '.' OR $filename[strlen($filename)-1] === '~')
					{
						// Skip all hidden files and UNIX backup files
						continue;
					}

					// Relative filename is the array key
					$key = $directory.$filename;

					if ($file->isDir())
					{
						if ($sub_dir = SMVC::list_files($key, $paths))
						{
							if (isset($found[$key]))
							{
								// Append the sub-directory list
								$found[$key] += $sub_dir;
							}
							else
							{
								// Create a new sub-directory list
								$found[$key] = $sub_dir;
							}
						}
					}
					else
					{
						if ( ! isset($found[$key]))
						{
							// Add new files to the list
							$found[$key] = realpath($file->getPathName());
						}
					}
				}
			}
		}

		// Sort the results alphabetically
		ksort($found);

		return $found;
	}

	/**
	 * Loads a file within a totally empty scope and returns the output:
	 *
	 *     $foo = SMVC::load('foo.php');
	 *
	 * @param   string
	 * @return  mixed
	 */
	public static function load($file)
	{
		return include $file;
	}

	/**
	 * Creates a new configuration object for the requested group.
	 *
	 * @param   string   group name
	 * @return  SMVC_Config
	 */
	public static function config($group)
	{
		static $config;

		if (strpos($group, '.') !== FALSE)
		{
			// Split the config group and path
			list ($group, $path) = explode('.', $group, 2);
		}

		if ( ! isset($config[$group]))
		{
			// Load the config group into the cache
			$config[$group] = SMVC::$config->load($group);
		}

		if (isset($path))
		{
			return Arr::path($config[$group], $path);
		}
		else
		{
			return $config[$group];
		}
	}

	/**
	 * PHP error handler, converts all errors into ErrorExceptions. This handler
	 * respects error_reporting settings.
	 *
	 * @throws  ErrorException
	 * @return  TRUE
	 */
	public static function error_handler($code, $error, $file = NULL, $line = NULL)
	{
		if (error_reporting() & $code)
		{
			// This error is not suppressed by current error reporting settings
			// Convert the error into an ErrorException
			throw new ErrorException($error, $code, 0, $file, $line);
		}

		// Do not execute the PHP error handler
		return TRUE;
	}

	/**
	 * Inline exception handler, displays the error message, source of the
	 * exception, and the stack trace of the error.
	 *
	 * @uses    ::exception_text
	 * @param   object   exception object
	 * @return  boolean
	 */
	public static function exception_handler(Exception $e)
	{
		try
		{
			// Get the exception information
			$type    = get_class($e);
			$code    = $e->getCode();
			$message = $e->getMessage();
			$file    = $e->getFile();
			$line    = $e->getLine();

			// Create a text version of the exception
			$error = SMVC::exception_text($e);
			// Get the exception backtrace
			$trace = $e->getTrace();

			if ($e instanceof ErrorException)
			{
				if (isset(SMVC::$php_errors[$code]))
				{
					// Use the human-readable error name
					$code = SMVC::$php_errors[$code];
				}

				if (version_compare(PHP_VERSION, '5.3', '<'))
				{
					// Workaround for a bug in ErrorException::getTrace() that exists in
					// all PHP 5.2 versions. @see http://bugs.php.net/bug.php?id=45895
					for ($i = count($trace) - 1; $i > 0; --$i)
					{
						if (isset($trace[$i - 1]['args']))
						{
							// Re-position the args
							$trace[$i]['args'] = $trace[$i - 1]['args'];

							// Remove the args
							unset($trace[$i - 1]['args']);
						}
					}
				}
			}

			if ( ! headers_sent())
			{
				// Make sure the proper content type is sent with a 500 status
				header('Content-Type: text/html; charset='.SMVC::$charset, TRUE, 500);
			}

			// Start an output buffer
			ob_start();

			// Include the exception HTML
			include SMVC::find_file('views', 'smvc/error');

			// Display the contents of the output buffer
			echo ob_get_clean();

			return TRUE;
		}
		catch (Exception $e)
		{
			// Clean the output buffer if one exists
			ob_get_level() and ob_clean();

			// Display the exception text
			echo SMVC::exception_text($e), "\n";

			// Exit with an error status
			exit(1);
		}
	}

	/**
	 * Catches errors that are not caught by the error handler, such as E_PARSE.
	 *
	 * @uses    SMVC::exception_handler
	 * @return  void
	 */
	public static function shutdown_handler()
	{
		if ( ! SMVC::$_init)
		{
			// Do not execute when not active
			return;
		}

		if (SMVC::$errors AND $error = error_get_last() )
		{
			// Clean the output buffer
			ob_get_level() and ob_clean();

			// Fake an exception for nice debugging
			SMVC::exception_handler(new ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));

			// Shutdown now to avoid a "death loop"
			exit(1);
		}
	}

	/**
	 * Get a single line of text representing the exception:
	 *
	 * Error [ Code ]: Message ~ File [ Line ]
	 *
	 * @param   object  Exception
	 * @return  string
	 */
	public static function exception_text(Exception $e)
	{
		return sprintf('%s [ %s ]: %s ~ %s [ %d ]',
			get_class($e), $e->getCode(), strip_tags($e->getMessage()), SMVC::debug_path($e->getFile()), $e->getLine());
	}

	/**
	 * Returns an HTML string of debugging information about any number of
	 * variables, each wrapped in a "pre" tag:
	 *
	 *     // Displays the type and value of each variable
	 *     echo SMVC::debug($foo, $bar, $baz);
	 *
	 * @param   mixed   variable to debug
	 * @param   ...
	 * @return  string
	 */
	public static function debug()
	{
		if (func_num_args() === 0)
			return;

		// Get all passed variables
		$variables = func_get_args();

		$output = array();
		foreach ($variables as $var)
		{
			$output[] = SMVC::_dump($var, 1024);
		}

		return '<pre class="debug">'.implode("\n", $output).'</pre>';
	}

	/**
	 * Returns an HTML string of information about a single variable.
	 *
	 * Borrows heavily on concepts from the Debug class of [Nette](http://nettephp.com/).
	 *
	 * @param   mixed    variable to dump
	 * @param   integer  maximum length of strings
	 * @return  string
	 */
	public static function dump($value, $length = 128)
	{
		return SMVC::_dump($value, $length);
	}

	/**
	 * Helper for SMVC::dump(), 
	 * handles recursion in arrays and objects.
	 *
	 * @param   mixed    variable to dump
	 * @param   integer  maximum length of strings
	 * @param   integer  recursion level (internal)
	 * @return  string
	 */
	protected static function _dump( & $var, $length = 128, $level = 0)
	{
		if ($var === NULL)
		{
			return '<small>NULL</small>';
		}
		elseif (is_bool($var))
		{
			return '<small>bool</small> '.($var ? 'TRUE' : 'FALSE');
		}
		elseif (is_float($var))
		{
			return '<small>float</small> '.$var;
		}
		elseif (is_resource($var))
		{
			if (($type = get_resource_type($var)) === 'stream' AND $meta = stream_get_meta_data($var))
			{
				$meta = stream_get_meta_data($var);

				if (isset($meta['uri']))
				{
					$file = $meta['uri'];

					if (function_exists('stream_is_local'))
					{
						// Only exists on PHP >= 5.2.4
						if (stream_is_local($file))
						{
							$file = SMVC::debug_path($file);
						}
					}

					return '<small>resource</small><span>('.$type.')</span> '.htmlspecialchars($file, ENT_NOQUOTES, SMVC::$charset);
				}
			}
			else
			{
				return '<small>resource</small><span>('.$type.')</span>';
			}
		}
		elseif (is_string($var))
		{
			if (strlen($var) > $length)
			{
				// Encode the truncated string
				$str = htmlspecialchars(substr($var, 0, $length), ENT_NOQUOTES, SMVC::$charset).'&nbsp;&hellip;';
			}
			else
			{
				// Encode the string
				$str = htmlspecialchars($var, ENT_NOQUOTES, SMVC::$charset);
			}

			return '<small>string</small><span>('.strlen($var).')</span> "'.$str.'"';
		}
		elseif (is_array($var))
		{
			$output = array();

			// Indentation for this variable
			$space = str_repeat($s = '    ', $level);

			static $marker;

			if ($marker === NULL)
			{
				// Make a unique marker
				$marker = uniqid("\x00");
			}

			if (empty($var))
			{
				// Do nothing
			}
			elseif (isset($var[$marker]))
			{
				$output[] = "(\n$space$s*RECURSION*\n$space)";
			}
			elseif ($level < 5)
			{
				$output[] = "<span>(";

				$var[$marker] = TRUE;
				foreach ($var as $key => & $val)
				{
					if ($key === $marker) continue;
					if ( ! is_int($key))
					{
						$key = '"'.htmlspecialchars($key, ENT_NOQUOTES, self::$charset).'"';
					}

					$output[] = "$space$s$key => ".SMVC::_dump($val, $length, $level + 1);
				}
				unset($var[$marker]);

				$output[] = "$space)</span>";
			}
			else
			{
				// Depth too great
				$output[] = "(\n$space$s...\n$space)";
			}

			return '<small>array</small><span>('.count($var).')</span> '.implode("\n", $output);
		}
		elseif (is_object($var))
		{
			// Copy the object as an array
			$array = (array) $var;

			$output = array();

			// Indentation for this variable
			$space = str_repeat($s = '    ', $level);

			$hash = spl_object_hash($var);

			// Objects that are being dumped
			static $objects = array();

			if (empty($var))
			{
				// Do nothing
			}
			elseif (isset($objects[$hash]))
			{
				$output[] = "{\n$space$s*RECURSION*\n$space}";
			}
			elseif ($level < 10)
			{
				$output[] = "<code>{";

				$objects[$hash] = TRUE;
				foreach ($array as $key => & $val)
				{
					if ($key[0] === "\x00")
					{
						// Determine if the access is protected or protected
						$access = '<small>'.($key[1] === '*' ? 'protected' : 'private').'</small>';

						// Remove the access level from the variable name
						$key = substr($key, strrpos($key, "\x00") + 1);
					}
					else
					{
						$access = '<small>public</small>';
					}

					$output[] = "$space$s$access $key => ".SMVC::_dump($val, $length, $level + 1);
				}
				unset($objects[$hash]);

				$output[] = "$space}</code>";
			}
			else
			{
				// Depth too great
				$output[] = "{\n$space$s...\n$space}";
			}

			return '<small>object</small> <span>'.get_class($var).'('.count($array).')</span> '.implode("\n", $output);
		}
		else
		{
			return '<small>'.gettype($var).'</small> '.htmlspecialchars(print_r($var, TRUE), ENT_NOQUOTES, SMVC::$charset);
		}
	}

	/**
	 * Useful for display a shorter path.
	 *
	 * echo SMVC::debug_path(SMVC::find_file('classes', 'SMVC'));
	 *
	 * @param   string  path to debug
	 * @return  string
	 */
	public static function debug_path($file)
	{
		if (strpos($file, APPPATH) === 0)
		{
			$file = 'APPPATH/'.substr($file, strlen(APPPATH));
		}
		elseif (strpos($file, SYSPATH) === 0)
		{
			$file = 'SYSPATH/'.substr($file, strlen(SYSPATH));
		}
		elseif (strpos($file, MODPATH) === 0)
		{
			$file = 'MODPATH/'.substr($file, strlen(MODPATH));
		} 
		elseif (strpos($file, BASEDIR) === 0)
		{
			$file = 'BASEDIR/'.substr($file, strlen(BASEDIR));
		}

		return $file;
	}

	/**
	 * Returns an HTML string, highlighting a specific line of a file, with some
	 * number of lines padded above and below.
	 *
	 *     // Highlights the current line of the current file
	 *     echo SMVC::debug_source(__FILE__, __LINE__);
	 *
	 * @param   string   file to open
	 * @param   integer  line number to highlight
	 * @param   integer  number of padding lines
	 * @return  string   source of file
	 * @return  FALSE    file is unreadable
	 */
	public static function debug_source($file, $line_number, $padding = 5)
	{
		if ( ! $file OR ! is_readable($file))
		{
			// Continuing will cause errors
			return FALSE;
		}

		// Open the file and set the line position
		$file = fopen($file, 'r');
		$line = 0;

		// Set the reading range
		$range = array('start' => $line_number - $padding, 'end' => $line_number + $padding);

		// Set the zero-padding amount for line numbers
		$format = '% '.strlen($range['end']).'d';

		$source = '';
		while (($row = fgets($file)) !== FALSE)
		{
			// Increment the line number
			if (++$line > $range['end'])
				break;

			if ($line >= $range['start'])
			{
				// Make the row safe for output
				$row = htmlspecialchars($row, ENT_NOQUOTES, SMVC::$charset);

				// Trim whitespace and sanitize the row
				$row = '<span class="number">'.sprintf($format, $line).'</span> '.$row;

				if ($line === $line_number)
				{
					// Apply highlighting to this row
					$row = '<span class="line highlight">'.$row.'</span>';
				}
				else
				{
					$row = '<span class="line">'.$row.'</span>';
				}

				// Add to the captured source
				$source .= $row;
			}
		}

		// Close the file
		fclose($file);

		return '<pre class="source"><code>'.$source.'</code></pre>';
	}

	/**
	 * Returns an array of HTML strings that represent each step in the backtrace.
	 *
	 *     // Displays the entire current backtrace
	 *     echo implode('<br/>', SMVC::trace());
	 *
	 * @param   string  path to debug
	 * @return  string
	 */
	public static function trace(array $trace = NULL)
	{
		if ($trace === NULL)
		{
			// Start a new trace
			$trace = debug_backtrace();
		}

		// Non-standard function calls
		$statements = array('include', 'include_once', 'require', 'require_once');

		$output = array();
		foreach ($trace as $step)
		{
			if ( ! isset($step['function']))
			{
				// Invalid trace step
				continue;
			}

			if (isset($step['file']) AND isset($step['line']))
			{
				// Include the source of this step
				$source = SMVC::debug_source($step['file'], $step['line']);
			}

			if (isset($step['file']))
			{
				$file = $step['file'];

				if (isset($step['line']))
				{
					$line = $step['line'];
				}
			}

			// function()
			$function = $step['function'];

			if (in_array($step['function'], $statements))
			{
				if (empty($step['args']))
				{
					// No arguments
					$args = array();
				}
				else
				{
					// Sanitize the file path
					$args = array($step['args'][0]);
				}
			}
			elseif (isset($step['args']))
			{
				if (strpos($step['function'], '{closure}') !== FALSE)
				{
					// Introspection on closures in a stack trace is impossible
					$params = NULL;
				}
				else
				{
					if (isset($step['class']))
					{
						if (method_exists($step['class'], $step['function']))
						{
							$reflection = new ReflectionMethod($step['class'], $step['function']);
						}
						else
						{
							$reflection = new ReflectionMethod($step['class'], '__call');
						}
					}
					else
					{
						$reflection = new ReflectionFunction($step['function']);
					}

					// Get the function parameters
					$params = $reflection->getParameters();
				}

				$args = array();

				foreach ($step['args'] as $i => $arg)
				{
					if (isset($params[$i]))
					{
						// Assign the argument by the parameter name
						$args[$params[$i]->name] = $arg;
					}
					else
					{
						// Assign the argument by number
						$args[$i] = $arg;
					}
				}
			}

			if (isset($step['class']))
			{
				// Class->method() or Class::method()
				$function = $step['class'].$step['type'].$step['function'];
			}

			$output[] = array(
				'function' => $function,
				'args'     => isset($args)   ? $args : NULL,
				'file'     => isset($file)   ? $file : NULL,
				'line'     => isset($line)   ? $line : NULL,
				'source'   => isset($source) ? $source : NULL,
			);

			unset($function, $args, $file, $line, $source);
		}

		return $output;
	}


} // End SMVC
