<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Simple users managment
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
class Model_User_Roles extends Model{

	/**
	 * @var string $_table
	 */
	
	protected $_table = 'roles_users';
	/**
	 * @var array $_fields
	 */
	protected $_fields = array( 	'user_id' 			=> '',
									'role_id'			=> ''
								);

	/**
	 * get user roles
	 * @param object $user_id
	 * @return 
	 */
	public function get_user_roles( $user_id ){
		$this->select( $this->_table, 'role_id' );
		$this->where( 'user_id', $user_id);
		
		$this->limit( 0, 1000);
		$ret = $this->query();
		if( count( $ret ) > 0 ){
			$roles = array();
			foreach( $ret AS $role ){
				$roles[] = $role->role_id;
			}
			$ret = $roles;
		}
		return $ret;
	}	

	/**
	 * add role
	 * @param object $user_id
	 * @param object $role_id
	 * @return 
	 */
	public function add_user_role( $user_id, $role_id ){
		$this->_fields['user_id'] = $user_id;
		$this->_fields['role_id'] = $role_id;
		return $this->insert( $this->_table, $this->_fields );
	}	
	
	/**
	 * update user roles
	 * @param object $user_id
	 * @param object $new
	 * @return 
	 */
	public function update_user_roles( $user_id, array $new ){
		
		$user = Model::factory('user');
		$user = $user->get_user( $user_id );
		if( $user != null){
			$old = $user->roles;
			
			$for_delete = array_diff( $old, $new );
			$for_add    = array_diff( $new, $old );
			if( $for_delete != null ){
				foreach( $for_delete AS $del){
					$this->delete_user_role( $user_id, $del);
				}
			}
			if( $for_add 	!= null ){
				foreach( $for_add AS $add){
					$this->add_user_role( $user_id, $add);
				}				
			}
			
			if($for_add !=null && $for_delete!=null) Auth::logout();
			
			return true;
		}
		return false;
		
	}
	
	/**
	 * delete user role
	 * @param object $user_id
	 * @param object $role_id
	 * @return 
	 */
	public function delete_user_role( $user_id, $role_id ){
		try
		{
			$sql = "DELETE FROM ".$this->_table." WHERE user_id=:user_id AND role_id=:role_id";
			$stmt = $this->_db->prepare($sql);
			$stmt->bindParam(":user_id", $user_id);
			$stmt->bindParam(":role_id", $role_id);
			$stmt->execute();
			$this->log($sql);
		}
		catch(Exception $e)
		{
			$this->errors[] = $e->getMessage();
		}
	}
	
	/**
	 * delete user all roles
	 * @param object $user_id
	 * @return 
	 */
	public function delete_user_roles( $user_id ){
		try
		{
			$sql = "DELETE FROM ".$this->_table." WHERE user_id=:user_id";
			$stmt = $this->_db->prepare($sql);
			$stmt->bindParam(":user_id", $user_id);
			$stmt->execute();
			$this->log($sql);
		}
		catch(Exception $e)
		{
			$this->errors[] = $e->getMessage();
		}
		return true;
	}
	
	public function has_role( $user_id, $role_id){
		$user = Model::factory('user');
		$user = $user->get_user( $user_id );
		if( $user != null){
			return in_array( $role_id, $user->roles);
		}		
		return false;
	}
		
}
?>