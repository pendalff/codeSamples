<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Генератор ссылок и чекбоксов для CRUID действий.
  * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 * @package CMS
 * @subpackage Core
 */
class Core_Actions{
	public static $prefix = NULL;
	public static $uri = NULL;
	
	public static function link_sort($url, $title, $order, $direction = 'asc'){	

	} 
	
	public static function link_add($id = NULL, $text, $label = NULL, $url = NULL){
		if( NULL === $label)	$label = $text;
		
		if($id !== NULL) $id = "/".(int)$id;


		if( NULL === $url)	    $url   = URL::site('admin/cats/add').$id;
		self::set_uri($url);
		$str = "<span class='new'><a href='".self::$uri."' title='".$label."'>".$text."</a></span>";

		return $str;				
	}		
	
	public static function link_edit($id = NULL,$text, $label = NULL,$url = NULL){
		if( NULL === $label)	$label = $text;

		if($id !== NULL) $id = "/".(int)$id;


		if( NULL === $url)	    $url   = URL::site('admin/cats/edit').$id;
		self::set_uri($url);
		$str = "<span class='edit'><a href='".self::$uri."' title='".$label."'>".$text."</a></span>";

		return $str;				
	}
	
	public static function link_delete($id = NULL, $text, $label = NULL,$url = NULL){
		if( NULL === $label)	$label = $text;
	
		if($id !== NULL) $id = "/".(int)$id;


		if( NULL === $url)	    $url   = URL::site('admin/cats/del').$id;
		self::set_uri($url);
		$str = "<span class='delete'><a href='".self::$uri."' title='".$label."'>".$text."</a></span>";

		return $str;
	}
	
	public static function link_moveup($id = NULL, $text, $label = NULL,$url = NULL){
		if( NULL === $label)	$label = $text;
	
		if($id !== NULL) $id = "/".(int)$id;


		if( NULL === $url)	    $url   = URL::site('admin/cats/moveup').$id;
		self::set_uri($url);
		$str = "<span class='move_up'><a href='".self::$uri."' title='".$label."'>".$text."</a></span>";

		return $str;
	}
	public static function link_movedown($id = NULL, $text, $label = NULL,$url = NULL){
		if( NULL === $label)	$label = $text;
	
		if($id !== NULL) $id = "/".(int)$id;


		if( NULL === $url)	    $url   = URL::site('admin/cats/movedown').$id;
		self::set_uri($url);
		$str = "<span class='move_down'><a href='".self::$uri."' title='".$label."'>".$text."</a></span>";

		return $str;
	}		
	public static function link_move($id = NULL, $text, $label = NULL, $url = NULL){
		if( NULL === $label)	$label = $text;
	
		if($id !== NULL) $id = "/".(int)$id;

		if( NULL === $url)	    $url   = URL::site( Request::instance() -> uri()).$id;

		
		self::set_uri($url);
		$str = "<span class='move'><a href='".self::$uri."' title='".$label."'>".$text."</a></span>";

		return $str;
	}

	/**
	 * Возвращает абсолютный путь от корня сайта к статичному assets файлу.
	 * Использует SMVC::find_file
	 * @param string $name
	 * @return string
	 */	
	public static function link_static ( $name ) {
		$filename = SMVC::find_file('assets', $name, false);
		if (empty ($filename)) return false;
		if (class_exists('JConfig')){
			 $path =  substr($filename, strlen (JPATH_ROOT));
		}
		else{
		 	$path =  substr($filename, strlen (DOCROOT));			
		}
	
		return $path;
	}

	/**
	 * Возвращает алиас или id объекта для построения ссылок
	 * @param Jelly_Model $object
	 * @return mixed
	 */	
	public static function alias ( Jelly_Model $object , $key = 'alias') {
		$alias = $object->$key;
		$alias = trim($alias);
		return !empty ( $alias ) ? $alias : $object->id();
	}
	
	public static function link_box($id, $name = 'ids', $attr = NULL){
		if ($attr === NULL){
			$attr['id'] = $name.$id;
		}
		return Form::checkbox( $name.'[]',  $id , FALSE , $attr );		
	}
	
	public static function set_uri( $url ){
		self::$uri = ($url);
	}
	
	public static function get_uri(  $params = NULL){
		if( NULL != $params ){
			self::set_uri($params);
		}
		return self::$uri;
	}
}
