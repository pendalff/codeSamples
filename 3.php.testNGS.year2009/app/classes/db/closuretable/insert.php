<?php

/**
 * Db_ClosureTable_Insert
 * @desc
 * inserting nodes for a closure relation
 * @package Db
 * @author Thomas Schaefer
 */
class Db_ClosureTable_Insert extends Db_ClosureTable_Base {
	/**
	 * @var int $insertId
	 */
	public $insertId = null;
	 
	/**
	 * addRootNode
	 *
	 * @desc create a closure table root node
	 * @return $this
	 */
	public function addRootNode() {
		// get the entry => usually the table has to be empty
		$ancestorialRootNodeId = (int) $this->bindAndExecute('SELECT %s AS ancestor FROM %s.%s WHERE %s=0 or %s IS NULL', array(
			$this->getDatabaseName(),
			$this->getForeignTable(),
			$this->getProperty("ancestor"),
			$this->getProperty("depth"),
			$this->getProperty("weight"),
		))->getDb()->getRow(false)->ancestor;

		// if there is no root node then make another one
		if(is_null($ancestorialRootNodeId) or 0==$ancestorialRootNodeId) {
			$ancestorialRootNodeId++;
		}

		// register a new root node
		$this->bindAndExecute('INSERT INTO %s.%s () VALUES (%d,%d,NULL,1);', array(
			$this->getDatabaseName(),
			$this->getForeignTable(),
			$ancestorialRootNodeId,
			$ancestorialRootNodeId,
		));
		$this->insertId = $this->insertId();	
		return $this;
	}
	
	/**
	 * addNode
	 * @param string $sql
	 * @return bool
	 */
	private function addNode($sql) {
		return $this->doExecute($sql);
	}

	/**
	 * insert
	 * 
	 * @desc
	 * The advantage of this layout is that it is very, very fast to select from.
	 * You can get any number of children with a single query. For example, all
	 * of the children of B would be:
	 * SELECT * FROM table WHERE ancestor = 'B'
	 * which returns "D" and "E".
	 * The downside is that it takes more room on disk.
	 *
	 * @param string $sql
	 * @param integer $parentId
	 * @param bool $useLock
	 * @return $this
	 */
	public function insert($sql, $parentId, $useLock=true) {

		if($useLock) $this->lock();

		$e = $this->addNode($sql);

		if($e) {			
			$insertId = $this->insertId = $this->insertId();	
			if($insertId > 0) {
				$this->relateNode($insertId, $parentId);
			} else {
				throw new SMVC_Exception("SQL ERROR: ". $this->getDb()->get_error());
			}
					
		} else {
			throw new SMVC_Exception("SQL ERROR: ". $this->getDb()->get_error());
		}

		if($useLock) $this->unlock();

		return $this;
	}

}