<?php

/**
 * Db_ClosureTable_Retrieve
 * @package Db
 * @author Thomas Schaefer
 */
class Db_ClosureTable_Retrieve extends Db_ClosureTable_Base {

	/**
	 * getPath
	 * @desc shortcut for getAncestorsById
	 * @param integer $id node id
	 * @return $this
	 */
	public function getPath($id) {
		$this->getAncestorsById($id);
		return $this;
	}

	/**
	 * getAncestorsById
	 * @desc retrieve ancestors
	 * @param integer $id
	 * @return self
	 */
	public function getAncestorsById($id) {
		$this->bindAndExecute('SELECT p.*, t.depth FROM %s.%s p JOIN %s.%s t ON p.%s=t.%s WHERE t.%s = %d;', array(
			$this->getDatabaseName(),
			$this->getForeignTable(),
			$this->getDatabaseName(),
			$this->getClosureTable(),
			$this->getForeignField(),
			$this->getProperty("ancestor"),
			$this->getProperty("descendant"),
			$id
		));
		return $this;
	}

	/**
	 * getAncestorId
	 * @desc retrieve ancestor
	 * @param integer $id
	 * @param integer $depth
	 * @return $this
	 */
	public function getAncestorId($id, $depth=1) {
		$this->bindAndExecute('SELECT ancestor FROM %s.%s WHERE %s=%d AND %s=%d;', array(
			$this->getDatabaseName(),
			$this->getClosureTable(),
			$this->getProperty("descendant"),
			$id,
			$this->getProperty("depth"),
			$depth
		));
		return $this->getDb()->getRow(true)->ancestor;
	}
	
	/**
	 *  getNode
	 * @param integer $id
	 * @return array
	 */
	public function getNode($id) {
		$this->bindAndExecute('SELECT p.* FROM %s.%s p WHERE p.%s = %d ',array(
			$this->getDatabaseName(),
			$this->getForeignTable(),
			$this->getForeignField(),
			$id,
		));
		return $this->getDb()->getRow();
	}
	
	/**
	 * getParent
	 * @desc retrieve ancestor
	 * @param integer $id
	 * @return $this
	 */
	public function getParent($id) {
		$this->bindAndExecute('SELECT p.*, t.depth FROM %s.%s p JOIN %s.%s t ON p.%s=t.%s WHERE t.%s = %d AND depth=1;', array( 
			$this->getDatabaseName(),
			$this->getForeignTable(),
			$this->getDatabaseName(),
			$this->getClosureTable(),
			$this->getForeignField(),
			$this->getProperty("ancestor"),
			$this->getProperty("descendant"),
			$id
		));
		return $this;
	}

	/**
	 * getUpPath
	 * @param integer $id
	 * @return $this 
	 */
	public function getUpPath($id) {
		$this->getDescendantsById($id);
		return $this;
	}
	/**
	 * getDescendantsById
	 * @desc retrieve descendants
	 * @param integer $id
	 * @return self
	 */
	public function getDescendantsById($id) {

		$data = array();
		$data[] = $this->getDatabaseName();
		$data[] = $this->getForeignTable();
		$data[] = $this->getDatabaseName();
		$data[] = $this->getClosureTable();
		$data[] = $this->getForeignField();
		$data[] = $this->getProperty("descendant");
		$data[] = $this->getProperty("ancestor");
		$data[] = $id;
		$data[] = $this->getProperty("descendant");

		$SQL = 'SELECT p.*, t.depth FROM %s.%s p JOIN %s.%s t ON p.%s=t.%s WHERE t.%s = %d;';

		$this->bindAndExecute($SQL, $data);

		return $this;
	}

	/**
	 * getDescendants
	 * @desc common function to retrieve descendants by id and depth
	 * @access private
	 * @param integer $id
	 * @param integer $depth
	 * @param string $operator
	 * return $this;
	 */
	private function getDepthedDescendants($id, $depth, $operator='=') {
		$data = array(
			$this->getDatabaseName(),
			$this->getForeignTable(),
			$this->getDatabaseName(),
			$this->getClosureTable(),
			$this->getForeignField(),
			$this->getProperty("descendant"),
			$this->getProperty("ancestor"),
			$id,
			$this->getProperty("depth"),
			$operator,
			$depth,
		);
		$SQL = 'SELECT p.*, t.depth FROM %s.%s p JOIN %s.%s t ON p.%s=t.%s WHERE t.%s = %d and t.%s%s%d;';
		$this->bindAndExecute($SQL, $data);
	}

	/**
	 * getDescendantsByIdAndDepth
	 * @desc retrieve descendants
	 * @param integer $id
	 * @param integer $depth
	 * @return $this
	 */
	public function getDescendantsByIdAndDepth($id,$depth) {
		$this->getDepthedDescendants($id, $depth, '=');
		return $this;
	}

