<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
class Controller_Admin_Base extends Controller_Application{
	protected $messages=array('success','error','validation','notice','info','custom');
	protected $messages_rendered;
	public $template = 'admin';
	public function __construct( Request $req )
	{
		// Pass the request to the template controller
		parent::__construct($req);

	}

    public function before() {
    	parent::before();

  		message::init();
		if(message::has($this->messages)){
			$this->messages_rendered = message::render();
		}  	
    }
    public function after() {
        parent::after();
		if($this->_is_ajax != TRUE){
			$this->template->bind('messages', $this->messages_rendered);
		}
    }
}
?>