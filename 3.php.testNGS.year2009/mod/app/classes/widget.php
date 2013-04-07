<?php

class Widget{

/**
 * Простой виджет
 * @param object $params [optional]
 * @return 
 */	
	static function simple(array $params = NULL){
		$defined_prop = array('title','body','attr');
		
		$view = View::factory('admin/widgets/widget');
		if(is_array($params)){
			foreach ($params as $key => $val) {
				if(in_array($key, $defined_prop)){
					$view->set($key,$val);
				}
			}
		}
		return $view;
	}

/**
 * Панель виджетов
 * @param object $widgets
 * @param object $attr [optional]
 * @param object $caption [optional]
 * @param object $footer [optional]
 * @return 
 */	
	static function portlet(array $widgets, array $attr, $caption = false, $footer = false){
		if(!isset($attr['id'])){
			return NULL;
		}
		if(is_array($widgets)){
		$view = View::factory('admin/widgets/portlet');
		$list = '';
		$i=1;
		$id=$attr['id']."_widget";

			foreach ($widgets as $widget) {
				$list.="<li id='{$id}$i'>";
				
				if(is_object($widget))    $list .= $widget;
				
				else 					  $list .= self::simple($widget);
				
				$list.="</li>";
				$i++;
			}
			$view->set('list', $list);
			
			$view->set('attr', $attr);	
			
			if($caption!=false){
			$view->set('caption', $caption);	
			}
			if($footer!=false){
			$view->set('footer', $footer);	
			}
	
		return $view;	
		}
		return NULL;
	}

/**
 * Табулированый виджет
 * 		$elem = array( 
 *						'ajax' => 1,
 *						'id'   => 'tab1',
 *						'title' => 'ajax Tab',
 *						'body'  => 'ajax/content',
 *					 	'attr' => array('class'=>'ajaxtab')
 *					 );
 * @param object $widgets
 * @param object $attr [optional]
 * @return 
 */
	static function tabs(array $widgets, $attr = false){
		if(is_array($widgets)){
		$view = View::factory('admin/widgets/tabs');
		$view->set('elements', $widgets);

		if($attr!=false){
			$view->set('attr', $attr);	
		}
		
		return $view;	
		}
		return NULL;		
	}
	
	static function group(array $elements,$attr = false){
		if(is_array($elements)){
		$view = View::factory('admin/widgets/group');
		$list = '';
			foreach ($elements as $element) {
				$list.="<div>";
				$list .= $element;
				$list.="</div>";
			}
			$view->set('list', $list);
			if($attr!=false){
				$view->set('attr', $attr);	
			}
		return $view;	
		}
		return NULL;		
	}

}
?>
