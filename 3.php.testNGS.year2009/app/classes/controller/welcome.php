<?php defined('SYSPATH') or die('No direct script access.');
/**
 * 
 */
class Controller_welcome extends Controller{
	
	public function action_index(){ 
		//new SMVC_DB_Driver_PDO;
		$this->request->status = 404;
		$this->request->response = ';';
		$this->request->response .= "<a href='".URL::site('welcome/a/1')."'>asdsad</a>";
		$this->request->response .= 'a';
		$db = DB::instance();
		$a = new DB_Abstraction( $db ); 
		$r = $db->doExecute('SELECT * FROM users');
		//$r = $db->getRows(); 
var_dump($r);
$db_closure = new Db_ClosureTable($db, "tp_posts", "posts", "post_id");

//Db_ClosureTable::debug();
$node = arr::get($_REQUEST, 'node',1);;
$res = $db_closure->asNestedTree($node);

var_dump($res);


	}

	public function action_addpost(){
		
 		$phrazes = SMVC::config('postgen')->default;	
		$phraze = $phrazes[rand(0,count($phrazes)-1)];
		
		$id = (int) $this->request->param('id', 1);

		$posts = new Model_post;
		$posts->add_post( 1,1, 'test', $phraze, 0, $id );
	}

	public function action_delpost(){
		Db_ClosureTable::debug();
		$id = (int) $this->request->param('id', 0);
		if($id>0){

		/**
		 * @var Model_Post $posts
		 */
		$posts = Model::factory('post');
		$tree = $posts->get_posts($id);
		$tree = $tree->getDescendantsById($id);
			var_dump($tree->getNumRows());
			$posts->del_post( $id ); 
		
		}
		else{
			throw new SMVC_Exception403('Попытка удалить неизвестный пост');
		}
	}
	
	public function action_forum(){
		$forums = Model::factory('forum');
		
		$f = $forums->get_forum(2);
		var_dump($f);
	}
}


/**		
		$user = Model::factory('User');
		$user1 = $user->get_user(1);
		var_dump( $user->roles->update_user_roles(1, array(1,2)));
		echo $user->log_render();
*/
//		throw new SMVC_Exception404('asd');