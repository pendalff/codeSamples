<?php defined('SYSPATH') or die('No direct script access.');

/**
 * helper for working with system-generated messages
 *
 * Each message has a type (error/warning/success etc). Also there are two
 * additional types: custom (for data store) and validate (for validation errors)
 *
 * @property	Session		$session
 * @property	View		$template
 */

class Core_message
{
	// all helper data (messages)
	static protected $_data = array();
	// array for previous requests data
	static protected $_olddata = array();
	// array for currently added data only
	static protected $_newdata = array();
	
	// default template name (use in render() method)
	static protected $_template = 'messages/default';
	
	// this property allow to render all-in-one validation errors
	static protected $_show_validation = FALSE;
	
	// what is the name of session variable
	static protected $_session_var = 'message_container';
	// session object
	static protected $_session = FALSE;
	// TRUE if data was initialized (loaded from Session)
	static protected $_loaded = FALSE;
	// TRUE if data was saved in Session
	static protected $_saved = FALSE;

	// some URLs (like media) can be skipped to avoid message deleting
	static public $ignored_routes = array();

	// message types
	const SUCCESS     = 'success';
	const ERROR       = 'error';
	const VALIDATION  = 'validation';
	const NOTICE      = 'notice';
	const INFO        = 'info';
	const CUSTOM      = 'custom';

/**
 * Dumps all messages. For tests only!
 */
	public static function dump()
	{
		var_dump(self::$_data);
	}

	/**
	 * Initial data loading
	 */
	static public function init()
	{
		if (in_array(Route::name(Request::instance()->route), self::$ignored_routes))
		{
			return FALSE;
		}

		// don't call init() twice!
		if (self::$_loaded) return FALSE;

		@self::$_session = Session::instance( );

		// load session data
		self::$_olddata = self::$_session->get(self::$_session_var, FALSE);
		// clear session data
		self::$_session->delete(self::$_session_var);

		if (self::$_olddata == FALSE)
		{
			// create empty array - there is no data
			self::$_olddata = array();
		}

		self::$_data = self::$_olddata;
		self::$_loaded = TRUE;
	}

/**
 *
 * Save data in session. Saves new data only ($added array)
 *
 */
	static public function save()
	{
		if ( !self::$_loaded OR self::$_saved) return FALSE;

		self::$_session->set(self::$_session_var, self::$_newdata);
		self::$_saved = TRUE;
	}

/**
 *
 * Save "old" custom tag with "new" values
 *
 */
	public static function save_tag($tag)
	{
		if (isset(self::$_newdata[message::CUSTOM][$tag]))
		{
			return FALSE;
		}

		if (isset(self::$_olddata[message::CUSTOM][$tag]))
		{
			self::$_newdata[message::CUSTOM][$tag] = self::$_olddata[message::CUSTOM][$tag];
			return TRUE;
		}

		return FALSE;
	}

/**
 *
 * Save all data in session ($data array).
 * Needed if we want to use current data on the next page
 *
 */

	static public function save_all()
	{
		if (self::$_saved) return FALSE;

		self::$_session->set(self::$_session_var, self::$_data);
		self::$_saved = TRUE;
	}

/**
 *
 * Apply new template.
 *
 * @param string $template   new template name
 */

	static public function set_template($template = 'default')
	{
		self::$_template = 'messages/'.$template;
	}

/**
 *
 * Syncronizes $data, $added and $loaded arrays. Uses when some new data added
 *
 * @param string $type   which type of messages will we syncronize
 */

	static private function sync($type = NULL)
	{
		if (is_null($type))
		{
			// sync all data
			self::$_data = self::$_olddata + self::$_newdata;
		}
		else
		{
			// sync $type messages only
			self::$_data[$type] = (isset(self::$_olddata[$type]) ? self::$_olddata[$type] : array())
			                    + (isset(self::$_newdata[$type]) ? self::$_newdata[$type] : array());
		}
	}

/**
 *
 * Removes data from helper.
 *
 * Can delete all data ($type is NULL), or $type data, or one value ($tag is set)
 *
 * @param string $type    messages type to remove
 * @param string $tag     tagname to remove one value
 */

	static private function remove($type, $tag = NULL)
	{
		if (is_null($tag))
		{
			// remove all data
			if (isset(self::$_data[$type]))          unset(self::$_data[$type]);
			if (isset(self::$_newdata[$type]))       unset(self::$_newdata[$type]);
			if (isset(self::$_olddata[$type]))       unset(self::$_olddata[$type]);
		}
		else
		{
			// remove only $type data (with $tag checking)
			if (isset(self::$_data[$type][$tag]))    unset(self::$_data[$type][$tag]);
			if (isset(self::$_newdata[$type][$tag])) unset(self::$_newdata[$type][$tag]);
			if (isset(self::$_olddata[$type][$tag])) unset(self::$_olddata[$type][$tag]);
		}
	}

/**
 *
 * Returns all values of supplied type and tagname and removes them from helper
 *
 * @param string $type   messages type
 * @param string $tag    tagname if one value needed
 * @return array
 */

	static public function get_type($type = NULL, $tag = NULL)
	{
		if (is_null($type))
		{
			// full data request
			$result = self::$_data;
			// delete messages
			self::clear();
			return $result;
		}
		if (is_array($type))
		{
			$result = array();
			foreach($type as $t)
			{
				$result[$t] = self::get_type($t);
			}
			return $result;
		}

		// check for data of supplied type
		if (!isset(self::$_data[$type])) return array();

		if (isset($tag))
		{
			// returns one value if tagname supplied
			if (!isset(self::$_data[$type][$tag]))
			{
				return array();
			}
			else
			{
				$result = array(self::$_data[$type][$tag]);
			}
		}
		else
		{
			// get all data of this type
			$result = self::$_data[$type];
		}

		// delete returned data from helper
		self::remove($type, $tag);

		return $result;
	}

/**
 *
 * @param string|array $message  data to store
 * @param string       $type     data type
 */

