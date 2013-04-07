<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
class Core_HTML  {

	/**
	 * @var  boolean  automatically target external URLs to a new window
	 */
	public static $windowed_urls = FALSE;
	/**
	 * @var  array  preferred order of attributes
	 */
	public static $attribute_order = array
	(
		'action',
		'method',
		'type',
		'id',
		'name',
		'value',
		'href',
		'src',
		'width',
		'height',
		'cols',
		'rows',
		'size',
		'maxlength',
		'rel',
		'media',
		'accept-charset',
		'accept',
		'tabindex',
		'accesskey',
		'alt',
		'title',
		'class',
		'style',
		'selected',
		'checked',
		'readonly',
		'disabled',
	);

	/**
	 * Convert special characters to HTML entities. All untrusted content
	 * should be passed through this method to prevent XSS injections.
	 *
	 *     echo HTML::chars($username);
	 *
	 * @param   string   string to convert
	 * @param   boolean  encode existing entities
	 * @return  string
	 */
	public static function chars($value, $double_encode = TRUE)
	{
		return htmlspecialchars((string) $value, ENT_QUOTES, SMVC::$charset, $double_encode);
	}

	/**
	 * Create HTML link anchors. Note that the title is not escaped, to allow
	 * HTML elements within links (images, etc).
	 *
	 * @param   string  URL or URI string
	 * @param   string  link text
	 * @param   array   HTML anchor attributes
	 * @param   string  use a specific protocol
	 * @return  string
	 */
	public static function anchor($uri, $title = NULL, array $attributes = NULL, $protocol = NULL)
	{
		if ($title === NULL)
		{
			// Use the URI as the title
			$title = $uri;
		}

		if ($uri === '')
		{
			// Only use the base URL
			$uri = URL::base(FALSE, $protocol);
		}
		else
		{
			if (strpos($uri, '://') !== FALSE)
			{
				if (HTML::$windowed_urls === TRUE AND empty($attributes['target']))
				{
					// Make the link open in a new window
					$attributes['target'] = '_blank';
				}
			}
			elseif ($uri[0] !== '#')
			{
				// Make the URI absolute for non-id anchors
				$uri = URL::site($uri, $protocol);
			}
		}

		// Add the sanitized link to the attributes
		$attributes['href'] = $uri;

		return '<a'.HTML::attributes($attributes).'>'.$title.'</a>';
	}

	/**
	 * Creates an email anchor. Note that the title is not escaped, to allow
	 * HTML elements within links (images, etc).
	 *
	 * @param   string  email address to send to
	 * @param   string  link text
	 * @param   array   HTML anchor attributes
	 * @return  string
	 */
	public static function mailto($email, $title = NULL, array $attributes = NULL)
	{

		if ($title === NULL)
		{
			// Use the email address as the title
			$title = $email;
		}

		return '<a href="&#109;&#097;&#105;&#108;&#116;&#111;&#058;'.$email.'"'.HTML::attributes($attributes).'>'.$title.'</a>';
	}
	/**
	 * Compiles an array of HTML attributes into an attribute string.
	 *
	 * @param   array   attribute list
	 * @return  string
	 */
	public static function attributes(array $attributes = NULL)
	{
		if (empty($attributes))
			return '';

		$sorted = array();
		foreach (HTML::$attribute_order as $key)
		{
			if (isset($attributes[$key]))
			{
				// Add the attribute to the sorted list
				$sorted[$key] = $attributes[$key];
			}
		}

		// Combine the sorted attributes
		$attributes = $sorted + $attributes;

		$compiled = '';
		foreach ($attributes as $key => $value)
		{
			if ($value === NULL)
			{
				// Skip attributes that have NULL values
				continue;
			}

			// Add the attribute value
			$compiled .= ' '.$key.'="'.htmlspecialchars($value, ENT_QUOTES, SMVC::$charset).'"';
		}

		return $compiled;
	}

	final private function __construct()
	{
		// This is a static class
	}

}


?>