<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @package Application
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
class Page extends Page_Base {

	public static $instance = NULL;
	
	/**
	 * 
	 * @param Request $req [optional]
	 * @return Page
	 */
	public static function instance ( Request $req = null ){
		if (self::$instance === NULL )
		{
			  self::$instance = new Page( $req );
		} 
		return self::$instance;
	}
	
}
?>