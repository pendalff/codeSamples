<?php
/**
 * Simple ViewContext object
 * User: sem
 * Date: 21.03.12
 * Time: 23:26
 */
class ViewContext
{
  protected $_context = null;

  protected $_data = array();

  /**
   * Sets the initial view local data.
   * @param   array   array of values
   * @return  void
   */
  public function __construct(CBaseController $context, array $data = NULL)
  {
    $this->_context = $context;

    if ($data !== NULL) {
      // Add the values to the current data
      $this->_data = $data + $this->_data;
    }
  }

  /**
   * Assigns a variable by name. Assigned values will be available as a
   * variable within the view file:
   *
   *     // This value can be accessed as $foo within the view
   *     $view->set('foo', 'my value');
   *
   * You can also use an array to set several values at once:
   *
   *     // Create the values $food and $beverage in the view
   *     $view->set(array('food' => 'bread', 'beverage' => 'water'));
   *
   * @param   string   variable name or an array of variables
   * @param   mixed    value
   * @return  View
   */
  public function set($key, $value = NULL)
  {
    if (is_array($key)) {
      foreach ($key as $name => $value) {
        $this->_data[$name] = $value;
      }
    } else {

      Arr::set_path($this->_data, $key, $value);
      //$this->_data[$key] = $value;
    }

    return $this;
  }

  /**
   * Assigns a value by reference. The benefit of binding is that values can
   * be altered without re-setting them. It is also possible to bind variables
   * before they have values. Assigned values will be available as a
   * variable within the view file:
   *
   *     // This reference can be accessed as $ref within the view
   *     $view->bind('ref', $bar);
   *
   * @param   string   variable name
   * @param   mixed    referenced variable
   * @return  View
   */
  public function bind($key, & $value)
  {
    Arr::bind_path($this->_data, $key, $value);
    //$this->_data[$key] =& $value;
    return $this;
  }

  /**
   * Get all view data
   * @return array|null
   */
  public function getData()
  {
    //$this->_data = $this->_data + get_object_vars( $this->_context );
    /** Hell shit spikes */
    if (Yii::app()->getComponent('xslt')) {
      $xslt = Yii::app()->getComponent('xslt');
      if ($xslt->preloader) {
        if (!empty($xslt->preloadFunc) && is_array($xslt->preloadFunc)) {
          foreach ($xslt->preloadFunc as $keyFunc => $func) {
            if (!isset($this->_data[$keyFunc])) {
              $this->_data[$keyFunc] = Preloader::$func();
            }
          }
        }
      }
    }

    return $this->_data;
  }

  /**
   * Magic method, searches for the given variable and returns its value.
   * Local variables will be returned before global variables.
   *
   * @param   string  variable name
   * @return  mixed
   */
  public function & __get($key)
  {
    if (isset($this->_data[$key])) {
      return $this->_data[$key];
    } else {
      throw new CException('View variable is not set: :var',
        array(':var' => $key));
    }
  }

  /**
   * Magic method, calls set() with the same parameters.
   *
   * @param   string  variable name
   * @param   mixed   value
   * @return  void
   */
  public function __set($key, $value)
  {
    $this->set($key, $value);
  }

  /**
   * Magic method, determines if a variable is set and is not NULL.
   *
   * @param   string  variable name
   * @return  boolean
   */
  public function __isset($key)
  {
    return (isset($this->_data[$key]));
  }

  /**
   * Magic method, unsets a given variable.
   *
   * @param   string  variable name
   * @return  void
   */
  public function __unset($key)
  {
    unset($this->_data[$key]);
  }
}
