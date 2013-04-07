<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */

class Controller_Error extends Controller_Site{
	
	public function action_403()
	{
		$this->request->status = 403;
		$e=$this->request->e;
		
		$this->page->content = View::factory('errors/403')->set('e', $e);
		$this->template = View::factory('site');
		$this->template->bind('page',  	$this->page     );	
		$this->template->bind('body',  	$this->page->content     );

	}
	
	public function action_404()
	{
		$this->request->status = 404;
		$e=$this->request->e;

		$this->page->content = View::factory('errors/404')->set('e', $e);
		$this->template = View::factory('site');
		$this->template->bind('page',  	$this->page     );	
		$this->template->bind('body',  	$this->page->content     );
	}
	public function action_500()
	{
		$this->request->status = 500;
		$e=$this->request->e;
	
		$this->page->content = View::factory('errors/500')->set('e', $e);
		$this->template = View::factory('site');
		$this->template->bind('page',  	$this->page     );	
		$this->template->bind('body',  	$this->page->content     );
	}

}

?>