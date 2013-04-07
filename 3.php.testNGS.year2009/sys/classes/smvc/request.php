<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Request wrapper. Uses the [Route] class to determine what
 * [Controller] to send the request to.
 */
class SMVC_Request {

	// HTTP status codes and messages
	public static $messages = array(

		// Success 2xx
		200 => 'OK',
		// Redirection 3xx
		301 => 'Moved Permanently',
		302 => 'Found',
		304 => 'Not Modified',
		// Client Error 4xx
		400 => 'Bad Request',
		403 => 'Forbidden',
		404 => 'Not Found',
		// Server Error 5xx
		500 => 'Internal Server Error'
	);

	/**
	 * @var  string  method: GET, POST, PUT, DELETE, etc
	 */
	public static $method = 'GET';

	/**
	 * @var  string  protocol: http, https, ftp, cli, etc
	 */
	public static $protocol = 'http';


	/**
	 * @var  boolean  AJAX-generated request
	 */
	public static $is_ajax = FALSE;

	/**
	 * @var  object  main request instance
	 */
	public static $instance;

	/**
	 * @var  object  currently executing request instance
	 */
	public static $current;

	/**
	 * @var  object  route matched for this request
	 */
	public $route;

	/**
	 * @var  integer  HTTP response code: 200, 404, 500, etc
	 */
	public $status = 200;

	/**
	 * @var  string  response body
	 */
	public $response = NULL;

	/**
	 * @var  array  headers to send with the response body
	 */
	public $headers = array();

	/**
	 * @var  string  controller directory
	 */
	public $directory = '';

	/**
	 * @var  string  controller to be executed
	 */
	public $controller;

	/**
	 * @var  string  action to be executed in the controller
	 */
	public $action;

	/**
	 * @var  string  the URI of the request
	 */
	public $uri;

	/**
	 * @var array Parameters extracted from the route
	 */
	
	protected $_params;


	/**
	 * Creates a new request object for the given URI. 
	 * @param   string  URI of the request
	 * @return  Request
	 */
	public static function factory($uri)
	{
		return new Request($uri);
	}
	/**
	 * Return the currently executing request.
	 *     $request = Request::current();
	 *
	 * @return  Request
	 */
	public static function current()
	{
		return Request::$current;
	}

	/**
	 * Creates a new request object for the given URI. New requests should be
	 * created using the Request::instance or Request::factory methods.
	 *
	 * @param   string  URI of the request
	 * @return  void
	 * @throws  SMVC_Request_Exception
	 * @uses    Route::all
	 * @uses    Route::matches
	 */
	public function __construct($uri)
	{
		// Remove trailing slashes from the URI
		$uri = trim($uri, '/');

		// Load routes
		$routes = Route::all();

		foreach ($routes as $name => $route)
		{
			if ($params = $route->matches($uri))
			{

				// Store the URI
				$this->uri = $uri;

				// Store the matching route
				$this->route = $route;

				if (isset($params['directory']))
				{
					// Controllers are in a sub-directory
					$this->directory = $params['directory'];
				}

				// Store the controller
				$this->controller = $params['controller'];

				if (isset($params['action']))
				{
					// Store the action
					$this->action = $params['action'];
				}
				else
				{
					// Use the default action
					$this->action = Route::$default_action;
				}

				// These are accessible as public vars and can be overloaded
				unset($params['controller'], $params['action'], $params['directory']);

				// Params cannot be changed once matched
				$this->_params = $params;

				return;
			}
		}

		// No matching route for this URI
		$this->status = 404;

		throw new SMVC_Request_Exception('Unable to find a route to match the URI: :uri',
			array(':uri' => $uri));
	}

	/**
	 * Returns the response as the string representation of a request.
	 *
	 *     echo $request;
	 *
	 * @return  string
	 */
	public function __toString()
	{
		return (string) $this->response;
	}

	/**
	 * Main request singleton instance. 
	 *
	 * @param   string   URI of the request
	 * @return  Request
	 */
	public static function instance( & $uri = TRUE)
	{
		if ( ! Request::$instance)
	{
			if (isset($_SERVER['REQUEST_METHOD']))
			{
				// Use the server request method
				Request::$method = $_SERVER['REQUEST_METHOD'];
			}

			if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
			{
				// This request is an AJAX request
				Request::$is_ajax = TRUE;
			}

			if ($uri === TRUE)
			{
				$uri = Request::detect_uri();
			}

			// Reduce multiple slashes to a single slash
			$uri = preg_replace('#//+#', '/', $uri);

			// Remove all dot-paths from the URI, they are not valid
			$uri = preg_replace('#\.[\s./]*/#', '', $uri);

			// Create the instance singleton
			Request::$instance = Request::$current = new Request($uri);

			// Add the default Content-Type header
			Request::$instance->headers['Content-Type'] = 'text/html; charset='.SMVC::$charset;
		}

		return Request::$instance;
	}

