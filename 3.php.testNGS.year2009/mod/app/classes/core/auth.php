<?php defined('SYSPATH') or die('No direct script access.');
/**
 * 
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
class Core_Auth{
	public static $instance;
	const REMEMBER_TIME = 1209600;
	const LOGIN_ROLE     = 1;
	const ADMIN_ROLE     = 2;
	const MODER_ROLE     = 3;
	const BANNED_ROLE    = 9999;

	public $user = null;

	public static function instance (){
		if (self::$instance === NULL )
		{
			  self::$instance = new Auth( );
		} 
		return self::$instance;
	}	
	
	protected function __construct()
	{
		
	}
	/**
	 * Logs a user in.
	 *
	 * @param   string   username
	 * @param   string   password
	 * @param   boolean  enable auto-login
	 * @return  boolean
	 */
	public function login($username, $password, $remember = true, $cript_pass = TRUE)
	{
		$user_model = Model::factory('user');
		$user 		= $user_model->get_user( NULL, " AND username ='".$username."'" );
	
		$password   = $cript_pass ? md5($password) : $password;
		// If the passwords match, perform a login
		if ($user != null AND $user->password === $password && in_array( self::LOGIN_ROLE, $user->roles))
		{

			if ($remember === TRUE)
			{
				// Set the autologin cookie
				cookie::set('autologin', $user->id.":".$password, self::REMEMBER_TIME);
			}
			$this->user = $user;

			return TRUE;
		}
		elseif($user != null AND !in_array( self::LOGIN_ROLE, $user->roles)){
				Request::instance()->redirect(url::site('users/needloginb',true));
		}
		// Login failed
		return FALSE;
	}

	/**
	 * Logs a user in, based on the autologin cookie.
	 *
	 * @return  boolean
	 */
	public function auto_login()
	{
		if(Request::current()->param('install', null)) return FALSE;

		if ($token = cookie::get('autologin'))
		{
			
			list( $user_id, $password ) = explode(":", $token);
			$user_model = Model::factory('user');
			$user 		= $user_model->get_user( $user_id );
			// If the passwords match, perform a login
			if ($user != null AND $user->password === $password && in_array( self::LOGIN_ROLE, $user->roles))
			{
					// Set the autologin cookie
				cookie::set('autologin', $user->id.":".$password, self::REMEMBER_TIME);
				$this->user = $user;
				return TRUE;
			}
			elseif($user != null AND !in_array( self::LOGIN_ROLE, $user->roles)){
				Request::instance()->redirect(url::site('users/needloginb',true));
			}
			return FALSE;
		}

		return FALSE;
	}
	
	/**
	 * Log a user out and remove any auto-login cookies.
	 * @return  boolean
	 */
	public static function logout( $to = null)
	{
		if ($token = cookie::get('autologin'))
		{
			// Delete the autologin cookie to prevent re-login
			cookie::delete('autologin');
		}
		if(!empty($to)){
			Request::instance()->redirect(url::site($to,true));
		}
		return true;
	}
	
	public static function set_auto( $id )
	{
		$user_model = Model::factory('user');
		$user 		= $user_model->get_user( $id );
		if ($user != null && in_array( self::LOGIN_ROLE, $user->roles))
		{
				// Set the autologin cookie
				cookie::set('autologin', $user->id.":".$user->password, self::REMEMBER_TIME);
		}

	}

	public static function userid(){
		$obj = self::instance();
		if($obj->user!=null){
			return $obj->user->id;
		}	
		return null;	
	}		
	
	public static function user(){
		$obj = self::instance();
		if($obj->user!=null){
			return $obj->user;
		}	
		return null;	
	}		
	
	public static function logined(){
		$obj = self::instance();
		if($obj->user!=null && in_array(self::LOGIN_ROLE, $obj->user->roles)){
			return true;
		}	
		return false;	
	}	


	public static function need_login(){
		if(!self::logined()){
			Request::instance()->redirect(url::site('users/needlogin',true));
		}		
	}	
	
	public static function banned_role(){
		$obj = self::instance();
		if($obj->user!=null && in_array(self::BANNED_ROLE, $obj->user->roles)){	
			return true;
		}	
		return false;
	}		
	
	public static function login_role($redirect = true){
		self::need_login();
		$obj = self::instance();
		if(in_array(self::LOGIN_ROLE, $obj->user->roles)){	
			return true;
		}	
		if($redirect)	
		Request::instance()->redirect(url::site('users/loginrole',true));
	}	
	
	public static function admin_role($redirect = true){
		self::need_login();
		$obj = self::instance(); 
		if(in_array(self::ADMIN_ROLE, $obj->user->roles)){	
		
			return true;
		}	
		if($redirect)	
		Request::instance()->redirect(url::site('users/adminrole',true));
	}	
	public static function moder_role($redirect = true){
		self::need_login();
		$obj = self::instance();
		if(in_array(self::MODER_ROLE, $obj->user->roles)){	
			return true;
		}
		if($redirect)	
		Request::instance()->redirect(url::site('users/moderrole',true));
	}	

	protected $simple_acl = array(
		'forum' => array(
				'addpost' => array('login'),
				'editpost' => array('self','admin','moder'),
				'delpost'  =>  array('self','admin','moder'),
				
				'addforum' =>	array('admin','moder'),
				'editforum' =>  array('admin','moder'),
				'delforum' => 	array('admin'),  
		),
		'users' => array(
				'editroles' => array('admin'),
				'editpass'  => array('admin'),
				'edit' => array('self','admin','moder'),
		),
		'config' => array(
				'edit' => array('admin'),
		),
	);
	public static function has_perm($resource, $action, $user_id = null){
		if( !self::logined() ) return false;
		self::need_login();
		$obj = self::instance();

		if(isset($obj->simple_acl[$resource]) && isset($obj->simple_acl[$resource][$action])){
			$needs = $obj->simple_acl[$resource][$action];
			$has = false;
			foreach($needs AS $need){
				if($need=='self' && $user_id!=null){
					$has = $obj->user->id == $user_id;
				}
				elseif($user_id!=null){
					$method = new ReflectionMethod($obj, $need."_role");			
					$has = $method->invokeArgs(NULL, array(false));

				}
				if($has){ return true;  }
			}
			
			return $has;
		}
		return false;
	}
}
