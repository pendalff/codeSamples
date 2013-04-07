<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Simple users managment
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
class Model_User_Role extends Model{

	/**
	 * @var string $_table
	 */
	
	protected $_table = 'roles';
	/**
	 * @var array $_fields
	 */
	protected $_fields = array( 	'id' 			=> '',
									'name'			=> '',
									'description'	=> '',
								);
								
	public function get_all_roles( $as_simple_array = false ){
		
		$this->select( $this->_table );
		$this->limit( 0, 1000);
		
		$data = $this->query();
		if( $this->_db->getNumRows() > 0 ){
			$ret = array();		
			foreach( $data AS $role ){
				$ret[$role->id] =  $role;	
				if($as_simple_array){
					$ret[$role->id] =  $role->name;		
				}			
			}
			$ban = new stdClass;
			$ban->id = 9999;
			$ban->name = 'Banned';
			$ban->description = 'Banned user';
			$ret[9999] = 	$ban;
			return $ret;
		}
		else{
			return null;
		}		
	}
	
	/**
	 * add role
	 * @param object $name
	 * @param object $description
	 * @return 
	 */
	public function add_role( $name, $description ){
		$this->_fields['name'] 		  = $name;
		$this->_fields['description'] = $description;
		return $this->insert( $this->_table, $this->_fields );
	}
	
	/**
	 * Update role
	 * @param object $id
	 * @param object $username
	 * @param object $password
	 * @param object $email
	 * @return 
	 */
	public function update_role( $id, $name, $description ){
		$this->_fields['name'] 		  = $name;
		$this->_fields['description'] = $description;
		$this->update( $this->_table, $id, $this->_fields);
	}
	
	/**
	 * Delete role
	 * @param object $id
	 * @return 
	 */
	public function delete_role( $id ){
		$this->delete( $this->_table, $id);
	}
}
?>