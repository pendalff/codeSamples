<?php

/**
 * Db_ClosureTable_Base
 * @package Db
 * @abstract
 * @author Thomas Schaefer
 * @author Sem yapendalff@gmail.com
 */
abstract class Db_ClosureTable_Base {

	/**
	 * @var static
	 * @var bool $dbg;
	 */
	private static $dbg = false;

	/**
	 * @var Db $db;
	 */
	private $db;

	/**
	 * @var string $dbName;
	 */
	private $dbName;

	/**
	 * @var string $closureTable;
	 */
	private $closureTable;

	/**
	 * @var string $foreignTable;
	 */
	private $foreignTable;

	/**
	 * @var string $foreignField
	 */
	private $foreignField;

	/**
	 * @var bool $hasDepth
	 */
	private $hasDepth = true; // use distance or depth operator

	/**
	 * @var array $stack
	 */
	private $stack = array();

	/**
	 * @var array $properties
	 */
	private $properties = array(
		"ancestor"=>"ancestor",
		"descendant"=>"descendant",
		"depth"=>"depth",
		"weight"=>"weight"
	);
	
	public $limit = null;
	
	public $offset = null;

    
    /**
     * Returns $limit.
     *
     */
    public function getLimit () {
        return $this->limit;
    }
    
    /**
     * Sets $limit.
     *
     * @param object $limit
     */
    public function setLimit ( $limit ) {
        $this->limit = $limit;
    }
    
    /**
     * Returns $offset.
     *
     */
    public function getOffset () {
        return $this->offset;
    }
    
    /**
     * Sets $offset.
     *
     * @param object $offset
     */
    public function setOffset ( $offset ) {
        $this->offset = $offset;
    }
 	
	/**
	 * @var boolean $use_lock
	 */
	private $use_lock = true;

	/**
	 * contrucutor
	 * @param Db $db
	 * @param string $closureTable
	 * @param string $foreignTable
	 * @param string $foreignField
	 * @param bool $hasDepth
	 */
	public function __construct(DB $db, $closureTable, $foreignTable, $foreignField, $hasDepth=true) {
		$this->db = $db;
		$config = $db->get_config();
		if( !isset( $config['database'] )){
			throw new SMVC_Exception("Closure table need database name in config");
		}
		$this->dbName = $config['database'];
		$this->closureTable = $closureTable;
		$this->foreignTable = $foreignTable;
		$this->foreignField = $foreignField;
		$this->hasDepth = $hasDepth;
	}

	/**
	 * debug
	 * @static
	 * @return void
	 */
	public static function debug() {
		self::$dbg = true;
	}

	/**
	 * getDatabaseName
	 * @return string
	 */
	public function getDatabaseName() {
		return $this->dbName;
	}

	/**
	 * getClosureTable
	 * @return string
	 */
	public function getClosureTable() {
		return $this->closureTable;
	}

	/**
	 * getForeignTable
	 * @return string
	 */
	public function getForeignTable() {
		return $this->foreignTable;
	}

	/**
	 * getForeignField
	 * @return string
	 */
	public function getForeignField() {
		return $this->foreignField;
	}

	/**
	 * hasDepth
	 * @return bool
	 */
	public function hasDepth() {
		return $this->hasDepth;
	}

	/**
	 * getDb
	 * @return Db
	 */
	public function getDb() {
		return $this->db;
	}

	/**
	 * insertId
	 * @return integer
	 */
	public function insertId() {
		return $this->getDb()->lastInsertId();
	}

	/**
	 * setProperty
	 * @param string $name
	 * @param mixed $property
	 * @return void
	 */
	public function setProperty($name, $property){
		if(array_key_exists($name, $property)) {
			$this->properties[$name] = $property;
		} else {
			throw new InvalidArgumentException("$name is not a registered property");
		}
	}

	/**
	 * getProperty
	 * @param string $key
	 * @return string
	 */
	public function getProperty($key) {
		if(array_key_exists($key, $this->properties)) {
			return (string)$this->properties[$key];
		} else {
			throw new InvalidArgumentException("$key is not supported.");
		}
	}

	/**
	 * bindAndExecute
	 * @param string $SQL
	 * @param array $data
	 * @return $this
	 */
	public function bindAndExecute($SQL, $data) {

		$this->doExecute(self::bind($SQL, $data));
		return $this;
	}

	/**
	 * bind
	 * @desc bind data to sql
	 * @param string $SQL
	 * @param array $data
	 * @return string
	 */
	public static function bind($SQL, $data) {
		$sql= vsprintf($SQL, $data);
		return $sql;
	}

	/**
	 * doExecute
	 * @desc facade for query
	 * @param string $sql
	 * @return $this
	 */
	public function doExecute($sql) {
		if(self::$dbg) {
			$fp = fopen("tree.txt", "a");
			fwrite($fp, $sql);
			fwrite($fp, "\n##########################\n");
			fclose($fp);
			
		}/*
		$sql = trim($sql,";");
		if( stripos($sql, 'select')===0 || stripos($sql, 'select')<5){
			if( $this->limit!==null && $this->offset!==null  ){
				$sql.=" LIMIT( ".$this->offset.", ". $this->limit." )";
			}
		}*/
		return $this->getDb()->doExecute($sql);
	}

	/**
	 * execute
	 * @desc execute sql stackwise
	 * @return void
	 */
	public function execute() {
		foreach($this->getStack() as $SQL) {
			$this->doExecute($SQL);
		}
	}

