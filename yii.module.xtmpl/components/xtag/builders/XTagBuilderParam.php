<?php
/**
 * Build all element params
 * User: sem
 * Date: 27.03.12
 * Time: 9:02
 */
class XTagBuilderParam extends XTagBuilderBase
{
  /**
   * @var XTagBuilderParamValue|IXTagBuilder
   */
  private $valueBuilder = null;

  /**
   * @param IXTagProcessor $proc
   */
  public function __construct(IXTagProcessor & $proc)
  {
    $this->proc = $proc;
    $this->valueBuilder = $proc->getFactory()->builder('paramValue');
  }

  /**
   * Recursive build element parameters
   * @param  array $currentItem
   * @return mixed
   */
  public function build(array $currentItem)
  {
    if (!isset($currentItem['params'])) {
      return array();
    }

    $targets = $currentItem['params'];
    $params = array();
    foreach ($targets AS $param) {
      $paramName = (!empty($param['name']) ? $param['name'] : NULL);

      if (!is_array($param['val'])) {
        $paramVal =  & $this->getParameterValue($param);
      } else {
        if (isset($param['val']['params'])) {
          $paramVal = & $this->build($param['val']);
        } else {
          $paramVal = & $this->build(array('params' => $param['val']));
        }
      }
      $param['val'] = $paramVal;

      if ($paramName) {
        $params[$paramName] = $paramVal;
      } else {
        $params[] = $paramVal;
      }
    }

    return $params;
  }

  /**
   * Get param value
   * @param mixed $param
   * @param null $default
   * @return mixed|null
   */
  protected function getParameterValue($param, $default = null)
  {
    $this->valueBuilder->setDefaultValue($default);
    return $this->valueBuilder->build($param);
  }
}