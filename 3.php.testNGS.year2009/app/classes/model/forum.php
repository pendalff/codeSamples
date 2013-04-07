<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Forum model
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru 
 */
class Model_Forum extends Model{
  
	/**
	 * @var string $_table
	 */
	
	protected $_table = 'forums';

	protected $_pk   = 'forum_id';

	/**
	 * @var array $_fields
	 */
	protected $_fields = array( 'forum_id' 		=> '',
								'name'			=> '',
								'description'	=> '',
								'sort'			=> ''
								);	
	/**
	 * @var Model_Post $posts
	 */
	public $posts = null;
	/**
	 * @var Model_User $posts
	 */
	public $users = null;
				
	public function __construct($db = NULL)
	{
		parent::__construct($db);
		$this->posts = new Model_Post($db);
		
		$this->users = new Model_User($db);
		
		$this->posts->setUsers($this->users);
		
	}	
   
    /**
     * Returns $_fields.
     *
     * @see Model_Forum::$_fields
     */
    public function get_fields () {
        return $this->_fields;
    }
	
	public function get_forum( $forum_id = null, $where = null, $with_posts = false )
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

		$this->limit( 0, 1);
		
		$forum = $this->query();
		if( $this->_db->getNumRows() > 0 ){
			$forum = $forum[0];
			$forum->topics = null;
			$forum->users = $this->users;
			$forum->count  =  $this->posts->count_posts($forum_id, ' AND `is_topic`=1');
			if($with_posts) $forum->topics =  $this->posts->get_posts($forum_id, ' AND `is_topic`=1');
			
			return $forum; 
		}
		else{
			return null;
		}
	}		
	
	public function get_forums($where = null)
	{
		$this->select( $this->_table );
		
		if( $where !== NULL){
			$this->where( '1', 1);
			$this->anyClause($where);
		}

		if( $this->limit!==null && $this->offset!==null  ){
			$this->limit( $this->offset, $this->limit);
		}
		$this->orderBy('sort');		
		$forum = $this->query(false);
		if( $this->_db->getNumRows() > 0 ){
			return $this->query();
		}
		else{
			return null;
		}
	}	

	/**
	 *  Add post 
	 * @param object $user_id
	 * @param object $post
	 * @param object $content
	 * @param object $parent_id [optional]
	 * @return 
	 */
	public function add_forum(  $name , $description ){
		$fields = $this->_fields;

		foreach($fields AS $k => $v){
			if( isset($$k) && $$k!=null){
				$fields[$k]=$$k;
			} else{
				unset($fields[$k]);
			}
		}
		$fields['sort'] = $max = $this->field($this->_table, "MAX(sort)+1", null, 'max');

		$sql = $this->insert( $this->_table, $fields);	
				
	}

	/**
	 * Update post 
	 * @param object $post_id
	 * @param object $user_id
	 * @param object $post
	 * @param object $content
	 * @return 
	 */
	public function upd_forum( $forum_id, $name = null, $description = null){
		$fields = $this->_fields;

		foreach($fields AS $k => $v){
			if( isset($$k) && $$k!=null){
				$fields[$k]=$$k;
			} else{
				unset($fields[$k]);
			}
		}

		$this->update($this->_table, $forum_id, $fields);
		return true;
		//$db_closure = $this->update( $this->_table, $post_id);;
	}
		
	/**
	 * Delete post
	 * @param object $post_id
	 * @return 
	 */
	public function del_forum( $post_id ){
			return $this->delete($this->_table, $post_id);
	}

}
?>