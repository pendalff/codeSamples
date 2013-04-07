<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Routes are used to determine the controller and action for a requested URI.
 *
 *     // This is the standard default route, and no keys are required
 *     Route::default('(<controller>(/<action>(/<id>)))');
 *
 *     // This route only requires the :file key
 *     Route::factory('(<path>/)<file>(<format>)', array('path' => '.*', 'format' => '\.\w+'));
 */
class SMVC_Route {

	const REGEX_KEY     = '<([a-zA-Z0-9_]++)>';
	const REGEX_SEGMENT = '[^/.,;?]++';
	const REGEX_ESCAPE  = '[.\\+*?[^\\]${}=!|]';

	/**
	 * @var  string  default action for all routes
	 */
	public static $default_action = 'index';

	// List of route objects
	protected static $_routes = array();

	// Route URI string
	protected $_uri = '';

	// Regular expressions for route keys
	protected $_regex = array();

	// Default values for route keys
	protected $_defaults = array('action' => 'index');

	// Compiled regex cache
	protected $_route_regex;

	/**
	 * Stores a named route and returns it.
	 *
	 * @param   string   route name
	 * @param   string   URI pattern
	 * @param   array    regex patterns for route keys
	 * @return  Route
	 */
	public static function set($name, $uri, array $regex = NULL)
	{
		return Route::$_routes[$name] = new Route($uri, $regex);
	}

	/**
	 * Retrieves a named route.
	 *
	 * @param   string  route name
	 * @return  Route
	 * @return  FALSE   when no route is found
	 */
	public static function get($name)
	{
		if ( ! isset(Route::$_routes[$name]))
		{
			throw new SMVC_Exception('The requested route does not exist: :route',
				array(':route' => $name));
		}

		return Route::$_routes[$name];
	}

	/**
	 * Retrieves all named routes.
	 *
	 * @return  array  named routes
	 */
	public static function all()
	{
		return Route::$_routes;
	}

	/**
	 * Get the name of a route.
	 *
	 * @return  string
	 */
	public static function name(Route $route)
	{
		return array_search($route, Route::$_routes);
	}

	/**
	 * Creates a new route. Sets the URI and regular expressions for keys.
	 *
	 * @param   string   route URI pattern
	 * @param   array    key patterns
	 * @return  void
	 */
	public function __construct($uri = NULL, array $regex = NULL)
	{
		if ($uri === NULL)
		{
			// Assume the route is from cache
			return;
		}

		if ( ! empty($regex))
		{
			$this->_regex = $regex;
		}

		// Store the URI that this route will match
		$this->_uri = $uri;

		// Store the compiled regex locally
		$this->_route_regex = $this->_compile();
	}

	/**
	 * Provides default values for keys when they are not present. The default
	 * action will always be "index" unless it is overloaded here.
	 *
	 *     $route->defaults(array('controller' => 'welcome', 'action' => 'index'));
	 *
	 * @param   array  key values
	 * @return  Route
	 */
	public function defaults(array $defaults = NULL)
	{
		$this->_defaults = $defaults;

		return $this;
	}

	/**
	 * Tests if the route matches a given URI.
	 * A failed match will return boolean FALSE.
	 * @param   string  URI to match
	 * @return  array   on success
	 * @return  FALSE   on failure
	 */
	public function matches($uri)
	{
		if ( ! preg_match($this->_route_regex, $uri, $matches))
			return FALSE;

		$params = array();
		foreach ($matches as $key => $value)
		{
			if (is_int($key))
			{
				// Skip all unnamed keys
				continue;
			}

			// Set the value for all matched keys
			$params[$key] = $value;
		}

		foreach ($this->_defaults as $key => $value)
		{
			if ( ! isset($params[$key]) OR $params[$key] === '')
			{
				// Set default values for any key that was not matched
				$params[$key] = $value;
			}
		}

		return $params;
	}

	/**
	 * Generates a URI for the current route based 
	 * on the parameters given.
	 *
	 * @param   array   URI parameters
	 * @return  string
	 */
	public function uri(array $params = NULL)
	{
		if ($params === NULL)
		{
			// Use the default parameters
			$params = $this->_defaults;
		}
		else
		{
			// Add the default parameters
			$params += $this->_defaults;
		}

		// Start with the routed URI
		$uri = $this->_uri;

		if (strpos($uri, '<') === FALSE AND strpos($uri, '(') === FALSE)
		{
			// This is a static route, no need to replace anything
			return $uri;
		}

		while (preg_match('#\([^()]++\)#', $uri, $match))
		{
			// Search for the matched value
			$search = $match[0];

			// Remove the parenthesis from the match as the replace
			$replace = substr($match[0], 1, -1);

			while(preg_match('#'.Route::REGEX_KEY.'#', $replace, $match))
			{
				list($key, $param) = $match;

				if (isset($params[$param]))
				{
					// Replace the key with the parameter value
					$replace = str_replace($key, $params[$param], $replace);
				}
				else
				{
					// This group has missing parameters
					$replace = '';
					break;
				}
			}

			// Replace the group in the URI
			$uri = str_replace($search, $replace, $uri);
		}

		while(preg_match('#'.Route::REGEX_KEY.'#', $uri, $match))
		{
			list($key, $param) = $match;

			if ( ! isset($params[$param]))
			{
				// Ungrouped parameters are required
				throw new SMVC_Exception('Required route parameter not passed: :param',
					array(':param' => $param));
			}

			$uri = str_replace($key, $params[$param], $uri);
		}

		// Trim all extra slashes from the URI
		$uri = preg_replace('#//+#', '/', rtrim($uri, '/'));

		return $uri;
	}

	/**
	 * Returns the compiled regular expression for the route.
	 * Used PCRE regular expression.
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function _compile()
	{
		// The URI should be considered literal except for keys and optional parts
		// Escape everything preg_quote would escape except for : ( ) < >
		$regex = preg_replace('#'.Route::REGEX_ESCAPE.'#', '\\\\$0', $this->_uri);

		if (strpos($regex, '(') !== FALSE)
		{
			// Make optional parts of the URI non-capturing and optional
			$regex = str_replace(array('(', ')'), array('(?:', ')?'), $regex);
		}

		// Insert default regex for keys
		$regex = str_replace(array('<', '>'), array('(?P<', '>'.Route::REGEX_SEGMENT.')'), $regex);

		if ( ! empty($this->_regex))
		{
			$search = $replace = array();
			foreach ($this->_regex as $key => $value)
			{
				$search[]  = "<$key>".Route::REGEX_SEGMENT;
				$replace[] = "<$key>$value";
			}

			// Replace the default regex with the user-specified regex
			$regex = str_replace($search, $replace, $regex);
		}

		return '#^'.$regex.'$#';
	}

} // End Route
