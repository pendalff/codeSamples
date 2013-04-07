<?php defined('SYSPATH') or die('No direct script access.');
/**
 * PDO based database driver. is simple wrapper, use it for logging, profiling, etc
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
class SMVC_DB_Driver_PDO extends DB implements SMVC_I_dbdriver{

	/**
	 * @var PDOStatement $_stmt
	 */
	protected $_stmt = NULL;

	public function connect(){
		if ($this->_connection)
			return;

		// Extract the connection parameters, adding required variabels
		extract($this->_config['connection'] + array(
			'dsn'        => '',
			'username'   => NULL,
			'password'   => NULL,
			'persistent' => FALSE,
		));

		// Clear the connection parameters for security
		//unset($this->_config['connection']);

		// Force PDO to use exceptions for all errors
		$attrs = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);

		if ( ! empty($persistent))
		{
			// Make the connection persistent
			$attrs[PDO::ATTR_PERSISTENT] = TRUE;
		}

		// Create a new PDO connection
		$this->_connection = new PDO($dsn, $username, $password, $attrs);

		if ( ! empty($this->_config['charset']))
		{
			// Set the character set
			$this->set_charset($this->_config['charset']);
		}
	}
	
    /**
     * has Connection?
     * @return boolean
     */
    public function has_connection() {
        return (isset($this->_connection)?true:false);
    }
	
    public function get_stmt( $without_null = true ){
		if( $without_null && !$this->_stmt instanceof PDOStatement ){
	    		throw new SMVC_Exception("No PDO Statement available.");
		}
        return $this->_stmt;    	
    }


    /**
     * has Error
     * @return integer
     */
    public function has_error(){
    	$errno = $this->_stmt->errorCode();
        if ($errno>0) {
            return $errno;
        }
        return false;    	
    }

    /**
     * get Error
     * @return int
     */
    public function get_error(){
    	$message = $this->_stmt->errorInfo();
    	return isset($message[2]) ? $message[2] : $message[0];
    }


    /**
     * query
     * @param string $sql
     */
	public function query( $sql ){
		// Make sure the database is connected
		$this->_connection or $this->connect();		
		try
		{
			$this->_stmt = $this->_connection->query($sql);
		}
		catch (Exception $e)
		{
			// Rethrow exception
			throw $e;
		}
	}


    /**
     * exec
     * @param string $sql
     */
	public function exec( $sql ){
		try
		{	
			return $this->_connection->exec($sql);
		}
		catch (Exception $e)
		{
			// Rethrow exception
			throw $e;
		}
	}

    /**
     * exec
     * @param string $sql
     */
	public function doExecute( $sql ){
		try
		{	
			$this->prepare($sql);
			return $this->_stmt->execute();
		}
		catch (Exception $e)
		{
			// Rethrow exception
			throw $e;
		}
	}

    /**
     * query
     * @param string $sql
     */
	public function prepare( $sql, array $driver_options = array()  ){
		// Make sure the database is connected
		$this->_connection or $this->connect();		
		try
		{
			$this->_stmt = $this->_connection->prepare($sql,$driver_options);
			return $this->_stmt;
		}
		catch (Exception $e)
		{
			// Rethrow exception
			throw $e;
		}
	}	
	/**
	 * 
	 * @return 
	 */
	public function getNumRows(){
		return $this->get_stmt()->rowCount();	
	}

	/**
	 * Last insert ID
	 * @return int
	 */
	public function lastInsertId(){
		// Make sure the database is connected
		$this->_connection or $this->connect();		
		return $this->_connection->lastInsertId();	
	}
	/**
	 * 
	 * @param object $as_object [optional]
	 * @return 
	 */
	public function getRow( $as_object = true ){
		
			$resource = $this->get_stmt();
			// Convert the result into an array, as PDOStatement::rowCount is not reliable
			if ($as_object === FALSE)
			{
				$resource->setFetchMode(PDO::FETCH_ASSOC);
			}
			else
			{
				$resource->setFetchMode(PDO::FETCH_CLASS,  'stdClass');
			}
			return $resource->fetch();

			return (count($ret)>0) ? $ret[0] : false;	 
	}
	/**
	 * 
	 * @param object $as_object [optional]
	 * @return 
	 */
	public function getRows( $as_object = true ){
			$resource = $this->get_stmt();
			// Convert the result into an array, as PDOStatement::rowCount is not reliable
			if ($as_object === FALSE)
			{
				$resource->setFetchMode(PDO::FETCH_ASSOC);
			}
			else
			{
				$resource->setFetchMode(PDO::FETCH_CLASS,  'stdClass');
			}

			return $resource->fetchAll();			
	}
	
	/**
	 * Escape
	 * @param mixed $value
	 * @return 
	 */
	public function escape($value)
	{
		// Make sure the database is connected
		$this->_connection or $this->connect();

		return $this->_connection->quote($value);
	}

	public function set_charset($charset)
	{
		// Make sure the database is connected
		$this->_connection or $this->connect();

		// Execute a raw SET NAMES query
		$this->_connection->exec('SET NAMES '.$this->escape($charset));
	}
	
	public function disconnect(){
		// Destroy  PDO object
		$this->_connection = NULL;

		return TRUE;		
	}
}
?>