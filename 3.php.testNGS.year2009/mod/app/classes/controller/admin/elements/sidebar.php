<?php
class Controller_Admin_Elements_Sidebar extends Controller_Admin_Base {
	
	public function before() {
		$this->template = "admin/sidebar/".$this->request->action;
		parent::before();
       // $this->_is_ajax = true;

		//= View::factory("sidebar/".$this->request->action);	
		$this->page->add_CSS('sidebars.css');

	//	$this->addStyles(array('sidebars'));
	}
	
	public function action_top(){
        if($this->_is_ajax !== TRUE){
		$config =  SMVC_Config::instance()->load('sidebar/top')->default;
		
		$buttons = $config['buttons'];
		
		$buttonView = View::factory('admin/sidebar/button')->bind('id',$id)->bind('button',$button);
		$this->template->buttons = array();
/*		
		$active = 'home';
		$headtext = 'Магазин';
		$caption_icon = $this->url."img/icons/home128.png";
*/		
		$active = 'product';
		$headtext = 'Продукты';
		$caption_icon = $this->request->uri()."img/icons/cart128.png";

		if(isset($_REQUEST['act'])){
			$active = $_REQUEST['act'];
		}
		
		foreach($buttons AS $id => $button){
			$button['icon'] = $this->request->uri()."img/icons/".$button['icon'];
			if($id == $active){
				$button['active'] = 1;
				$headtext = $button['label'];
				
				$caption_icon = $button['icon']."128.png";
			}
			$this->template->buttons[]=$buttonView->render();
			
		}
		$this->template->caption_icon = $caption_icon;
		$this->template->caption = "Управление электронным магазином <small>[".$headtext."]</small>";
	}
	}
	
	public function action_subtop(){
		$active = Request::instance()->action;
		if($active){
			$group = $active;
			$icon = $active;
		}
		else{
			$group = 'default';
			$icon = 'cats';
		}
		
		$config =  SMVC_Config::instance()->load('sidebar/subhead')->$group;

		$buttons = $config['buttons'];
		
		$buttonView = View::factory('admin/sidebar/button')->bind('id',$id)->bind('button',$button);
		$this->template->buttons = array();
		
		
		if(isset($_REQUEST['mode'])){
			$active = $_REQUEST['mode'];
			$icon = $_REQUEST['mode'];
		}		

		$caption_icon = $this->request->uri()."img/icons/".$icon."48.png";

		if(!defined('JPATH_ROOT')){
			define('JPATH_ROOT', DOCROOT);
		}
		foreach($buttons AS $id => $button){
			$size = 48;
			if(isset( $button['size'])){
				$size = $button['size'];
			}
			$filename 		= SMVC::find_file('assets/img/icons', $button['icon'].$size, 'png');
			$button['icon'] = str_replace(JPATH_ROOT, rtrim( Joomla_Utils::getURL(), "/"), $filename);

			
			if($button['link'] == $this->request->uri()){
				$button['active'] = 1;
				$headtext = $button['label'];
				
				$caption_icon = $button['icon']."128.png";
			}
			$this->template->buttons[]=$buttonView->render();
			
		}
		$this->template->caption_icon = $caption_icon;
		$this->template->caption = "sss";
		$this->page->content = $this->template->render();
	}
		
	public function action_bottom(){

	}
	
	public function after() {
	  $this->_is_ajax = TRUE;
	  parent::after();
	}
}