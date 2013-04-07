<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Контроллер для создания различных виджетов
 */
class Controller_Admin_Elements_Widget extends Controller_Admin_Base{
	
	public function before(){
		parent::before();
	}
	public function after(){

	}
		
	public function action_SSW() {
		
		if(isset ($_COOKIE["theme"]))
		{
		    $theme = $_COOKIE["theme"];
		} else {
			$theme = 'blue';
		}

		$view = View::factory('admin/widgets/widget/style_switcher')->set('theme',$theme);
		$this->request->response = "OLD";
		return;
		$this->template = $view;
		

		$folder = realpath( DOCROOT."..".DS."assets".DS."css".DS."themes" );

		$theme_list = glob($folder."/*");	
		$themes = array();
		foreach( $theme_list as $th){
			$key = $this->getLast($th);
			$themes[$key] = ucfirst ($key);
		}
		$this->template->themes = $themes;
	}
	
	public function action_ajaxinfo() {
		
		$this->page->add_JS('jquery/gts/ajaxinfo.js');
		$simple = View::factory('admin/widgets/widget');
		
		$widget = View::factory('admin/widgets/widget/ajaxinfo');
		
		$simple->set('title', 'Информация:');
		
		$simple->set('body',$widget);

		$this->template = $simple;
		//return array('sdfsdfsdf');
		$this->request->response = $simple;
	}

	private function getLast($str) {
		$arr=explode(DS,$str);
		return array_pop($arr);
	}
	
}
?>