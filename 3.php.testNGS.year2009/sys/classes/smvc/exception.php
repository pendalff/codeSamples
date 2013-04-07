<?php defined('SYSPATH') or die('No direct access');
/**
 * Base exception class. Translates exceptions .
 */
class SMVC_Exception extends Exception {

	/**
	 * Creates a new translated exception.
	 *
	 *     throw new SMVC_Exception('Something went terrible wrong, :user',
	 *         array(':user' => $user));
	 *
	 * @param   string     error message
	 * @param   array      translation variables
	 * @param   integer    the exception code
	 * @return  void
	 */
	public function __construct($message, array $variables = NULL, $code = 0)
	{
		// Set the message
		$message = __($message, $variables);

		// Pass the message to the parent
		parent::__construct($message, $code);
	}

	/**
	 * Magic object-to-string method.
	 *
	 *     echo $exception;
	 *
	 * @uses    SMVC::exception_text
	 * @return  string
	 */
	public function __toString()
	{
		return SMVC::exception_text($this);
	}

} // End SMVC_Exception
