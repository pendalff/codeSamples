<?php

/**
 * Db_ClosureTable_Sort
 * @package Db
 * @author Thomas Schäfer
 * @desc 
 * various adjacency relation operations to move nodes
 *   - moveLeft => level up
 *   - moveRight => level down
 *   - moveUp => intra level weighting lower
 *   - moveDown => intra level weighting greater
 * @TODO 
 * 	refactor method signature and method body to avoid parent id injection 
 * 
 */
class Db_ClosureTable_Sort extends Db_ClosureTable_Base {

	/**
	 * moveLeft
	 * @desc move node one level up to grand parent tree as last child
	 * @param integer $id
	 * @param integer $parent
	 * @return $this
	 */
	public function moveLeft($id, $parent) {

		$this->lock();

		// delete
		$closure = new Db_ClosureTableDelete($this->getDb(), $this->getClosureTable(), $this->getForeignTable(), $this->getForeignField());
		$closure->delete($id, $parent, false); // set lock usage				
		
		// get the parent's ancestor id
		$closure = new Db_ClosureTableRetrieve($this->getDb(), $this->getClosureTable(), $this->getForeignTable(), $this->getForeignField());
		$ancestorId = $closure->getAncestorId($parent);

		// now move up as last child of parent's ancestor
		$closure = new Db_ClosureTableInsert($this->getDb(), $this->getClosureTable(), $this->getForeignTable(), $this->getForeignField());
		$closure->relateNode($id, $ancestorId, "INSERT");

		$this->unlock();

		return $this;
	}

	/**
	 * moveRight
	 * @desc move node one level down into previous sibling tree
	 * @param integer $id
	 * @param integer $parent
	 * @return $this
	 */
	public function moveRight($id, $parent) {

		$this->lock();
		
		$closure = new Db_ClosureTableRetrieve($this->getDb(), $this->getClosureTable(), $this->getForeignTable(), $this->getForeignField());
		$prevSiblingId = $closure->getPreviousSibling($id, $parent);

		$closure = new Db_ClosureTableDelete($this->getDb(), $this->getClosureTable(), $this->getForeignTable(), $this->getForeignField());
		$closure->delete($id, $parent, false);

		$closure = new Db_ClosureTableInsert($this->getDb(), $this->getClosureTable(), $this->getForeignTable(), $this->getForeignField());
		$closure->relateNode($id, $prevSiblingId, "INSERT");

		$this->unlock();

		return $this;
	}

	/**
	 * moveUp
	 * @desc updates the weight of current and predecessor nodes
	 * @param integer $id
	 * @param integer $parent
	 * @return $this
	 */
	public function moveUp($id, $parent) {

		$this->lock();

		$data = array(
			$this->getDatabaseName(),
			$this->getClosureTable(),
			$this->getProperty("weight"),
			$this->getProperty("weight"),
			$this->getDatabaseName(),
			$this->getClosureTable(),
			$this->getProperty("descendant"),
			$id,
			$this->getProperty("depth"),
			$this->getProperty("depth"),
			$this->getProperty("ancestor"),
			$parent,
			// union
			$this->getDatabaseName(),
			$this->getClosureTable(),
			$this->getProperty("descendant"),
			$id,
			$this->getProperty("depth"),
			$this->getProperty("ancestor"),
			$parent
		);
		
		// UNION SELECT with SubQuery
		$SQL = 'SELECT * FROM `%s`.`%s` WHERE `%s`+1=(SELECT i.`%s` FROM `%s`.`%s` i WHERE i.`%s`=%d AND i.`%s`=1) AND `%s`=1 AND `%s`=%d UNION ALL SELECT * FROM `%s`.`%s` where `%s`=%d AND `%s`=1 AND `%s`=%d;';

		$this->bindAndExecute($SQL, $data);

		$res = $this->getDb()->getRows(false);

		$upId = $res[0]; // move up
		$downId = $res[1]; // move down
	
		// @TODO replace following queries by a single update query
		 
		// update weight before current node
		$data = array(
			$this->getDatabaseName(),
			$this->getClosureTable(),
			$this->getProperty("weight"),
			$upId[$this->getProperty("weight")],
			$this->getProperty("descendant"),
			$downId[$this->getProperty("descendant")],
			$this->getProperty("depth")
		);
		$SQL = 'UPDATE %s.%s SET %s=%d WHERE %s=%d AND %s>0;';
		$this->bindAndExecute($SQL, $data);

		// update weight after current node
		$data = array(
			$this->getDatabaseName(),
			$this->getClosureTable(),
			$this->getProperty("weight"),
			$downId[$this->getProperty("weight")],
			$this->getProperty("descendant"),
			$upId[$this->getProperty("descendant")],
			$this->getProperty("depth")
		);

		$SQL = 'UPDATE %s.%s SET %s=%d WHERE %s=%d AND %s>0;';
		$this->bindAndExecute($SQL, $data);

		$this->unlock();

		return $this;
	}

	/**
	 * moveDown
	 * @param integer $id
	 * @param integer $parent
	 * @return $this
	 */
	public function moveDown($id, $parent) {

		$this->lock();

		$data = array(
			$this->getDatabaseName(),
			$this->getClosureTable(),
			$this->getProperty("weight"),
			$this->getProperty("weight"),
			$this->getDatabaseName(),
			$this->getClosureTable(),
			$this->getProperty("descendant"),
			$id,
			$this->getProperty("depth"),
			$this->getProperty("depth"),
			$this->getProperty("ancestor"),
			$parent,
			// union
			$this->getDatabaseName(),
			$this->getClosureTable(),
			$this->getProperty("descendant"),
			$id,
			$this->getProperty("depth"),
			$this->getProperty("ancestor"),
			$parent
		);
		$SQL = 'SELECT * FROM `%s`.`%s` WHERE `%s`-1=(SELECT i.`%s` FROM `%s`.`%s` i WHERE i.`%s`=%d AND i.`%s`=1) AND `%s`=1 AND `%s`=%d UNION ALL SELECT * FROM `%s`.`%s` where `%s`=%d AND `%s`=1 AND `%s`=%d;';

		$this->bindAndExecute($SQL, $data);

		$res = $this->getDb()->getRows(false);
		
		$upId = $res[0]; // move up
		$downId = $res[1]; // move down

		// up
		$data = array(
			$this->getDatabaseName(),
			$this->getClosureTable(),
			$this->getProperty("weight"),
			$upId[$this->getProperty("weight")],
			$this->getProperty("descendant"),
			$downId[$this->getProperty("descendant")],
			$this->getProperty("depth")
		);
		$SQL = 'UPDATE %s.%s SET %s=%d WHERE %s=%d AND %s > 0;';
		$this->bindAndExecute($SQL, $data);

		// down
		$data = array(
			$this->getDatabaseName(),
			$this->getClosureTable(),
			$this->getProperty("weight"),
			$downId[$this->getProperty("weight")],
			$this->getProperty("descendant"),
			$upId[$this->getProperty("descendant")],
			$this->getProperty("depth")
		);
		$SQL = 'UPDATE %s.%s SET %s=%d WHERE %s=%d AND %s > 0;';
		$this->bindAndExecute($SQL, $data);

		$this->unlock();

		return $this;
	}

}