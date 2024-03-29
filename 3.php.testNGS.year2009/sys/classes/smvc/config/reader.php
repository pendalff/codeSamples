<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Abstract config reader.
 */
abstract class SMVC_Config_Reader extends ArrayObject {

	// Configuration group name
	protected $_configuration_group;

	/**
	 * Loads an empty array as the initial configuration
	 *
	 * @return  void
	 */
	public function __construct()
	{
		parent::__construct(array(), ArrayObject::ARRAY_AS_PROPS);
	}


	/**
	 * Loads a configuration group.
	 *
	 * @param   string  group name
	 * @param   array   configuration array
	 * @return  $this   clone of the current object
	 */
	public function load($group, array $config = NULL)
	{
		if ($config === NULL)
		{
			return FALSE;
		}

		// Set the group name
		$this->_configuration_group = $group;

		// Clone the current object
		$object = clone $this;

		// Swap the array with the actual configuration
		$object->exchangeArray($config);

		// Empty the configuration group
		$this->_configuration_group = NULL;

		return $object;
	}

	/**
	 * Return the raw array that is being used for this object.
	 *
	 * @return  array
	 */
	public function as_array()
	{
		return $this->getArrayCopy();
	}

	/**
	 * Get a variable from the configuration or return the default value.
	 *
	 * @param   string   array key
	 * @param   mixed    default value
	 * @return  mixed
	 */
	public function get($key, $default = NULL)
	{
		return $this->offsetExists($key) ? $this->offsetGet($key) : $default;
	}

	/**
	 * Sets a value in the configuration array.
	 *
	 * @param   string   array key
	 * @param   mixed    array value
	 * @return  $this
	 */
	public function set($key, $value)
	{
		$this->offsetSet($key, $value);

		return $this;
	}


	/**
	 * Return current config in serialized form.
	 *
	 * @return  string
	 */
	public function __toString()
	{
		return serialize($this->getArrayCopy());
	}
} // End SMVC_Config_Reader