	static public function add($message, $type = message::INFO)
	{
		if (is_array($message))
		{
			// save data as $key=>$value
			if (isset(self::$_newdata[$type]))
			{
				// this data type array already exists
				self::$_newdata[$type] += $message;
			}
			else {
				// its a first data of this type
				self::$_newdata[$type] = $message;
			}
		}
		else {
			// its a string data
			if (!isset(self::$_newdata[$type]))
			{
				// there is no such type data
				self::$_newdata[$type][] = $message;
			}
			elseif (!in_array($message, self::$_newdata[$type]))
			{
				// add message string
				self::$_newdata[$type][] = $message;
			}
		}
		// sync data after every change
		self::sync($type);
	}

/**
 *
 * Adds validation errors (usually from Validate->errors() method)
 *
 * Data key will be a fieldname, value - array($rule, $params)
 *
 * @param array  $errors        validation errors
 * @param string $i18n_file     i18n filename
 */

	static public function add_validation(array $errors, $i18n_prefix = FALSE)
	{
		$result = array();

		$prefix = $i18n_prefix ? $i18n_prefix.'.'.$key.'.' : '';

		foreach($errors as $key => $value)
			$result[$key] = __($prefix.$value);
		// add errors to $added array with validation type
		self::add($result, message::VALIDATION);
	}

/**
 *
 * Delete all storing data (possible by type and tagname)
 *
 * @param  string  $type  data type to delete (optional). Clears all messages if not set.
 * @param  string  $tag   tag name (optional). Clears all messages with $type applied
 */

	static public function clear($type = NULL, $tag = NULL)
	{
		if (is_null($type)) {
			// remove all data
			self::$_data = self::$_newdata = self::$_olddata = array();
		}
		else {
			// remove supplied type only
			self::remove($type, $tag);
		}
	}

/**
 *
 * Returns custom type data by tagname.
 *
 * @param  string       $tag      tagname
 * @param  mixed        $default  default value if not exists
 * @return string|array
 */

	static public function custom($tag = NULL, $default = NULL)
	{
		if (is_null($tag)) {
			// returns all custom data
			if ( ! isset(self::$_data[message::CUSTOM])) return $default;
			$result = self::$_data[message::CUSTOM];
			// dont forget to clear data!
			self::remove(message::CUSTOM);
			return $result;
		}

		if ( ! isset(self::$_data[message::CUSTOM])) return $default;
		// check for existing
		if (isset(self::$_data[message::CUSTOM][$tag])) {
			// get tagged value and delete it from data
			$result = self::$_data[message::CUSTOM][$tag];
			self::remove(message::CUSTOM, $tag);
			return $result;
		}
		else
		{
			// check if there are dots in tag - its an array key
			if (strpos($tag, '.') === FALSE)
				return $default;
			return arr::path(self::$_data[message::CUSTOM], $tag, $default);
		}
	}

/**
 * Returns array of system messages (useful for AJAX requests)
 */
	static public function export()
	{
		$types = array
		(
			message::ERROR,
			message::INFO,
			message::NOTICE,
			message::SUCCESS,
		);

		$result = array();

		foreach($types as $type)
		{
			$result[$type] = self::get_type($type);
		}

		return $result;
	}

/**
 * Shows message box with supplied type and tagname
 *
 * @param   string   $type   data type to render
 * @param   string   $tag    tagname if only one value needed
 * @return  boolean          FALSE if there was no rendering
 */

	static public function render($type = NULL, $tag = NULL)
	{
		// check for data
		if (count(self::$_data)==0) return FALSE;

		// custom data may use in form submitting for example
		if ($type == message::CUSTOM) return FALSE;

		if (is_null($type))
		{
			// render all data by existing types
			$val = '';
			foreach(self::$_data as $type=>$value)
			{
				$val .= self::render($type, $tag);
			}
			return $val;
		}

		// don't show validation data without tagname
		if (self::$_show_validation===FALSE AND $type == message::VALIDATION AND is_null($tag))
			return FALSE;

		$data = self::get_type($type, $tag);
		// there is no data to render
		if (count($data)==0) return FALSE;

		$view = new View(self::$_template);
		$view->data = $data;
		$view->type = $type;
		// if tagname supplied it will be a single box without many message string
		$view->inline = (isset($tag) ? " inline" : "");

		return $view->render();

	}

	static public function has($type = message::ERROR, $tag = NULL)
	{
		if (is_array($type))
		{
			foreach($type as $mess_type)
			{
				if (self::has($mess_type, $tag)) return TRUE;
			}

			return FALSE;
		}

		if ( !isset(self::$_data[$type])) return FALSE;

		if (is_null($tag))
		{
			return (bool)count(self::$_data[$type]);
		}
		else
		{
			return isset(self::$_data[$type][$tag]);
		}
	}

/**
 *
 * Throws message immediately, without saving in $_data property.
 *
 * @param mixed   $message    message data
 * @param string  $type       message type (message constants recommended)
 * @param string  $template   template name
 */
	static public function flash($message, $type = message::NOTICE, $template = NULL)
	{
		if (is_null($template))
		{
			$template = self::$_template;
		}
		return View::factory($template, array('data' => array($message), 'type' => $type, 'inline'=>NULL));
	}

}