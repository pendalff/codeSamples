<?php defined('SYSPATH') or die('No direct script access.');
/**
 * 
 */
class Controller_Users extends Controller_Site
{
	/**
	 * @var Model_User $posts
	 */
	public $users = null;
	/**
	 * @var Model_Post $posts
	 */
	public $posts = null;
	
	public function __construct(Request $req)
	{
		parent::__construct($req);
		$this->posts = new Model_Post();
		$this->users = new Model_User();
	}	
	
	public function action_edit(){
		auth::login_role();
		$user_id = (int) $this->request->param('id', auth::userid());
		
		if($user_id<1) throw new SMVC_Exception404('Ошибка редактирования.');
		if( auth::has_perm('users', 'edit', $user_id)){
			
			$user = $this->users->get_user($user_id);	
			if($user==null) throw new SMVC_Exception404('Ошибка редактирования. Объект не найден.');		
			$i = (array) $user;
			$i['password_new'] = null;
			$i['password_old'] = null;
			unset($i['password']);
			$errors = array();
		    $keys = array_keys($i);
			
			$roles = null;
			if( auth::has_perm('users', 'editroles', auth::userid())){
				$role_model = new Model_User_Role;
				$roles = $role_model->get_all_roles();
				unset($roles[9999]);
			}

			if (Arr::get($_REQUEST, 'hidden') == 'form_sent')
		     {

		            $i = Arr::extract($_REQUEST, $keys, NULL);

		            $valid = Validate::factory($i)
		                        ->filter('username','trim')
								->filter('username','htmlspecialchars')
		                        ->filter('email','trim')
							
								->filter('password_new','trim')
		                        ->filter('password_old','trim')
		                        
		                        ->rules('username',array('not_empty'=>NULL,'alpha_dash'=>array(),'max_length'=>array(100)))
		                        ->rules('email',array('not_empty'=>NULL,'email'=>NULL))
		            			->rule('id','not_empty');
			
		            if ( ! $valid->check())
		            {
		                $errors = $valid->errors();    
		            }
					else{
						$i['password'] = null;
						//var_dump(!auth::has_perm('users', 'editpass', auth::userid()));
						if(!auth::has_perm('users', 'editpass', auth::userid()) && auth::user()->password != md5($i['password_old'])){
							$errors['password_old'] = array('не совпадает');
						}
						else{
							if( empty($i['password_new']) && !auth::has_perm('users', 'editpass', auth::userid())){
								$errors['password_new'] = array('not_empty');
							}else{
								$i['password'] = $i['password_new'];
							}
						}
						if(empty($errors)){
							$model = $this->users;
							$upd = $model->update_user($i['id'], $i['username'], $i['email'], $i['password'], $i['roles']);
							$this->request->redirect(url::site('users/show/'.$i['id'], true));
						}
						
					}
			}
			
			$view = View::factory('users/form')->set('input',$i)->set('errors',$errors)->set('roles',$roles)->set('self_user', !auth::has_perm('users', 'editpass',$user_id));
			
			$this->page->content = $view;
		}
		else{
			throw new SMVC_Exception403('Недостаточно прав для редактирования');
		}
	}
	public function action_register(){
	
		$i = $this->users->get_fields();	
		$i['id'] = 0;

		$errors = array();
	    $keys = array_keys($i);
		$roles = null;		
		if (Arr::get($_REQUEST, 'hidden') == 'form_sent')
		     {

		            $i = Arr::extract($_REQUEST, $keys, NULL);

		            $valid = Validate::factory($i)
		                        ->filter('username','trim')
								->filter('username','htmlspecialchars')
		                        ->filter('email','trim')
								->filter('password','trim')
		                        ->rules('username',array('not_empty'=>NULL,'alpha_dash'=>array(),'max_length'=>array(100),'model_user::exist_login'=>array()))
		                        ->rules('email',array('not_empty'=>NULL,'email'=>array(),'model_user::exist_email'=>array()))
								->rules('password',array('not_empty'=>NULL,'max_length'=>array(100)));

		            if ( ! $valid->check())
		            {
		                $errors = $valid->errors();    
		            }
					else{
							$model = $this->users;
							
							$id = $model->add_user($i['username'], $i['password'],$i['email']);

							$this->request->redirect(url::site('users/show/'.$id, true));
					}
			}
			
			$view = View::factory('users/form')->set('input',$i)->set('errors',$errors)->set('roles',$roles)->set('self_user', false);
			
			$this->page->content = $view;
	}
		