	/**
	 * getDescendantsByIdGreaterThan
	 * @desc retrieve descendants
	 * @param integer $id
	 * @param integer $depth
	 * @return $this
	 */
	public function getDescendantsByIdGreaterThan($id,$depth) {
		$this->getDepthedDescendants($id, $depth, '>');
		return $this;
	}

	/**
	 * getDescendantsByIdLowerThan
	 * @desc retrieve descendants
	 * @param integer $id
	 * @param integer $depth
	 * @return $this
	 */
	public function getDescendantsByIdLowerThan($id,$depth) {
		$this->getDepthedDescendants($id, $depth, '<');
		return $this;
	}

	/**
	 * getDescendantsByIdLowerThanEqual
	 * @desc retrieve descendants
	 * @param integer $id
	 * @param integer $depth
	 * @return $this
	 */
	public function getDescendantsByIdLowerThanEqual($id,$depth) {
		$this->getDepthedDescendants($id, $depth, '<=');
		return $this;
	}

	/**
	 * getDescendantsByIdGreaterThanEqual
	 * @desc retrieve descendants
	 * @param integer $id
	 * @param integer $depth
	 * @return $this
	 */
	public function getDescendantsByIdGreaterThanEqual($id,$depth) {
		$this->getDepthedDescendants($id, $depth, '>=');
		return $this;
	}

	/**
	 * getDescendantsByIdWithParent
	 * @desc retrieve descendants
	 * @param integer $id
	 * @return $this
	 */
	public function getDescendantsByIdWithParent($id) {

		$data = array(
			$this->getProperty("ancestor"), // t1 for parent
			$this->getDatabaseName(),
			$this->getClosureTable(),
			$this->getProperty("depth"), // where immediate parent
			$this->getProperty("descendant"),
			$this->getProperty("descendant"),
			$this->getDatabaseName(), // p
			$this->getForeignTable(),
			$this->getDatabaseName(), // join t
			$this->getClosureTable(),
			$this->getForeignField(),
			$this->getProperty("descendant"),
			$this->getProperty("ancestor"), // where
			$id, // starting point
		);
		$SQL = 'SELECT p.*, t.* , (SELECT t1.%s FROM %s.%s t1 WHERE t1.%s=1 AND t1.%s=t.%s) AS parent FROM %s.%s p JOIN %s.%s t ON p.%s=t.%s WHERE t.%s=%d;';
		$this->bindAndExecute($SQL, $data);

		return $this;
	}

	/**
	 * getTree
	 * @param integer $id
	 * @return $this
	 */
	public function getTree($id) {

		$data = array(
			$this->getProperty("ancestor"), // t1 for parent
			$this->getDatabaseName(),
			$this->getClosureTable(),
			$this->getProperty("depth"), // where immediate parent
			$this->getProperty("descendant"),
			$this->getProperty("descendant"),
			$this->getDatabaseName(), // isLeaf
			$this->getClosureTable(),
			$this->getProperty("ancestor"),
			$this->getProperty("descendant"),
			$this->getDatabaseName(), // from p
			$this->getForeignTable(),
			$this->getDatabaseName(), // join t
			$this->getClosureTable(),
			$this->getForeignField(),
			$this->getProperty("descendant"),
			$this->getProperty("ancestor"), // where
			$id // starting point
		);
		// do not show unbound data 
		$SQL = 'SELECT * FROM (';
		$SQL .= 'SELECT p.*, t.* ,
	(SELECT t1.%s FROM %s.%s t1 WHERE t1.%s=1 AND t1.%s=t.%s) AS parent,
	(SELECT count(*) FROM %s.%s t2 WHERE t2.%s=t.%s) AS isLeaf
	FROM %s.%s p JOIN %s.%s t ON p.%s=t.%s WHERE t.%s=%d';
		if( $this->limit!==null && $this->offset!==null  ){
			$SQL.=" LIMIT  ".$this->offset.", ". $this->limit."  ";
		}
		$SQL .= ') adjacency_relation WHERE isLeaf > 0 ';
		$SQL.=' ORDER BY depth, weight';

				

		
		$this->bindAndExecute($SQL, $data);
		
		return $this;
	}
	public function getcountTree($id) {

		$data = array(
			$this->getProperty("ancestor"), // t1 for parent
			$this->getDatabaseName(),
			$this->getClosureTable(),
			$this->getProperty("depth"), // where immediate parent
			$this->getProperty("descendant"),
			$this->getProperty("descendant"),
			$this->getDatabaseName(), // isLeaf
			$this->getClosureTable(),
			$this->getProperty("ancestor"),
			$this->getProperty("descendant"),
			$this->getDatabaseName(), // from p
			$this->getForeignTable(),
			$this->getDatabaseName(), // join t
			$this->getClosureTable(),
			$this->getForeignField(),
			$this->getProperty("descendant"),
			$this->getProperty("ancestor"), // where
			$id // starting point
		);
		// do not show unbound data 
		$SQL = 'SELECT COUNT(*) as count FROM (';
		$SQL .= 'SELECT p.*, t.* ,
	(SELECT t1.%s FROM %s.%s t1 WHERE t1.%s=1 AND t1.%s=t.%s) AS parent,
	(SELECT count(*) FROM %s.%s t2 WHERE t2.%s=t.%s) AS isLeaf
	FROM %s.%s p JOIN %s.%s t ON p.%s=t.%s WHERE t.%s=%d';

		$SQL .= ') adjacency_relation WHERE isLeaf > 0 ';
		$SQL.=' ORDER BY depth, weight';
			
		$this->bindAndExecute($SQL, $data);
		
		return $this;
	}
	/**
	 * getPreviousSibling
	 * @param integer $id
	 * @param integer $parent
	 * @return integer
	 */
	public function getPreviousSibling($id, $parent) {
		$data = array(
			// column
			$this->getProperty("descendant"),
			// from
			$this->getDatabaseName(),
			$this->getClosureTable(),
			// where
			$this->getProperty("ancestor"),
			$parent,
			// and
			$this->getProperty("depth"),
			$this->getProperty("weight"),
			// select
			// column
			$this->getProperty("weight"),
			// from
			$this->getDatabaseName(),
			$this->getClosureTable(),
			// where
			$this->getProperty("descendant"),
			$id,
			// and
			$this->getProperty("depth"),
		);
		
		$SQL = 'SELECT %s AS prevSibling FROM %s.%s WHERE %s=%d AND %s=1 AND %s=(';
		$SQL .= 'SELECT %s FROM %s.%s WHERE %s=%d AND %s=1)-1;';
		$this->bindAndExecute($SQL, $data);

		return $this->getDb()->getRow()->prevSibling;
	}

