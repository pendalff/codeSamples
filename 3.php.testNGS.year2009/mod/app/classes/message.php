<?php defined('SYSPATH') or die('No direct script access.');
class message extends Core_message{
	
	static protected $_template = 'messages/popup';
	
	static public function render($type = NULL, $tag = NULL)
	{
		parent::$_template = self::$_template;
		return parent::render($type = NULL, $tag = NULL);
	}
}
?>