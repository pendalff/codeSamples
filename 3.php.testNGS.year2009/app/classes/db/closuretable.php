<?php

/**
 * Db_ClosureTable 
 * - features:
 *   + create closure table
 *   + insert node to a tree
 *   + move nodes within tree up and down
 *   + delete node and tree of nodes
 *   + retrieve different kinds of node structure: path, tree, subtree
 * @desc wrapper class for closure table operations
 * @package Db
 * @author Thomas Schaefer
 * @author Sem yapendalff@gmail.com
 */
class Db_ClosureTable {

	/**
	 * @var object $db
	 */
	private $db;

	/**
	 * @var string $closureTable
	 */
	private $closureTable;
	/**
	 * @var string $foreignTable
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
	 * @var Db_ClosureTable_Base $closure
	 */
	private $closure;

	/**
	 * @var static
	 * @var array $chain
	 */
	private static $chain = array();
	/**
	 * @var static
	 * @var integer $amountOfNodes
	 */
	private static $amountOfNodes = 0;
	/**
	 * @var static
	 * @var integer $nodeCounter
	 */
	private static $nodeCounter = 0;

	/**
	 * @var static
	 * @var integer $computeLRValues
	 */
	private static $computeLRValues = false;

	/**
	 * @var static
	 * @var array $Nodes
	 */
	private static $Nodes = array();
	/**
	 * @var static
	 * @var array $columns
	 */
	private static $columns = array(
		"captionField" => "post",
		"idField" => "descendant",
		"parentIdField" => "parent",
		"childNodesField" => "childs",
		"leafNodesField" => "isLeaf",
		"weightField" => "weight",
		"leftField" => "l",
		"rightField" => "r",
		"counterField" => "c"
	);
    
    /**
     * Returns $limit.
     *
     */
    public function getLimit () {
        return $this->closure->limit;
    }
    
    /**
     * Sets $limit.
     *
     * @param object $limit
     */
    public function setLimit ( $limit ) {
        $this->closure->limit = $limit;
    }
    
    /**
     * Returns $offset.
     *
     */
    public function getOffset () {
        return $this->closure->offset;
    }
    
    /**
     * Sets $offset.
     *
     * @param object $offset
     */
    public function setOffset ( $offset ) {
        $this->closure->offset = $offset;
    }
 	

	/**
	 * constructor
	 *
	 * @param Db $db
	 * @param string $closureTable name of the closure table
	 * @param string $foreignTable name of the related data table
	 * @param string $foreignField name of the relating data column
	 * @param bool $hasDepth
	 */
	public function __construct(Db $db, $closureTable, $foreignTable, $foreignField, $hasDepth=true) {
		// @TODO Database Adapter
		$this->db = $db;
		$this->closureTable = $closureTable;
		$this->foreignTable = $foreignTable;
		$this->foreignField = $foreignField;
		$this->hasDepth = $hasDepth;
	}

	/**
	 * create
	 * @param bool $mode
	 * @return $this
	 */
	public function create($mode=1) {
		$this->closure = new Db_ClosureTable_Create($this->db, $this->closureTable, $this->foreignTable, $this->foreignField);
		$this->closure()->init();
		$this->closure()->execute();

		$this->closure()->getRenderedCreateTable($mode);
		return $this;
	}

	/**
	 * insert
	 * @param string $sql
	 * @param integer $parent
	 * @return $this
	 */
	public function insert($sql, $parent, $return_insertid = false) {
		$this->closure = new Db_ClosureTable_Insert($this->db, $this->closureTable, $this->foreignTable, $this->foreignField);
		$this->closure()->insert($sql, $parent);
		
		if($return_insertid){
			return $this->closure->insertId;
		}
		
		return $this;
	}

	/**
	 * moveUp
	 * @param integer $id
	 * @param integer $parent
	 * @return $this
	 */
	public function moveUp($id, $parent) {
		$this->closure = new Db_ClosureTable_Sort($this->db, $this->closureTable, $this->foreignTable, $this->foreignField);
		$this->closure()->moveUp($id, $parent);
		return $this;
	}

	/**
	 * moveDown
	 * @param integer $id
	 * @param integer $parent
	 * @return $this
	 */
	public function moveDown($id, $parent) {
		$this->closure = new Db_ClosureTable_Sort($this->db, $this->closureTable, $this->foreignTable, $this->foreignField);
		$this->closure()->moveDown($id, $parent);
		return $this;
	}

