<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Base controller for the application.
 */
abstract class Controller_Application extends Controller_Template {
	
	// Default session instance
	protected $_session;
	

	protected $_auth;	
	
	// Is the request ajax?
	protected $_is_ajax;
	
	// Page for this
	public $page;
	public $template = 'template';		
	/**
	 * Pass the request to the true template controller,
	 * then initialize the template and session.
	 * 
	 * @param	Request	page request
	 * @return 
	 */
	public function __construct( Request $req )
	{
		// Pass the request to the template controller
		parent::__construct($req);

		// Set page object
		$this->page     = Page::instance($req);

		// Set the default session instance, this will be used throughout the application
		@$this->_session = Session::instance();
		
		$this->_auth = Auth::instance();;
		
		$this->_auth->auto_login();
		
		// Is the request ajax?
		if ( Request::$is_ajax ) //NOTE!  $this->request !== Request::instance()
		{
			$this->_is_ajax = TRUE;
		}
	}
	
	/**
	 * Determine whether the request is ajax or not.  If it is, send the
	 * proper headers and turn off aut-rendering.
	 * 
	 * Initialize template variables such as title and keywords
	 * if it is a normal request.
	 * 
	 * @return	void
	 */
	public function before()
	{		
		if ($this->_is_ajax === TRUE)
		{
			// Turn off auto-rendering
    		$this->auto_render = FALSE;
		
			// Send headers for a json response
			$this->request->headers['Cache-Control'] = 'no-cache, must-revalidate';
			$this->request->headers['Expires'] = 'Sun, 30 Jul 1989 19:30:00 GMT';
			$this->request->headers['Content-Type'] = 'application/json';
		}
		else
		{
			// Call template controller before() to initialize template
			parent::before();
			// Initialize template variables
			$this->template->bind('req', 	$this->request  );
			$this->template->bind('page',  	$this->page     );	
			$this->template->bind('body',  	$this->page->content     );
		}
	}
	
	public function after(){	
		if ($this->_is_ajax === TRUE)
		{
			echo $this->request->response;
		}
		else
		{   	
			// Call initialize template
			parent::after();
			$this->request->response = $this->template;
		}

	}
} // End Application Controller
