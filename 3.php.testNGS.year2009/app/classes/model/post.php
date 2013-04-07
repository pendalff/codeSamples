<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Forum posts model
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru 
 */
class Model_Post extends Model{

	/**
	 * @var string $_table
	 */
	
	protected $_table = 'posts';
	
	/**
	 * Name of closure table
	 * @var string $_ctable
	 */
	protected $_ctable = 'tp_posts';
	
	/**
	 * Primary key
	 * @var int $_pk 
	 */
	protected $_pk = 'post_id';


		
	/**
	 * @var array $_fields
	 */
	protected $_fields = array( 'post_id' 	=> '',
								'user_id'	=> '',
								'forum_id'	=> '',
								'is_topic'  => 0,
								'post'		=> '',
								'content'	=> '',
								'ts'		=> ''
								);	

	/**
	 * @var Db_ClosureTable $_closure
	 */
	public $_closure = NULL;

	/**
	 * @var Model_User $users
	 */
	public $users = null;
		
	public function __construct($db = NULL)
	{
		parent::__construct($db);
		$this->_closure = new Db_ClosureTable($this->_db, $this->_ctable, $this->_table, $this->_pk);
	}	

    /**
     * Returns $_fields.
     *
     * @see Model_Forum::$_fields
     */
    public function get_fields () {
        return $this->_fields;
    }
    
    /**
     * Returns $users.
     *
     * @see Model_Post::$users
     */
    public function getUsers () {
        return $this->users;
    }
    
    /**
     * Sets $users.
     *
     * @param object $users
     * @see Model_Post::$users
     */
    public function setUsers ( $users ) {
        $this->users = $users;
    }
    	
	public function get_posts( $forum_id = null, $where = null, $order_by = 'ts', $dir = 'DESC' )
	{
		$this->select( $this->_table );
		$start_where = false;
		if( $forum_id !== NULL){
			$this->where( 'forum_id', $forum_id);
			$start_where = true;
		}
		if( $where !== NULL){
			if(!$start_where) $this->where( '1', 1);
			$this->anyClause($where);
		}
		$this->orderBy('ts', 'DESC');		
		if( $this->limit!==null && $this->offset!==null  ){
			$this->limit( $this->offset, $this->limit);
		}

		$posts = $this->query( false );
		if( $this->_db->getNumRows() > 0 ){
			return $this->query( );
		}
		else{
			return null;
		}
	}	
	
	public function get_user_posts( $user_id  )
	{
		$this->select( $this->_table );
		$this->where( 'user_id', $user_id);
		$this->orderBy('ts');
		
		if( $this->limit!==null && $this->offset!==null  ){
			$this->limit( $this->offset, $this->limit);
		}
		
		$posts = $this->query( false );
		if( $this->_db->getNumRows() > 0 ){
			return $this->query( );
		}
		else{
			return null;
		}
	}	
	
	public function count_user_posts( $user_id  )
	{
		$this->select( $this->_table );
		$this->where( 'user_id', $user_id);
		$this->orderBy('ts');
		
		$posts = $this->query( false );
		return $this->_db->getNumRows();
	}	
					
	public function count_posts( $forum_id = null, $where = null )
	{
		$this->select( $this->_table );
		
		$start_where = false;
		if( $forum_id !== NULL){
			$this->where( 'forum_id', $forum_id);
			$start_where = true;
		}
		if( $where !== NULL){
			if(!$start_where) $this->where( '1', 1);
			$this->anyClause($where);
		}

		$posts = $this->query( false );
		return $this->_db->getNumRows();
	}	
	
	public function get_post( $post_id ){
		$ret = new Db_ClosureTable_Retrieve($this->_db, $this->_ctable, $this->_table, $this->_pk);
		return $ret->getNode($post_id);
	}

	/**
	 *  Add post 
	 * @param object $user_id
	 * @param object $post
	 * @param object $content
	 * @param object $parent_id [optional]
	 * @return 
	 */
	public function add_post( $user_id, $forum_id, $post, $content, $is_topic = 0, $parent_id = 1 ){
		
		$db_closure = $this->_closure;	
		
		$fields = $this->_fields;
		unset($fields['post_id']);
		unset($fields['ts']);
		$fields['forum_id']  = $forum_id; 
		$fields['user_id']  = $user_id; 
		$fields['post'] 	= $post;
		$fields['content'] 	= $content;
		$fields['is_topic'] 	= $is_topic;
		$sql = $this->insert( $this->_table, $fields, true);	
		
		return $db_closure->insert($sql, $parent_id, true );		
	}

	/**
	 * Update post 
	 * @param object $post_id
	 * @param object $user_id
	 * @param object $post
	 * @param object $content
	 * @return 
	 */
	public function upd_post( $post_id, $post = null, $content = null, $user_id = null, $forum_id=null, $is_topic = null){
		$fields = $this->_fields;

		unset($fields['post_id']);
		unset($fields['ts']);
		foreach($fields AS $k => $v){
			if( isset($$k) && $$k!=null){
				$fields[$k]=$$k;
			} else{
				unset($fields[$k]);
			}
		}
		$this->update($this->_table, $post_id, $fields);
		return true;
		//$db_closure = $this->update( $this->_table, $post_id);;	
		
	}
		
	/**
	 * Delete post
	 * @param object $post_id
	 * @return 
	 */
	public function del_post( $post_id ){
		$db_closure = $this->_closure;	
		
		$descend = $db_closure->getDescendantsById( $post_id );
		$count_d = $descend->getNumRows();
		$d = $descend->getRows();
		
		$ancest = $db_closure->getAncestorsById( $post_id );
		$count_a = $descend->getNumRows();
		$a = $ancest->getRows();
		
		$parent = $this->get_parentId($post_id);


	
		if($d[0]['is_topic']==1){
			$db_closure->deleteSubtree($post_id, null);
			foreach($d AS $id){
				$this->delete($this->_table, $id['post_id']);
			}
		}
		elseif( $count_d==1 ){
			$parent = $this->get_parentId($post_id);
			$db_closure->delete($post_id, $parent);
			$this->delete($this->_table, $post_id);
		}	
		else{
			$this->upd_post($post_id, __('comment deleted'), __('comment deleted') );
		}
		return true;
	}
	
	/**
	 * Delete post with subtree
	 * @param object $post_id
	 * @return 
	 */	
	public function del_postTree( $post_id ){
		$db_closure = $this->_closure;	
		$parent = $this->get_parentId($post_id);
		$db_closure->deleteSubtree($post_id, $parent);		
	}

	protected function get_parentId( $post_id ){
		$ret = new Db_ClosureTable_Retrieve($this->_db, $this->_ctable, $this->_table, $this->_pk);
		$parent = $ret->getParent($post_id)->getRows();
		if(isset($parent[0]) && isset($parent[0]['post_id']) ) return $parent[0]['post_id'];
		
		return NULL;
	}
	public function get_parent( $post_id ){
		$ret = new Db_ClosureTable_Retrieve($this->_db, $this->_ctable, $this->_table, $this->_pk);
		$parent = $ret->getParent($post_id)->getRows();
		if(isset($parent[0]) ) return $parent[0];
		
		return NULL;
	}
}
?>