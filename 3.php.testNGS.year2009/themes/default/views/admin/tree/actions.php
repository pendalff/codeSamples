<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
if(isset($act)){
	$_GET['act']=$act;	
}
	
	if( $level != 0){
	//    var_dump( $controller->sibling_up($node)->loaded() );exit();
	//	if($controller->sibling_up($node)->loaded()){
			echo Core_Actions::link_moveup($id, "Выше");
	//	}
	//	if($controller->sibling_up($node)->loaded()){
			echo Core_Actions::link_movedown($id, "Ниже");
	//	}
	}
	
	if( $level < 2){
		echo Core_Actions::link_add($id, __('add'));
	}
//NOTE! is for two levels
//	if($is_root!==TRUE){
	//Переместить корень нельзя.
	echo Core_Actions::link_edit($id, __('edit'));
	//и удалить нельзя.
	echo Core_Actions::link_delete($id, __('delete') );
	
//	}
?>