	/**
	 * moveLeft
	 * @param integer $id
	 * @param integer $parent
	 * @return $this
	 */
	public function moveLeft($id, $parent) {
		$this->closure = new Db_ClosureTable_Sort($this->db, $this->closureTable, $this->foreignTable, $this->foreignField);
		$this->closure()->moveLeft($id, $parent);
		return $this;
	}

	/**
	 * moveRight
	 * @param integer $id
	 * @param integer $parent
	 * @return $this
	 */
	public function moveRight($id, $parent) {
		$this->closure = new Db_ClosureTable_Sort($this->db, $this->closureTable, $this->foreignTable, $this->foreignField);
		$this->closure()->moveRight($id, $parent);
		return $this;
	}

	/**
	 * delete
	 * @param integer $id
	 * @param integer $parent
	 * @return $this
	 */
	public function delete($id, $parent) {
		$this->closure = new Db_ClosureTable_Delete($this->db, $this->closureTable, $this->foreignTable, $this->foreignField);
		$this->closure()->delete($id, $parent);
		return $this;
	}

	/**
	 * deleteSubtree
	 * @param integer $id
	 * @param integer $parent
	 * @return $this
	 */
	public function deleteSubtree($id, $parent) {
		$this->closure = new Db_ClosureTable_Delete($this->db, $this->closureTable, $this->foreignTable, $this->foreignField);
		$this->closure()->deleteSubtree($id, $parent);
		return $this;
	}

	/**
	 * getAncestorsById
	 * @param integer $id
	 * @return $this
	 */
	public function getAncestorsById($id) {
		$this->closure = new Db_ClosureTable_Retrieve($this->db, $this->closureTable, $this->foreignTable, $this->foreignField);
		$this->closure()->getAncestorsById($id);
		return $this;
	}

	/**
	 * getDescendantsById
	 * @param integer $id
	 * @return $this
	 */
	public function getDescendantsById($id) {
		$this->closure = new Db_ClosureTable_Retrieve($this->db, $this->closureTable, $this->foreignTable, $this->foreignField);
		$this->closure()->getDescendantsById($id);
		return $this;
	}

	/**
	 * getDescendantsByIdAndDepth
	 * @param integer $id
	 * @param integer $depth
	 * @return $this
	 */
	public function getDescendantsByIdAndDepth($id, $depth) {
		$this->closure = new Db_ClosureTable_Retrieve($this->db, $this->closureTable, $this->foreignTable, $this->foreignField);
		$this->closure()->getDescendantsByIdAndDepth($id,$depth);
		return $this;
	}
	
	/**
	 * getDescendantsByIdWithParent
	 * @param integer $id
	 * @return $this
	 */
	public function getDescendantsByIdWithParent($id) {
		/**
		 * @var Db_ClosureTable_Retrieve
		 */
		$this->closure = new Db_ClosureTable_Retrieve($this->db, $this->closureTable, $this->foreignTable, $this->foreignField);
		$this->closure()->getDescendantsByIdWithParent($id);
		return $this;
	}

	/**
	 * createRootNode
	 * @desc action for creating root nodes
	 * @return $this
	 */
	public function createRootNode() {
		/**
		 * @var Db_ClosureTable_Insert
		 */
		$this->closure = new Db_ClosureTable_Insert($this->db, $this->closureTable, $this->foreignTable, $this->foreignField);
		$this->closure()->addRootNode();
		return $this;
	}

	/**
	 * getNestedTree
	 * @param integer $id
	 * @param integer $depth
	 * @return $this
	 */
	public function getNestedTree($id, $depth=1) {
		/**
		 * @var Db_ClosureTable_Retrieve
		 */
		$this->closure = new Db_ClosureTable_Retrieve($this->db, $this->closureTable, $this->foreignTable, $this->foreignField);
		$this->closure()->getDescendantsByIdAndDepth($id,$depth);
		return $this;
	}


