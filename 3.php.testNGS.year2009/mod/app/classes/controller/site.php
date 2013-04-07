<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */

class Controller_Site extends Controller_Application{
	protected $_configs = null;
	public function __construct(Request $req)
	{
		parent::__construct( $req );
		$this->template =  'site';
		$this->_configs = SMVC_Config::instance()->load('forum');
	}
}

?>