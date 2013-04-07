<?php defined('SYSPATH') or die('No direct script access.');
/**
 * File configuration loader.
 */
class SMVC_Config_File extends SMVC_Config_Reader {

	// Configuration group name
	protected $_configuration_group;

	// Has the config group changed?
	protected $_configuration_modified = FALSE;

	public function __construct($directory = 'config')
	{
		// Set the configuration directory name
		$this->_directory = trim($directory, '/');

		// Load the empty array
		parent::__construct();
	}

	/**
	 * Merge all of the configuration files in this group.
	 *
	 * @param   string  group name
	 * @param   array   configuration array
	 * @return  $this   clone of the current object
	 */
	public function load($group, array $config = NULL)
	{
		if ($files = SMVC::find_file($this->_directory, $group))
		{
			// Initialize the config array
			$config = array();

			foreach ($files as $file)
			{
				// Merge each file to the configuration array
				$config = Arr::merge($config, require $file);
			}
		}

		return parent::load($group, $config);
	}

} // End SMVC_Config