	public function getRows() {
		return $this->getDb()->getRows(false);
	}
	
	public function getNumRows() {
		return $this->getDb()->getNumRows();
	}

	/**
	 * add
	 * @param string $string
	 */
	public function add($string) {
		$this->stack[] = $string;
	}

	/**
	 * getStack
	 * @return array
	 */
	public function getStack() {
		return $this->stack;
	}

	/**
	 * unlock table
	 * @return $this
	 */
	protected function unlock() {
		$this->bindAndExecute('UNLOCK TABLES;', array());
		return $this;
	}

	/**
	 * lock table
	 * @return $this
	 */
	protected function lock() {
		$this->bindAndExecute(  'LOCK TABLES `%s`.`%s` WRITE , `%s`.`%s` as t2 READ , `%s`.`%s` as t1 READ , `%s`.`%s` as t0 READ , `%s`.`%s` as tw0 WRITE , `%s`.`%s` WRITE;', 
							array(
			$this->getDatabaseName(),$this->getClosureTable(), 
			$this->getDatabaseName(),$this->getClosureTable(),
			$this->getDatabaseName(),$this->getClosureTable(),
			$this->getDatabaseName(),$this->getClosureTable(),
			$this->getDatabaseName(),$this->getClosureTable(),
			$this->getDatabaseName(),$this->getForeignTable(),
		) 
		);
		return $this;
	}

	/**
	 * closeWeightingGaps
	 * @desc close gaps in weight column in front of a delete or move operation
	 * @param $id $id
	 * @param integer $parent
	 */
	protected function closeWeightingGaps($id, $parent) {

		$data = array(
			#replace
			$this->getDatabaseName(),
			$this->getClosureTable(),
			$this->getProperty("ancestor"),
			$this->getProperty("descendant"),
			$this->getProperty("depth"),
			$this->getProperty("weight"),
			# outer select
			$this->getProperty("ancestor"),
			$this->getProperty("descendant"),
			$this->getProperty("depth"),
			$this->getProperty("weight"),
			$this->getDatabaseName(),
			$this->getClosureTable(),
			// where
			$this->getProperty("descendant"),
			// in
			# inner select
			$this->getProperty("descendant"),
			$this->getDatabaseName(),
			$this->getClosureTable(),
			$this->getProperty("ancestor"),
			$parent,
			$this->getProperty("depth"),
			$this->getProperty("weight"),
			# inner select
			$this->getProperty("weight"),
			$this->getDatabaseName(),
			$this->getClosureTable(),
			$this->getProperty("descendant"),
			$id,
			$this->getProperty("depth"),
			$this->getProperty("depth"),
		);
		$SQL = 'REPLACE INTO %s.%s (%s,%s,%s,%s)
			SELECT t0.%s,t0.%s,t0.%s,t0.%s-1 AS weight FROM %s.%s as t0 WHERE t0.%s 
			IN (
			SELECT t1.%s FROM %s.%s AS t1 WHERE t1.%s=%d AND t1.%s=1 AND t1.%s>(
				SELECT t2.%s FROM %s.%s t2 WHERE t2.%s=%d AND t2.%s=1
				)
			) AND t0.%s>0;';

		$this->bindAndExecute($SQL, $data);

		return $this;
	}


	/**
	 * relateNode
	 * @desc if true depth which is a synonym for distance insert a depth
	 * value for representing the distance between two nodes
	 *
	 * depth = distance from root
	 * weight = order = position in sub-tree
	 *
	 * @example
	 * ancestor | descendant | depth (distance)
	 * ----------------------------------------
	 * A        | B          | 1
	 * A        | C          | 1
	 * B        | D          | 1
	 * A        | D          | 2
	 * B        | E          | 1
	 * A        | E          | 2
	 * C        | F          | 1
	 * A        | F          | 2
	 *
	 * @param integer $insertId
	 * @param integer $parentId
	 */
	public function relateNode($insertId, $parentId, $SQLMODE = "REPLACE", $add=1) {

		$data = array(
			$this->getDatabaseName(),
			$this->getClosureTable(),
			$this->getProperty("ancestor"),
			$this->getProperty("descendant"),
			$this->getProperty("depth"),
			$this->getProperty("weight"),
			//SELECT1
			$insertId,
			$insertId,
			
			$this->getProperty("ancestor"),
			$insertId,
			$this->getProperty("depth"),
			$add,
			$add,
			// begin weight
			$this->getDatabaseName(),
			$this->getClosureTable(),
		//	$this->getClosureTable(),
		//	$this->getClosureTable(), 
			$this->getProperty("ancestor"),
			$parentId,
			$this->getProperty("depth"),
			// end weight
			
			$this->getDatabaseName(),
			$this->getClosureTable(),
			$this->getProperty("descendant"),
			$parentId,
			$this->getProperty("depth")
		);
		$SQL = $SQLMODE .' INTO `%s`.`%s`(`%s`, `%s`, `%s`, `%s`)
SELECT %d, %d, 0, 0
UNION ALL
SELECT `%s`, %d, t0.%s+(%d), (SELECT count(*)+(%d) FROM `%s`.`%s` as t2 WHERE t2.`%s`=%d and `%s`=1)
FROM `%s`.`%s` as t0
WHERE `%s`=%d';

		$this->bindAndExecute($SQL, $data);

		return $this;
	}



}