	/**
	 * getTree
	 * @param integer $id
	 * @return $this
	 */
	public function getTree($id, $limit=null, $offset=null) {
		/**
		 * @var Db_ClosureTable_Retrieve
		 */
		$this->closure = new Db_ClosureTable_Retrieve($this->db, $this->closureTable, $this->foreignTable, $this->foreignField);
		if( $limit!==null && $offset!==null  ){
			$this->setOffset($offset);
			$this->setLimit($limit);
		}		
		$this->closure()->getTree($id);
		return $this;
	}
	
	public function getcountTree($id) {
		/**
		 * @var Db_ClosureTable_Retrieve
		 */
		$this->closure = new Db_ClosureTable_Retrieve($this->db, $this->closureTable, $this->foreignTable, $this->foreignField);

		$this->closure()->getcountTree($id);
		
		return $this->getDb()->getRow();
	}
	/**
	 * private conversion methods
	 */
	
	/**
	 * setColumns
	 * @access static
	 * @param array $columns
	 */
	public static function setColumns($columns){
		foreach($columns as $key => $value) {
			if(array_key_exists($key, self::$columns)) {
				self::$columns[$key] = $value;
			}
		}
	}

	/**
	 * asNestedTree
	 * @param integer $id
	 * 
	 * @return mixed
	 */
	public function asNestedTree($id=null, $hidden=null, $computeLRValues=false, $limit=null, $offset=null) {
		if(empty($id)) {
			return false;
		}
		self::$computeLRValues = $computeLRValues;
		$this->getTree($id, $limit, $offset);
		$res = $this->getDb()->getRows(false);
		$this->close();

		return self::convertToTreeArray($res,$hidden, $limit, $offset);
	}

	/**
	 * convertToTreeArray
	 * @param array $flat
	 * @param string $idField
	 * @param string $parentIdField
	 * @param string $childNodesField
	 * @param string $leafNodesField
	 * @return array
	 */
	private static function convertToTreeArray(
		array $flat, array $hidden = null , $limit=null, $offset=null
	) {		
		$idField = self::$columns['idField'];
		$parentIdField = self::$columns['parentIdField'];
		$childNodesField = self::$columns['childNodesField'];
		$leafNodesField = self::$columns['leafNodesField'];
		$leftField = self::$columns['leftField'];
		$rightField = self::$columns['rightField'];
		
		self::$amountOfNodes = count($flat);

		/**
		 * preparing left and right node values of nested sets
		 * The root is always of the form:
		 *	(left = 1, right = 2 * (SELECT COUNT(*) FROM TreeTable));
		 * leaf nodes always have:
		 *	(left + 1 = right).
		 */
		
		$left = 0;
		$right = 2 * self::$amountOfNodes;
		$indexed = array();

		// first pass - get the array indexed by the primary id		
		foreach ($flat as $index => $row) {
			if(count($hidden)>0){
				foreach($hidden AS $filter_key => $filter_val){
					if($row[$filter_key]==$filter_val) continue;
				}
			}
			$indexed[$row[$idField]] = $row;
			self::$Nodes[$row[$idField]] = $row;
			if(array_key_exists($leafNodesField, $row) and $row[$leafNodesField]==1) {
				$indexed[$row[$idField]][$childNodesField] = array();
			}
	
		}
	//	var_dump($indexed);
		//second pass
		$root = null;
		foreach ($indexed as $id => $row) {
			// remove child container			
			$indexed[$row[$parentIdField]][$childNodesField][$id] =& $indexed[$id];
			if (!$row[$parentIdField] or $root==null) {
				$root = $id;
			}
		}
		
		if(self::$computeLRValues) {
			$indexed["LRValues"] = self::computeLRValues($indexed[$root], $left, $right, $data = array());			
		} else {
			$indexed["LRValues"] = array();
		}
		$indexed["root"] = $root;
		$indexed["columns"] = self::$columns;
		$indexed["count"] = self::$amountOfNodes;
		return $indexed;
	}

