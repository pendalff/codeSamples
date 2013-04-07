<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * DB
 */
abstract class SMVC_DB {
	
	protected static $instances = array();

	// Instance name
	protected $_instance;

	// Raw server connection
	protected $_connection;

	// Configuration array
	protected $_config;

	/**
	 * Get a singleton DB instance. If configuration is not specified,
	 * it will be loaded from the database configuration file using the same
	 * group as the name.
	 *
	 * @param   string   instance name
	 * @param   array    configuration parameters
	 * 
	 * @FIXME return type ONLY for IDE autocomplite
	 * @return  DB_Driver_PDO
	 */
	public static function instance($name = 'default', array $config = NULL)
	{
		if ( ! isset(DB::$instances[$name]))
		{
			if ($config === NULL)
			{
				// Load the configuration for this database
				$config = SMVC::config('database')->$name;
			}

			if ( ! isset($config['type']))
			{
				throw new SMVC_Exception('Database type not defined in :name configuration',
					array(':name' => $name));
			}


			// Set the driver class name
			$driver = 'DB_driver_'.ucfirst($config['type']);

			// Create the database connection instance
			new $driver($name, $config);
		}

		return DB::$instances[$name];
	}


	/**
	 * Stores the database configuration  and the instance.
	 * @return  void
	 */
	final protected function __construct($name, array $config)
	{
		// Set the instance name
		$this->_instance = $name;

		// Store the config locally
		$this->_config = $config;

		// Store the database instance
		DB::$instances[$name] = $this;
		
	}
	
	/**
	 * Get DB config
	 * @return  void
	 */
	public function get_config()
	{
		return $this->_config;
	}
	
	abstract public function disconnect();
		
	/**
	 * Disconnect from the database when the object is destroyed.
	 *
	 * @return  void
	 */
	final public function __destruct()
	{
		$this->disconnect();
	}

} // End DB

