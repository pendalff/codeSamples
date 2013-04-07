<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Internationalization class.
 */
class SMVC_I18n {

	/**
	 * @var  string   target language: en, fr etc
	 */
	public static $lang = 'ru';

	// Cache of loaded languages
	protected static $_cache = array();

	/**
	 * Get and set the target language.
	 *
	 * @param   string   new language setting
	 * @return  string
	 */
	public static function lang($lang = NULL)
	{
		if ($lang)
		{
			// Normalize the language
			I18n::$lang = strtolower( $lang );
		}

		return I18n::$lang;
	}

	/**
	 * Returns translation of a string. If no translation exists, the original
	 * string will be returned.
	 *
	 * @param   string   text to translate
	 * @return  string
	 */
	public static function get($string)
	{
		if ( ! isset(I18n::$_cache[I18n::$lang]))
		{
			// Load the translation table
			I18n::load(I18n::$lang);
		}

		// Return the translated string if it exists
		return isset(I18n::$_cache[I18n::$lang][$string]) ? I18n::$_cache[I18n::$lang][$string] : $string;
	}

	/**
	 * Returns the translation table for a given language.
	 *
	 * @param   string   language to load
	 * @return  array
	 */
	public static function load($lang)
	{
		if (isset(I18n::$_cache[$lang]))
		{
			return I18n::$_cache[$lang];
		}

		// New translation table
		$table = array();
		if ($files = SMVC::find_file('i18n', $lang))
		{
			$t = array();
			foreach ($files as $file)
			{
				// Merge the language strings into the sub table
				$t = array_merge($t, SMVC::load($file));
			}

			// Append the sub table, preventing less specific language
			// files from overloading more specific files
			$table += $t;
		}

		// Cache the translation table locally
		return I18n::$_cache[$lang] = $table;
	}

	final private function __construct()
	{
		// This is a static class
	}

} // End I18n