	public function action_show(){ 
		$user_id = (int) $this->request->param('id', 0);
		// Если не указан конкретный форум - отправляем к списку
		if(!$user_id){
			$this->request->redirect( URL::site('users/list') );
		}
		else{
			$user = $this->users->get_user($user_id);

			if($user===null) throw new SMVC_Exception404('Извините, указанный пользователь не существует.');
			$user->all_roles = $this->users->role->get_all_roles();
			$user->count_posts = $this->posts->count_user_posts($user_id);

			$this->page->content = View::factory('users/show')->bind('user',$user);
		}
	}

	public function action_posts(){
		$user_id = (int) $this->request->param('id', 0);
		// Если не указан конкретный форум - отправляем к списку
		if(!$user_id){
			$this->request->redirect( URL::site('users/list') );
		}
		else{
			$user = $this->users->get_user($user_id);

			if($user===null) throw new SMVC_Exception404('Извините, указанный пользователь не существует.');

			
			
			$per_page = arr::get((array)$this->_configs,'post_per_page', 20);
			$page = $this->request->param('page', 1);
			$offset = $per_page * ($page-1);	

			$user->count_posts = $this->posts->count_user_posts($user_id);

			$this->posts->setLimit($per_page);
			$this->posts->setOffset($offset);			

			$user->posts = $this->posts->get_user_posts($user_id);
			
			$pag_data = array
			(
			  'total_items'     => $user->count_posts,
			  'items_per_page'  => $per_page,
			  'current_page'     => array
			  (
			      'source'     => 'route',
			      'key'         => 'page'
			  ),
			  'auto_hide'         => true,
			//  'view'                => 'site/pagination',  
			);	
			$user->paging = Pagination::factory($pag_data);
									
			$this->page->content = View::factory('users/posts')->bind('user',$user);
		}
		
	}

	public function action_list(){
		$forums = new Model_forum;
		
		$f = $forums->get_forums();

		if($f===null) throw new SMVC_Exception403('Извините, форумы еще не созданы администратором.');
		$view = View::factory('forum/list')->bind('forums',$f)->bind('post_obj',$forums->posts);
		$this->page->content = $view;
	}
	
	public function action_login(){
		if($this->_auth->user!=null){
			$this->request->redirect('/');
		}
		$data = array('f','l','p');
		$input = arr::extract($_REQUEST, $data, null);
		if(!$input['f'] || !$input['l'] ||!$input['p']){
			$this->page->content = View::factory('users/login')->set('i',$input);
		}
		else{
			$ok = $this->_auth->login($input['l'], $input['p']);
			if(!$ok) $this->page->content = View::factory('users/login')->set('error','Неправильный логин или пароль')->set('i',$input);
			else{
				$this->request->redirect('/');
			}
		}
	}
	
	public function action_logout(){
		auth::logout('/');
	}

	
	public function action_needlogin(){
		if($this->_auth->user==null ){
			$this->request->redirect(url::site('users/needloginp',true));
		}

		$this->page->content = View::factory('users/login/need');
	}

	public function action_needloginp(){
		$this->page->content = View::factory('users/login/need');
	}	
	
	public function action_needloginb(){
		$this->page->content = View::factory('users/login/need')->set('b',true);
	}		
	
	public function action_loginrole(){
		throw new SMVC_Exception403('Для выбранного действия необходимо иметь права зарегистрированного пользователя');
	}
	
	public function action_adminrole(){
		throw new SMVC_Exception403('Для выбранного действия необходимо иметь права администратора');
	}
	
	public function action_moderrole(){
		throw new SMVC_Exception403('Для выбранного действия необходимо иметь права модератора');
	}
}
