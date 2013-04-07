<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Cookie-based session class.
 */
class SMVC_Session_Cookie extends Session {

	protected function _read($id = NULL)
	{
		return Cookie::get($this->_name, NULL);
	}

	protected function _regenerate()
	{
		// Cookie sessions have no id
		return NULL;
	}

	protected function _write()
	{
		return Cookie::set($this->_name, $this->__toString(), $this->_lifetime);
	}

	protected function _destroy()
	{
		return Cookie::delete($this->_name);
	}

} // End Session_Cookie
