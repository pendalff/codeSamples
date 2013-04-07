<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model base class.
 */
abstract class SMVC_Model extends DB_Abstraction{

	/**
	 * Create a new model instance.
	 *
	 * @param   string   model name
	 * @param   mixed    Database instance object or string
	 * @return  Model
	 */
	public static function factory($name, $db = NULL)
	{
		// Add the model prefix
		$class = 'Model_'.$name;

		return new $class($db);
	}

	/**
	 * @var DB $_db
	 */
	protected $_db = 'default';

	/**
	 * Load the database.
	 *
	 * @param   mixed  Database instance object or string
	 * @return  void
	 */
	public function __construct($db = NULL)
	{
		if ($db !== NULL)
		{
			// Set the database instance name
			$this->_db = $db;
		}

		if (is_string($this->_db))
		{
			// Load the database
			$this->_db = DB::instance($this->_db);
		}
		parent::__construct($this->_db);
	}

	public $limit = null;
	
	public $offset = null;
	protected $_fields = null;
 
    
    /**
     * Returns $_fields.
     *
     * @see SMVC_Model::$_fields
     */
    public function get_fields () {
        return $this->_fields;
    }
    
    /**
     * Sets $_fields.
     *
     * @param object $_fields
     * @see SMVC_Model::$_fields
     */
    public function set_fields ( $_fields ) {
        $this->_fields = $_fields;
    }
       
    /**
     * Returns $limit.
     *
     * @see SMVC_Model::$limit
     */
    public function getLimit () {
        return $this->limit;
    }
    
    /**
     * Sets $limit.
     *
     * @param object $limit
     * @see SMVC_Model::$limit
     */
    public function setLimit ( $limit ) {
        $this->limit = $limit;
    }
    
    /**
     * Returns $offset.
     *
     * @see SMVC_Model::$offset
     */
    public function getOffset () {
        return $this->offset;
    }
    
    /**
     * Sets $offset.
     *
     * @param object $offset
     * @see SMVC_Model::$offset
     */
    public function setOffset ( $offset ) {
        $this->offset = $offset;
    }
    	
} // End Model