	/**
	 * getNextSibling
	 * @param integer $id
	 * @param integer $parent
	 * @return integer
	 */
	public function getNextSibling($id, $parent) {
		$data = array(
			// column
			$this->getProperty("descendant"),
			// from
			$this->getDatabaseName(),
			$this->getClosureTable(),
			// where
			$this->getProperty("ancestor"),
			$parent,
			// and
			$this->getProperty("depth"),
			$this->getProperty("weight"),
			// select
			// column
			$this->getProperty("weight"),
			// from
			$this->getDatabaseName(),
			$this->getClosureTable(),
			// where
			$this->getProperty("descendant"),
			$id,
			// and
			$this->getProperty("depth"),
		);

		$SQL = 'SELECT %s AS nextSibling FROM %s.%s WHERE %s=%d AND %s=1 AND %s=(';
		$SQL .= 'SELECT %s FROM %s.%s WHERE %s=%d AND %s=1)+1;';
		$this->bindAndExecute($SQL, $data);

		return $this->getDb()->getRow()->nextSibling;
	}

	public function getNextSiblings($id, $parent) {
		$data = array(
			// column
			$this->getProperty("descendant"),
			// from
			$this->getDatabaseName(),
			$this->getClosureTable(),
			// where
			$this->getProperty("ancestor"),
			$parent,
			// and
			$this->getProperty("depth"),
			$this->getProperty("weight"),
			// select
			// column
			$this->getProperty("weight"),
			// from
			$this->getDatabaseName(),
			$this->getClosureTable(),
			// where
			$this->getProperty("descendant"),
			$id,
			// and
			$this->getProperty("depth"),
		);

		$SQL = 'SELECT %s AS nextSibling FROM %s.%s WHERE %s=%d AND %s=1 AND %s>(';
		$SQL .= 'SELECT %s FROM %s.%s WHERE %s=%d AND %s=1);';
		$this->bindAndExecute($SQL, $data);

		return $this->getDb()->getRow()->nextSibling;
	}

	public function getPreviousSiblings($id, $parent) {
		$data = array(
			// column
			$this->getProperty("descendant"),
			// from
			$this->getDatabaseName(),
			$this->getClosureTable(),
			// where
			$this->getProperty("ancestor"),
			$parent,
			// and
			$this->getProperty("depth"),
			$this->getProperty("weight"),
			// select
			// column
			$this->getProperty("weight"),
			// from
			$this->getDatabaseName(),
			$this->getClosureTable(),
			// where
			$this->getProperty("descendant"),
			$id,
			// and
			$this->getProperty("depth"),
		);

		$SQL = 'SELECT %s AS nextSibling FROM %s.%s WHERE %s=%d AND %s=1 AND %s<(';
		$SQL .= 'SELECT %s FROM %s.%s WHERE %s=%d AND %s=1);';
		$this->bindAndExecute($SQL, $data);

		return $this->getDb()->getRow()->nextSibling;
	}

}