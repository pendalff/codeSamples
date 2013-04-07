<?php
/**
 * build element params value type context (controller)
 * User: sem
 * Date: 27.03.12
 * Time: 9:02
 */
class XTagBuilderParamValueContext extends XTagBuilderBase implements IXTagBuilder
{
  /**
   * @param IXTagProcessor $proc
   */
  public function __construct(IXTagProcessor & $proc)
  {
    parent::__construct($proc);
  }


  /**
   * Recursive build element parameters
   * @param  mixed $currentItem
   * @return mixed
   */
  public function build($param)
  {
    $default = isset($param['default']) ? $param['default'] : null;
    if (!isset($param['val'])) {
      return $default;
    }
    return $this->find($param['val'], $default);
  }

  public function find($path, $default)
  {
    $context = & $this->proc->getContext();
    return ObjectHelper::path($context, $path, $default, $this->delimiter);
  }
}