<?php

/**
 * Db_ClosureTableDelete
 * @package Db
 * @author Thomas Schäfer
 * @author Sem yapendalff@gmail.com
 */
class Db_ClosureTable_Delete extends Db_ClosureTable_Base {

	/**
	 * delete
	 * @desc delete a child
	 * @param integer $id
	 * @param integer $parent
	 * @param bool $useLock
	 * @return $this
	 */
	public function delete($id, $parent, $useLock=true) {

		if($useLock) $this->lock();

		$this->closeWeightingGaps($id, $parent);

		$data = array(
			$this->getDatabaseName(),
			$this->getClosureTable(),
			$this->getProperty("ancestor"),
			$id,
			$this->getProperty("descendant"),
			$id
		);
		$SQL = 'DELETE FROM %s.%s WHERE %s = %d or %s = %d ;';
		$this->bindAndExecute($SQL, $data);

		if($useLock) $this->unlock();

		return $this;
	}

	/**
	 * deleteSubtree
	 * @desc deletes a sub tree of a closure table
	 * @param integer $id
	 * @param integer $parent
	 * @param bool $useLock
	 * @return $this
	 */
	public function deleteSubtree($id, $parent, $useLock=true) {
	
		if($useLock) $this->lock();

		$this->closeWeightingGaps($id, $parent);

		// multi-table delete
		$data = array(
			$this->getDatabaseName(),
			$this->getClosureTable(),
			$this->getDatabaseName(),
			$this->getClosureTable(),
			$this->getProperty("descendant"),
			$this->getProperty("ancestor"),
			$id
		);
		$SQL = 'DELETE tw0 FROM %s.%s tw0 JOIN %s.%s t2 USING (%s) WHERE t2.%s = %d;';
		$this->bindAndExecute($SQL, $data);

		if($useLock) $this->unlock();

		return $this;
	}

}