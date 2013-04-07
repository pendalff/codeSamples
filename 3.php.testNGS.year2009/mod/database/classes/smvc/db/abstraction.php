<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Simple Database abstraction layer.
 * @fixme - IT USED PDO, and worked only with mysql
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
class SMVC_DB_Abstraction{

	/*
	 * The sql query
	 */
	protected $sql;

	/**
	 * name=>value pairs
	 */
	protected $values = array();

	/**
	 * Cached pk for any tables
	 */
	protected static $primary_key = array();
	

	/**
	 *  @var DB $_db
	 */
	protected $_db = null;
	
	/*
	 * errors array
	 */
	public $errors = array();


	/*
	 * The sql query
	 */
	public static $sql_log = array(); 
		

	/**
	 * Constructor.
	 * @param DB $_db
	 * @return  void
	 */
	public function __construct( DB $_db )
	{
		// Set the instance name
		$this->_db = $_db;
	}

	/**
	 * Log.
	 * @param string $sql
	 * @return  void
	 */
	public function log( $sql )
	{
		self::$sql_log[] = $sql;
	}	

	/**
	 * Log render.
	 * @return  void
	 */
	public function log_render( )
	{
		return SMVC::dump( self::$sql_log );
	}	
		
	/**
	 * Add a value to the values array
	 * @access public
	 * @param string $key   - array key
	 * @param string $value - value
	 *
	 */
	public function addValue($key, $value)
	{
		$this->values[$key] = $value;
	}

	/**
	 * Set the values
	 * @access public
	 * @param array
	 *
	 */
	public function setValues($array)
	{
		$this->values = $array;
	}

	/**
	 * Delete a recored from a table
	 * @access public
	 * @param string $table The table name
	 * @param int ID
	 *
	 */
	public function delete($table, $id)
	{
		try
		{
			// get the primary key name
			$pk = $this->getPrimaryKey($table);
			$sql = "DELETE FROM $table WHERE $pk=:$pk";
			$stmt = $this->_db->prepare($sql);
			$stmt->bindParam(":$pk", $id);
			$stmt->execute();
			
			$this->log($sql);
		}
		catch(Exception $e)
		{
			$this->errors[] = $e->getMessage();
		}
	}


	/**
	 * Insert a record into a table
	 * @access public
	 * @param string $table The table name
	 * @param array $values An array of fieldnames and values
	 * @return int last insert ID
	 *
	 */
	public function insert($table, $values=null, $as_text = false)
	{
		$values = is_null($values) ? $this->values : $values;
		$sql = "INSERT INTO $table SET ";

		$obj = new CachingIterator(new ArrayIterator($values));

		try
		{
			$db = $this->_db;
			foreach( $obj as $field=>$val)
			{
				$sql .= "$field = :$field";
				$sql .=  $obj->hasNext() ? ',' : '';
				$sql .= "\n";
			}
			
			$this->log($sql);
			
			$stmt = $db->prepare($sql);


			// bind the params
			foreach($values as $k=>$v)
			{
				$stmt->bindParam(':'.$k, $v);
			}
			
			if( $as_text ){
				if($values!=NULL){
					$new_values = array();
					foreach( $values AS $key=>$val){
						$new_values[':'.$key] = $this->_db->escape($val);
					}
					$values = $new_values;
				}
				$query = strtr( $stmt->queryString, $values);
				return $query;
			}
			
			$stmt->execute($values);
			// return the last insert id
			return $db->lastInsertId();
		}
		catch(Exception $e)
		{
			$this->errors[] = $e->getMessage();
		}
	}


	/**
	 * Update a table
	 * @access public
	 * @param string $table The table name
	 * @param int $id
	 */
	public function update($table, $id, $values=null)
	{
		
		$values = is_null($values) ? $this->values : $values;
		try
		{
			// get the primary key/
			$pk = $this->getPrimaryKey($table);
	
			// set the primary key in the values array
			$values[$pk] = $id;

			$obj = new CachingIterator(new ArrayIterator($values));

			$db = $this->_db;
			$sql = "UPDATE $table SET \n";
			foreach( $obj as $field=>$val)
			{
				$sql .= "$field = :$field";
				$sql .= $obj->hasNext() ? ',' : '';
				$sql .= "\n";
			}
			$sql .= " WHERE $pk=$id";
			
			$this->log($sql);
			
			$stmt = $db->prepare($sql);

			// bind the params
			foreach($values as $k=>$v)
			{
				$stmt->bindParam(':'.$k, $v);
			}
			// bind the primary key and the id
			$stmt->bindParam($pk, $id);
			$stmt->execute($values);

			// return the affected rows
			return $stmt->rowCount();
		}
		catch(Exception $e)
		{
			$this->errors[] = $e->getMessage();
		}
	}


	/**
	 * Get the name of the field that is the primary key
	 * @access private
	 * @param string $table The name of the table
	 * @param object $use_cache [optional]
	 * @return string
	 */

