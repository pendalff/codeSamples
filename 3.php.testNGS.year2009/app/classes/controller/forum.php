<?php defined('SYSPATH') or die('No direct script access.');
/**
 * 
 */
class Controller_Forum extends Controller_Site
{
	protected $_model = null;
	
	
	public function __construct(Request $req)
	{
		parent::__construct( $req ); 
		$this->_model = new Model_Forum;
		
	}
		
	public function action_show(){ 
		$forum_id = (int) $this->request->param('id', 0);
		// Если не указан конкретный форум - отправляем к списку
		if(!$forum_id){
			$this->request->redirect( URL::site('forum/list') );
		}
		else{
			
			$per_page = arr::get((array)$this->_configs,'topic_per_page', 20);
			$page = $this->request->param('page', 1);
			$offset = $per_page * ($page-1);			
			
			$model = $this->_model;
			$model_posts = $this->_model->posts;
			
			$forum = $model->get_forum( $forum_id );
			
			if($forum===null) throw new SMVC_Exception404('Извините, указанный форум не существует.');
			
			$model_posts->setLimit($per_page);
			$model_posts->setOffset($offset);
			$topics = $model_posts->get_posts($forum_id, ' AND is_topic=1 ');

			$forum->topics = $topics;
			
			$pag_data = array
			(
			  'total_items'     => $forum->count,
			  'items_per_page'  => $per_page,
			  'current_page'     => array
			  (
			      'source'     => 'route',
			      'key'         => 'page'
			  ),
			  'auto_hide'         => true,
			//  'view'                => 'site/pagination',  
			);	
			$forum->paging = Pagination::factory($pag_data);	
			$this->page->content = View::factory('forum/show')->bind('forum',$forum);
		}

	}
 
	public function action_topic(){
		$topic_id = (int) $this->request->param('id', 0);
		// Если не указан конкретный форум - отправляем к списку
		if(!$topic_id){
			$this->request->redirect( URL::site('forum/list') );
		}
		else{
			//Db_ClosureTable::debug();
			$per_page = arr::get((array)$this->_configs,'post_per_page', 20);
			$page = $this->request->param('page', 1);
			$offset = $per_page * ($page-1);			
			
			$model = $this->_model;
			$closure = $this->_model->posts->_closure;
			$closure->setLimit($per_page);
			$closure->setOffset($offset);			
			$topic = $closure->asNestedTree( $topic_id );//, array('is_topic'=>0)
		//	$topic['count']= $closure->getcountTree($topic_id)->count;
		//	var_dump();
		//	var_dump($topic);
			if($topic['count']==0) throw new SMVC_Exception404('Извините, указанной темы не существует.');
						
			$pag_data = array
			(
			  'total_items'     => $topic['count'],
			  'items_per_page'  => $per_page,
			  'current_page'     => array
			  (
			      'source'     => 'route',
			      'key'         => 'page'
			  ),
			  'auto_hide'         => true,
			//  'view'                => 'site/pagination',  
			);	
			$topic['paging'] = '';//Pagination::factory($pag_data);	
			$this->page->content = View::factory('forum/topic')->bind('topic',$topic)->bind('model',$this->_model);
			
		}
	}

	public function action_tree(){
		$forum_id = (int) $this->request->param('id', 0);
		// Если не указан конкретный форум - отправляем к списку
		if(!$forum_id){
			$this->request->redirect( URL::site('forum/list') );
		}
		else{
	    	$per_page = arr::get((array)$this->_configs,'topic_per_page', 20);
			$page = $this->request->param('page', 1);
			$offset = $per_page * ($page-1);		
			
			$model = $this->_model;
			$model_posts = $this->_model->posts;
			
			$forum = $model->get_forum( $forum_id );
			
			if($forum===null) throw new SMVC_Exception404('Извините, указанный форум не существует.');
			
			$model_posts->setLimit($per_page);
			$model_posts->setOffset($offset);
			$posts = array();
			
			if($forum->count>0){
				$topics = $model_posts->get_posts($forum_id, ' AND is_topic=1 ');

				$closure = $this->_model->posts->_closure;
				foreach($topics AS $topic){
					$posts[] = $closure->asNestedTree( $topic->post_id );
				}
				
			}
			
			$forum->topics = $posts;
			// $forum->count = count($posts);	
			$pag_data = array
			(
			  'total_items'     => $forum->count,
			  'items_per_page'  => $per_page,
			  'current_page'     => array
			  (
			      'source'     => 'route',
			      'key'         => 'page'
			  ),
			  'auto_hide'         => true,
			//  'view'                => 'site/pagination',  
			);	

			$forum->paging =  Pagination::factory($pag_data);	
			$this->page->content = View::factory('forum/tree')->bind('forum',$forum)->bind('model',$this->_model);
			
		}
	}
	
