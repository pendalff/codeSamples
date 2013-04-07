<?php
/**
 * Form render
 * User: sem
 * Date: 27.03.12
 * Time: 8:54
 */
class XTagRenderForm extends XTagRenderBase implements IXTagRender
{
  /**
   * @param IXTagElement|XTagElement $target
   * @return mixed
   */
  public function run(IXTagElement $target)
  {
    parent::run($target);
    if (in_array($this->target->getClass(), get_class_methods('CHtml'))) {
      $content = call_user_func_array(array('CHtml', $this->target->getClass()), $this->target->getParams());
      $this->replaceElement($target->getNode(), $content);
    }
  }

}