	public function getPrimaryKey( $table, $use_cache = true )
	{
		try
		{
			if( isset( self::$primary_key[ $table ]) && 
				!EMPTY( self::$primary_key[ $table ]) && 
				$use_cache
			  )	return self::$primary_key[ $table ];
			
			// get the db name from the config
			$config = $this->_db->get_config();
			$db_name = $config['database']; 
			
			$sql = "SELECT
				k.column_name
				FROM
				information_schema.table_constraints t
				JOIN
				information_schema.key_column_usage k
				USING(constraint_name,table_schema,table_name)
				WHERE
				t.constraint_type='PRIMARY KEY'
				AND
				t.table_schema='{$db_name}'
				AND
				t.table_name=:table";
			$stmt = $this->_db->prepare( $sql );
			$stmt->bindParam(':table', $table, PDO::PARAM_STR);
			$stmt->execute();
			$pk = $stmt->fetchColumn(0);
			self::$primary_key[ $table ] = $pk;
			return $pk;
		}
		catch(Exception $e)
		{
			$this->errors[] = $e->getMessage();
		}
	}


	/**
	 * Fetch all records from table
	 * @access public
	 * @param $table The table name
	 * @return array
	 */
	public function query( $exec = true )
	{
		$res = $this->_db->query( $this->sql );
		$this->log($this->sql);
		if( $exec )	return $this->_db->getRows();
	}

	/**
	 *
	 * @select statement
	 * @access public
	 * @param string $table
	 */
	public function select( $table, $fields = null )
	{
		$fields    = $fields === null ? "*" : $fields;
		$this->sql = "SELECT ".$fields." FROM $table";
	}

	/**
	 * Where clause
	 * @access public
	 * @param string $field
	 * @param string $value
	 */
	public function where($field, $value)
	{
		$value = $this->_db->escape($value);
		$this->sql .= " WHERE $field=$value";
	}

	/**
	 * Set limit
	 * @access public
	 * @param int $offset
	 * @param int $limit
	 * @return string
	 */
	public function limit($offset, $limit)
	{
		$this->sql .= " LIMIT $offset, $limit";
	}

	/**
	 * Add an AND clause
	 * @access public
	 * @param string $field
	 * @param string $value
	 *
	 */
	public function andClause($field, $value)
	{
		$this->sql .= " AND $field=$value";
	}

	/**
	 * add an OR clause
	 * @access public
	 * @param string $field
	 * @param string $value
	 *
	 */
	public function orClause($field, $value)
	{
		$this->sql .= " OR $field=$value";
	}

	/**
	 * add any string to where
	 * @access public
	 * @param string $value
	 *
	 */
	public function anyClause($value)
	{
		$this->sql .= " $value";
	}

	/**
	 * Add order by
	 * @param string $fieldname
	 * @param string $order
	 *
	 */
	public function orderBy($fieldname, $order='ASC')
	{
		$this->sql .= " ORDER BY $fieldname $order";
	}
	
	// to know if table has a field
	public function hasField($table,$field) {
		try {

			// get the db name from the config
			$config = $this->_db->get_config();
			$db_name = $config['database']; 

			$sql = "SELECT
			COUNT(*)
			FROM
				information_schema.columns c
			WHERE
				c.table_schema='{$db_name}'
			AND
				c.table_name=:table
			AND
				c.column_name=:field";
			$st = $this->_db->prepare($sql);
			$st->bindParam(':table', $table, PDO::PARAM_STR);
			$st->bindParam(':field', $field, PDO::PARAM_STR);
			$st->execute();
			return $st->fetchColumn(0);
		}
		catch(Exception $e)
		{
			$this->errors[] = $e->getMessage();
		}
	}
	
	
	// inc - increment field by value
	public function inc($table,$id,$field,$value) {
		try {

			$pk = $this->getPrimaryKey($table);
			$sql = "UPDATE $table SET $field = $field + :value WHERE $pk = :$pk";
	    	$st = $this->_db->prepare($sql);
			
		    $st->bindParam(":value", $value, PDO::PARAM_INT);
		    $st->bindParam(":$pk", $id);
			$st->execute();
			$this->log($sql);
		}
		catch(Exception $e) {
			$this->errors[] = $e->getMessage();
		}
	}
	
	public function field($table,$field, $id = null, $alias = null) {
		try {

			$who = $field;
			if($alias){
				$who .= " AS " .$alias;
			}
			else{
				$alias = $field;
			}
			
			$sql = "SELECT $who FROM $table WHERE 1=1 ";
	    	if($id){
				$sql .= "$pk = :$pk";
	    	}
			$st = $this->_db->prepare($sql);
			$this->log($sql);
	    	if($id){
				$st->bindParam(":$pk", $id);
	    	}
		    
			$st->execute();
			$res = $st->fetch(PDO::FETCH_ASSOC);
			return $res[$alias];
		}
		catch(Exception $e) {
			$this->errors[] = $e->getMessage();
		}
	}
		
}
?>