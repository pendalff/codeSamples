<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Abstract controller class. Controllers should only be created using a [Request].
 *     $controller = new Controller_Foo($request);
 *     $controller->before();
 *     $controller->action_bar();
 *     $controller->after();
 *
 * The controller action should add the output it creates to
 * `$this->request->response`, typically in the form of a [View], during the
 * "action" part of execution.
 */
abstract class SMVC_Controller {

	/**
	 * @var  object  Request that created the controller
	 */
	public $request;

	/**
	 * Creates a new controller instance. Each controller must be constructed
	 * with the request object that created it.
	 *
	 * @param   object  Request that created the controller
	 * @return  void
	 */
	public function __construct(SMVC_Request $request)
	{
		// Assign the request to the controller
		$this->request = $request;
	}

	/**
	 * Automatically executed before the controller action. Can be used to set
	 * class properties, do authorization checks, and execute other custom code.
	 *
	 * @return  void
	 */
	public function before()
	{
		// Nothing by default
	}

	/**
	 * Automatically executed after the controller action. Can be used to apply
	 * transformation to the request response, add extra output, and execute
	 * other custom code.
	 *
	 * @return  void
	 */
	public function after()
	{
		// Nothing by default
	}

} // End Controller