	public function action_list(){

		$f = $this->_model->get_forums();

		if($f===null) throw new SMVC_Exception403('Извините, форумы еще не созданы администратором.');
		$view = View::factory('forum/list')->bind('forums',$f)->bind('post_obj',$this->_model->posts);
		$this->page->content = $view;
	}
	
	public function action_addforum(){
		auth::login_role();
		if( auth::has_perm('forum', 'addforum', auth::userid())){
			
			
			$i = (array) $this->_model->get_fields();
			$i['forum_id'] = 0;
			$errors = array();
			
			if (Arr::get($_REQUEST, 'hidden') == 'form_sent')
		     {
		            $keys = array_keys($i);
		
		            $i = Arr::extract($_REQUEST, $keys, NULL);
		            
		            $valid = Validate::factory($i)
		                        ->filter('name','trim')
		                        ->filter('description','trim')
								->filter('name','htmlspecialchars')
		                        ->filter('description','htmlspecialchars')
		                        
		                        ->rules('name',array('not_empty'=>NULL,'min_length'=>array(5),'max_length'=>array(150)))
		                        ->rules('description',array('not_empty'=>NULL,'min_length'=>array(10)))
		                        ->rule('forum_id','not_empty');
			
		            if ( ! $valid->check())
		            {
		                $errors = $valid->errors();    
		            }
					else{
						$post_model = $this->_model;
						$u = $post_model->add_forum(  $i['name'], $i['description']);
						
						$this->request->redirect(url::site('forum/show/'.$u, true));
					}
			}
			//var_dump($i); exit;
			$view = View::factory('forum/forum')->set('input',$i)->set('errors',$errors);
			
			$this->page->content = $view;
		}
		else{
			throw new SMVC_Exception403('Недостаточно прав для редактирования');
		}
	}	
	
	public function action_editforum(){
		auth::login_role();
		$forum_id = (int) $this->request->param('id', 0);
		if($forum_id<1) throw new SMVC_Exception404('Ошибка редактирования.');
		
		$forum = $this->_model->get_forum($forum_id);
		if($forum==null) throw new SMVC_Exception404('Ошибка редактирования. Объект не найден.');
		// $post->user_id == auth::userid() ||	Auth::moder_role(false)	||	Auth::admin_role(false)
	
		if( auth::has_perm('forum', 'editforum', auth::userid())){
			
			
			$i = (array) $forum;
			$errors = array();
			
			if (Arr::get($_REQUEST, 'hidden') == 'form_sent')
		     {
		            $keys = array_keys($i);
		
		            $i = Arr::extract($_REQUEST, $keys, NULL);
		            
		            $valid = Validate::factory($i)
		                        ->filter('name','trim')
		                        ->filter('description','trim')
								->filter('name','htmlspecialchars')
		                        ->filter('description','htmlspecialchars')
		                        
		                        ->rules('name',array('not_empty'=>NULL,'min_length'=>array(5),'max_length'=>array(150)))
		                        ->rules('description',array('not_empty'=>NULL,'min_length'=>array(10)))
		                        ->rule('forum_id','not_empty');
			
		            if ( ! $valid->check())
		            {
		                $errors = $valid->errors();    
		            }
					else{
						$post_model = $this->_model;
						$upd = $post_model->upd_forum( $i['forum_id'], $i['name'], $i['description']);
						$this->request->redirect(url::site('forum/show/'.$i['forum_id'], true));
					}
			}
			//var_dump($i); exit;
			$view = View::factory('forum/forum')->set('input',$i)->set('errors',$errors);
			
			$this->page->content = $view;
		}
		else{
			throw new SMVC_Exception403('Недостаточно прав для редактирования');
		}
	}
	
