<?php
/**
 * Widget`s render
 * User: sem
 * Date: 27.03.12
 * Time: 8:54
 */
class XTagRenderWidgetCall extends XTagRenderWidgetBegin implements IXTagRender
{
  public function run(IXTagElement $target)
  {
    $this->target = $target;

    $class = $this->target->getClass();
    $widget = $this->getWidget($class);

    $params = $target->getParams();
    if (!isset($params['method'])) {
      throw new CException('For call method on begined widget (eq $form->method($param1,$param2) need param "method"');
    }
    $method = $params['method'];
    unset($params['method']);

    if (method_exists($widget, $method)) {
      $reflection = new ReflectionMethod($widget, $method);
      $content = $reflection->invokeArgs($widget, $params);
      $this->replaceElement($this->target->getNode(), $content);
    } else {
      throw new CException('Method ' . $method . ' not found in widget instance');
    }
  }

}
