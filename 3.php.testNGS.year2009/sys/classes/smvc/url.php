<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * URL helper class.
 */
class SMVC_URL {

	/**
	 * Gets the base URL to the application. To include the current protocol,
	 * use TRUE. To specify a protocol, provide the protocol as a string.
	 *
	 * @param   boolean         add index file
	 * @param   boolean|string  add protocol and domain
	 * @return  string
	 */
	public static function base($index = FALSE, $protocol = FALSE)
	{
		if ($protocol === TRUE)
		{
			// Use the current protocol
			$protocol = Request::$protocol;
		}

		// Start with the configured base URL
		$base_url = SMVC::$base_url;

		if ($index === TRUE AND ! empty(SMVC::$index_file))
		{
			// Add the index file to the URL
			$base_url .= SMVC::$index_file.'/';
		}

		if (is_string($protocol))
		{
			if (parse_url($base_url, PHP_URL_HOST))
			{
				// Remove everything but the path from the URL
				$base_url = parse_url($base_url, PHP_URL_PATH);
			}

			// Add the protocol and domain to the base URL
			$base_url = $protocol.'://'.$_SERVER['HTTP_HOST'].$base_url;
		}

		return $base_url;
	}

	/**
	 * Fetches an absolute site URL based on a URI segment.
	 *
	 * @param   string          site URI to convert
	 * @param   boolean|string  non-default protocol
	 * @return  string
	 */
	public static function site($uri = '', $protocol = FALSE)
	{
		// Get the path from the URI
		$path = trim(parse_url($uri, PHP_URL_PATH), '/');

		if ($query = parse_url($uri, PHP_URL_QUERY))
		{
			// ?query=string
			$query = '?'.$query;
		}

		if ($fragment = parse_url($uri, PHP_URL_FRAGMENT))
		{
			// #fragment
			$fragment =  '#'.$fragment;
		}

		// Concat the URL
		return URL::base(TRUE, $protocol).$path.$query.$fragment;
	}

	/**
	 * Merges the current GET parameters with an array of new or overloaded
	 * parameters and returns the resulting query string.
	 *
	 *     // Returns "?sort=title&limit=10" combined with any existing GET values
	 *     $query = URL::query(array('sort' => 'title', 'limit' => 10));
	 *
	 * Typically you would use this when you are sorting query results,
	 * or something similar.
	 *
	 * [!!] Parameters with a NULL value are left out.
	 *
	 * @param   array   array of GET parameters
	 * @return  string
	 */
	public static function query(array $params = NULL)
	{
		if ($params === NULL)
		{
			// Use only the current parameters
			$params = $_GET;
		}
		else
		{
			// Merge the current and new parameters
			$params = array_merge($_GET, $params);
		}

		if (empty($params))
		{
			// No query parameters
			return '';
		}

		$query = http_build_query($params, '', '&');

		// Don't prepend '?' to an empty string
		return ($query === '') ? '' : '?'.$query;
	}
	/**
	 * Enforce static class
	 */
	final private function __construct(){ }

} // End url