	public function action_delforum(){
		auth::login_role();
		$forum_id = (int) $this->request->param('id', 0);
		if($forum_id<1) throw new SMVC_Exception404('Ошибка удаления.');
		
	
		if( auth::has_perm('forum', 'delforum', auth::userid())){
			$this->_model->del_forum($forum_id);
			$this->request->redirect(url::site('forum/list', true));

		}
		else{
			throw new SMVC_Exception403('Недостаточно прав для удаления');
		}			
			
	}
	public function action_editpost(){
		auth::login_role();
		$post_id = (int) $this->request->param('id', 0);
		if($post_id<1) throw new SMVC_Exception404('Ошибка редактирования.');
		
		$post = $this->_model->posts->get_post($post_id);
		if($post==null) throw new SMVC_Exception404('Ошибка редактирования. Объект не найден.');
		if( auth::has_perm('forum', 'editpost',$post->user_id)){
			
			
			$i = (array) $post;
			$errors = array();
			
			if (Arr::get($_REQUEST, 'hidden') == 'form_sent')
		     {
		            $keys = array_keys($i);
		
		            $i = Arr::extract($_REQUEST, $keys, NULL);
		            
		            $valid = Validate::factory($i)
		                        ->filter('post','trim')
		                        ->filter('content','trim')
								->filter('post','htmlspecialchars')
		                        ->filter('content','htmlspecialchars')
		                        
		                        ->rules('post',array('not_empty'=>NULL,'min_length'=>array(5),'max_length'=>array(150)))
		                        ->rules('content',array('not_empty'=>NULL,'min_length'=>array(10)))
		                        ->rule('forum_id','not_empty')
		            			->rule('user_id','not_empty');
			
		            if ( ! $valid->check())
		            {
		                $errors = $valid->errors();    
		            }
					else{
						$post_model = $this->_model->posts;
						$upd = $post_model->upd_post( $i['post_id'], $i['post'], $i['content'], $i['user_id'], $i['forum_id'], $i['is_topic'] );
						$this->request->redirect(url::site('forum/show/'.$i['forum_id'], true));
					}
			}
			$view = View::factory('forum/form')->set('input',$i)->set('errors',$errors);
			
			$this->page->content = $view;
		}
		else{
			throw new SMVC_Exception403('Недостаточно прав для редактирования');
		}
	}
	
	public function action_addpost(){
		auth::login_role();

		$forum_id = (int) $this->request->param('forum', 0);
		
		if($forum_id<1) throw new SMVC_Exception404('Не указан форум.');
		
		$parent_id = (int) $this->request->param('id', 0);
		
		
		$i = $this->_model->posts->get_fields();
		$i['forum_id']= $forum_id;
		$i['is_topic']= 0;
		$i['post_id'] = '';
		$i['user_id'] = Auth::userid();
		$i['parent_id'] = '';
		if($parent_id==0){
			$i['is_topic'] = 1;
		}
		else{
			$i['parent_id'] = $parent_id;
		}
		$errors = array();	
		if (Arr::get($_REQUEST, 'hidden') == 'form_sent')
        {
            $keys = array_keys($i);

            $i = Arr::extract($_REQUEST, $keys, NULL);
            
            $valid = Validate::factory($i)
                        ->filter('post','trim')
                        ->filter('content','trim')
						->filter('post','htmlspecialchars')
                        ->filter('content','htmlspecialchars')
                        
                        ->rules('post',array('not_empty'=>NULL,'min_length'=>array(5),'max_length'=>array(150)))
                        ->rules('content',array('not_empty'=>NULL,'min_length'=>array(10)))
                        ->rule('forum_id','not_empty')
            			->rule('user_id','not_empty');
	
            if ( ! $valid->check())
            {
                $errors = $valid->errors();    
            }
			else{
				$post_model = $this->_model->posts;
				$added = (int)$post_model->add_post( $i['user_id'], $i['forum_id'], $i['post'], $i['content'], $i['is_topic'], $i['parent_id'] );
				if($added>0){
						$db_closure = $this->_model->posts->_closure;	
						$ancest = $db_closure->getAncestorsById( $added );
						$a = $ancest->getRows();
						$this->request->redirect(url::site('forum/topic/'.$a[0]['post_id'], true)); 			
				}
				else{
					$mess = isset($i['is_topic']) && $i['is_topic']>0 ? ' темы' : ' ответа';
					$view = View::factory('errors/simple')->set('text','Ошибка добавления '.$mess);
					$this->page->content = $view;
					return;
				}
				
			}
                  			  		
        }

		$view = View::factory('forum/form')->set('input',$i)->set('errors',$errors);
		
		$this->page->content = $view;
	}	
	
	public function action_delpost(){
		auth::login_role();
		$post_id = (int) $this->request->param('id', 0);
		if($post_id<1) throw new SMVC_Exception404('Ошибка редактирования.');
		
		$post = $this->_model->posts->get_post($post_id);
		if($post==null) throw new SMVC_Exception404('Ошибка редактирования. Объект не найден.');
		if( auth::has_perm('forum', 'delpost',$post->user_id)){
			$this->_model->posts->del_post($post_id);
			$this->request->redirect(url::site('forum/list', true));
		}
		else{
			throw new SMVC_Exception403('Недостаточно прав для удаления');
		}			
			
	}
}