	/**
	 * computeLRValues
	 * @desc compute left and right values for nested sets
	 * @param array $node
	 * @param integer $left
	 * @param integer $right
	 * @param array $data
	 * @param integer $prev
	 * @return array
	 */
	private static function computeLRValues($node, $left, $right, $data = array(), $prev=null) {

		$idField = self::$columns['idField'];
		$parentIdField = self::$columns['parentIdField'];
		$childNodesField = self::$columns['childNodesField'];
		$leafNodesField = self::$columns['leafNodesField'];
		$leftField = self::$columns['leftField'];
		$rightField = self::$columns['rightField'];
		$counterField = self::$columns['counterField'];
		
		// root only
		if((is_null($node[$parentIdField]) or $node["depth"]==0) and $node[$leafNodesField]>1) {
			$data[$leftField][$node[$idField]] = 1;
			$data[$rightField][$node[$idField]] = $data[$leftField][$node[$idField]] + (2 * $node[$leafNodesField]) - 1;
			self::$Nodes[$node[$idField]][$leftField] = 1;
			self::$Nodes[$node[$idField]][$rightField] = $data[$rightField][$node[$idField]];
			self::$Nodes[$node[$idField]][$counterField] = self::$nodeCounter;
			self::$nodeCounter++;

			if($data[$rightField][$node[$idField]]>2) {
				$node = $node[$childNodesField];
				$left++;
				$prev = $node[$idField];				
			} else {
				// only root
				return $data;
			}
		} else {
			// node child
			$node = $node[$childNodesField];
			$previous = $prev;
			self::$nodeCounter++;
		}
		
		foreach($node as $key => $cnode) {
			// leaves

			if($cnode[$leafNodesField]==1) {
				if(self::$amountOfNodes-1==self::$nodeCounter) {			
					if($prev==$previous){
						$data[$leftField][$key] = self::$Nodes[$prev][$leftField]+1;
					} else {
						$data[$leftField][$key] = self::$Nodes[$prev][$rightField]+1;
					}
					$data[$rightField][$key] = $data[$leftField][$key]+1;
				} else {
					if(!count($cnode[$childNodesField])) {
						if($prev==$previous) {
							if(self::$nodeCounter==1){ // if second level node has no children
								$data[$leftField][$key] = self::$nodeCounter+1;
							} else {
								$data[$leftField][$key] = self::$Nodes[$prev][$leftField]+1;
							}
						} else {
							$data[$leftField][$key] = self::$Nodes[$prev][$rightField]+1;
						}
					} else {
						$offset = $prev!=$cnode[$parentIdField]?2:1;
						$data[$leftField][$key] = $data[$leftField][$prev]+$offset;
					}
					$data[$rightField][$key] = $data[$leftField][$key]+1;
				}
				
				self::$Nodes[$key][$leftField] = $data[$leftField][$key];
				self::$Nodes[$key][$rightField] = $data[$rightField][$key];
				self::$Nodes[$key][$counterField] = self::$nodeCounter;

				$data = self::computeLRValues(
						$cnode,
						$data[$leftField][$key],
						$data[$rightField][$key],
						$data,
						$key
					);

			} else {

				if($prev!=$previous){
					$data[$leftField][$key] = self::$Nodes[$prev][$rightField]+1;
				} else {
					$childsPrevSibling = !is_null($node[$prev][$leafNodesField])?$node[$prev][$leafNodesField]:0;
					$data[$leftField][$key] = $childsPrevSibling + $left+1+$node[$prev][$leafNodesField];
				}
				
				$data[$rightField][$key] = $data[$leftField][$key] + (2 * $cnode[$leafNodesField]) - 1;

				self::$Nodes[$key][$leftField] = $data[$leftField][$key];
				self::$Nodes[$key][$rightField] = $data[$rightField][$key];
				self::$Nodes[$key][$counterField] = self::$nodeCounter;

				$data = self::computeLRValues(
						$cnode,
						$data[$leftField][$key],
						$data[$rightField][$key],
						$data,
						$key
					);
			}
			$prev = $key;
			
		}
		return $data;
	}
	
	public function getRows() {
		return $this->getDb()->getRows(false);
	}
	
	public function getNumRows() {
		return $this->getDb()->getNumRows();
	}

	/**
	 * close
	 * @desc close Db Connection
	 */
	public function close() {
		$this->closure()->getDb()->disconnect();
	}

	/**
	 * getDb
	 * @return Db
	 */
	public function getDb() {
		return $this->db;
	}

	/**
	 * closure
	 * @desc returns an instance of Db_ClosureTable_Base
	 * @return Db
	 */
	public function closure() {
		return $this->closure;
	}

	/**
	 * debug
	 * @desc set debug flag
	 */
	public static function debug() {
		Db_ClosureTable_Retrieve::debug();
	}

}