	/**
	 * Automatically detects the URI of the main request 
	 *
	 * @return  string  URI of the main request
	 * @throws  SMVC_Exception
	 */
	public static function detect_uri()
	{
		if ( ! empty($_SERVER['PATH_INFO']))
		{
			$uri = $_SERVER['PATH_INFO'];
		}
		else
		{
			// REQUEST_URI and PHP_SELF

			if (isset($_SERVER['REQUEST_URI']))
			{
				// REQUEST_URI includes the query string, remove it
				$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

				// Decode the request URI
				$uri = rawurldecode($uri);
			}
			elseif (isset($_SERVER['PHP_SELF']))
			{
				$uri = $_SERVER['PHP_SELF'];
			}
			elseif (isset($_SERVER['REDIRECT_URL']))
			{
				$uri = $_SERVER['REDIRECT_URL'];
			}
			else
			{
				throw new SMVC_Exception('Unable to detect the URI');
			}
			
			// Gincluding the index file?
			$base_url = parse_url(SMVC::$base_url, PHP_URL_PATH);

			if (strpos($uri, $base_url) === 0)
			{
				// Remove the base URL from the URI
				$uri = substr($uri, strlen($base_url));
			}

			if (SMVC::$index_file AND strpos($uri, SMVC::$index_file) === 0)
			{
				// Remove the index file from the URI
				$uri = substr($uri, strlen(SMVC::$index_file));
			}
		}

		return $uri;
	}

	/**
	 * Generates a relative URI for the current route.
	 *
	 *     $request->uri($params);
	 *
	 * @param   array   additional route parameters
	 * @return  string
	 * @uses    Route::uri
	 */
	public function uri(array $params = NULL)
	{
		if ( ! isset($params['directory']))
		{
			// Add the current directory
			$params['directory'] = $this->directory;
		}

		if ( ! isset($params['controller']))
		{
			// Add the current controller
			$params['controller'] = $this->controller;
		}

		if ( ! isset($params['action']))
		{
			// Add the current action
			$params['action'] = $this->action;
		}

		// Add the current parameters
		$params += $this->_params;

		return $this->route->uri($params);
	}


	/**
	 * Retrieves a value from the route parameters.
	 *
	 * @param   string   key of the value
	 * @param   mixed    default value if the key is not set
	 * @return  mixed
	 */
	public function param($key = NULL, $default = NULL)
	{
		if ($key === NULL)
		{
			// Return the full array
			return $this->_params;
		}

		return isset($this->_params[$key]) ? $this->_params[$key] : $default;
	}

	/**
	 * Sends the response status and all set headers. 
	 * @return  $this
	 * @uses    Request::$messages
	 */
	public function send_headers()
	{
		if ( ! headers_sent())
		{
			if (isset($_SERVER['SERVER_PROTOCOL']))
			{
				// Use the default server protocol
				$protocol = $_SERVER['SERVER_PROTOCOL'];
			}
			else
			{
				// Default to using newer protocol
				$protocol = 'HTTP/1.1';
			}

			// HTTP status line
			header($protocol.' '.$this->status.' '.Request::$messages[$this->status]);

			foreach ($this->headers as $name => $value)
			{
				if (is_string($name))
				{
					// Combine the name and value to make a raw header
					$value = "{$name}: {$value}";
				}

				// Send the raw header
				header($value, TRUE);
			}
		}

		return $this;
	}

	/**
	 * Redirects as the request response.
	 * 
	 * @param   string   redirect location
	 * @param   integer  status code: 301, 302, etc
	 * @return  void
	 */
	public function redirect($url, $code = 302)
	{
		// Set the response status
		$this->status = $code;

		// Set the location header
		$this->headers['Location'] = $url;

		// Send headers
		$this->send_headers();

		// Stop execution
		exit;
	}

	/**
	 * Processes the request, executing the controller action 
	 *
	 * 1. Before the controller action - called Controller::before]
	 * 2. Next the controller action will be called.
	 * 3. After the controller action - called[Controller::after]	 *
	 * @return  $this
	 * @throws  SMVC_Exception
	 */
	public function execute()
	{
		// Create the class prefix
		$prefix = 'controller_';

		if ($this->directory)
		{
			// Add the directory name to the class prefix
			$prefix .= str_replace(array('\\', '/'), '_', trim($this->directory, '/')).'_';
		}

		// Store the currently active request
		$previous = Request::$current;

		// Change the current request to this request
		Request::$current = $this;

		try
		{
			// Load the controller using reflection
			$class = new ReflectionClass($prefix.$this->controller);

			if ($class->isAbstract())
			{
				throw new SMVC_Exception('Cannot create instances of abstract :controller',
					array(':controller' => $prefix.$this->controller));
			}

			// Create a new instance of the controller
			$controller = $class->newInstance($this);

			// Execute the "before action" method
			$class->getMethod('before')->invoke($controller);

			// Determine the action to use
			$action = empty($this->action) ? Route::$default_action : $this->action;

			// Execute the main action with the parameters
			$class->getMethod('action_'.$action)->invokeArgs($controller, $this->_params);

			// Execute the "after action" method
			$class->getMethod('after')->invoke($controller);
		}
		catch (Exception $e)
		{
			// Restore the previous request
			Request::$current = $previous;

			if ($e instanceof ReflectionException)
			{
				// Reflection will throw exceptions for missing classes or actions
				$this->status = 404;
			}
			else
			{
				// All other exceptions are PHP/server errors
				$this->status = 500;
			}

			// Re-throw the exception
			throw $e;
		}

		// Restore the previous request
		Request::$current = $previous;

		return $this;
	}

} // End Request
