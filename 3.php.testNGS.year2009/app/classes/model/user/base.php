<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Simple users managment
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
class Model_User_Base extends Model{

	/**
	 * @var string $_table
	 */
	
	protected $_table = 'users';
	/**
	 * @var array $_fields
	 */
	protected $_fields = array( 'id' 			=> '',
								'username'		=> '',
								'password'		=> '',
								'email'			=> ''
								);
	/**
	 * @var object Model_Roles $roles
	 */							
	public $roles = null;

	/**
	 * @var Model_Role $role
	 */							
	public $role = null;
	
	

	public function __construct($db = NULL)
	{
		parent::__construct($db);
		$this->roles = new Model_User_Roles($db);
		$this->role  = new Model_User_Role($db);
	}	
		
	/**
	 * get user
	 * @param object $id [optional]
	 * @param object $where
	 * @return 
	 */
	public function get_user( $id = NULL, $where = null ){
		
		$this->select( $this->_table );
		
		if( $id !== NULL){
			$this->where( 'id', $id);
		}
		elseif( $where !== NULL){
			$this->where( '1', 1);
			$this->anyClause($where);
		}
		$this->limit( 0, 1);
		
		$user = $this->query();
		if( $this->_db->getNumRows() > 0 ){
			$user = $user[0];
			$user->roles =  $this->roles->get_user_roles($user->id);
			$this->user = $user;
			return $user;
		}
		else{
			return null;
		}

	}

	/**
	 * get users
	 * @param object $where [optional]
	 * @param object $offset [optional]
	 * @param object $limit [optional]
	 * @return 
	 */
	public function get_users( $where = null, $offset = 0, $limit = 100 ){
		
		$this->select( $this->_table );
		if( $where !== NULL){
			$this->where( '1', 1);
			$this->anyClause($where);
		}
		$this->limit( $offset, $limit);
		
		$users = $this->query();
		if( $this->_db->getNumRows() > 0 ){
			foreach( $users AS $user ){
				$user->roles = $this->roles->get_user_roles( $user->id );				
			}
			return $users;
		}
		else{
			return null;
		}		

	}

	public static function exist_login( $username ){
		$user = Model::factory('user')->get_user( null, " AND username ='".$username."'");
		return $user == null;
	}
	
	public static  function exist_email( $email ){
		$user =  Model::factory('user')->get_user( null, " AND email ='".$email."'");		
		return $user == null;
	} 
	
	/**
	 * Add user to system
	 * @param object $username
	 * @param object $password
	 * @param object $email
	 * @return 
	 */
	public function add_user( $username, $password, $email, array $roles = array(1) ){
		$fields = $this->_fields;
		unset($fields['id']);
		$fields['username'] = $username; 
		$fields['password'] = md5($password);
		$fields['email'] 	= $email;
		
		$id = $this->insert( $this->_table, $fields);
		if( $id ){
			foreach( $roles AS $role ){
				$this->roles->add_user_role( $id, $role );
			}
			Auth::logout();
			Auth::set_auto($id);
		}
		return $id;
	}
	
	/**
	 * Update user system
	 * @param object $id
	 * @param object $username
	 * @param object $password
	 * @param object $email
	 * @param object $roles
	 * @return 
	 */
	public function update_user( $id, $username, $email, $password = null, array $roles = null ){
		$fields = $this->_fields;
		$fields['username'] = $username; 
		
		if($password!=null){
			$fields['password'] = md5($password);
			auth::logout();
		}
		else{
			unset($fields['password']);
		}
		$fields['email'] 	= $email;
		$this->update( $this->_table, $id, $fields);
		if( $roles != null ){
			$this->roles->update_user_roles( $id, $roles );
		}
		return true;
	}
	
	/**
	 * Delete user
	 * @param object $id
	 * @return 
	 */
	public function delete_user( $id ){
		$this->delete( $this->_table, $id);
		$this->roles->delete_user_roles($id);
		return true;
	}
}
?>