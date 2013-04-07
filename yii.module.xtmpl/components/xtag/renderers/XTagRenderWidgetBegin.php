<?php
/**
 * Widget`s render
 * User: sem
 * Date: 27.03.12
 * Time: 8:54
 */
class XTagRenderWidgetBegin extends XTagRenderBase implements IXTagRender
{
  /**
   * Instanses of widgets
   * @var null|CWidget[] $widget
   */
  private static $_widgets = array();

  public function run(IXTagElement $target)
  {
    parent::run($target);

    $classname = $this->target->getClass();

    if (!isset(XTagRenderWidgetBegin::$_widgets[$classname])) {
      ob_start();
      XTagRenderWidgetBegin::$_widgets[$classname]['item'] = $target;
      XTagRenderWidgetBegin::$_widgets[$classname]['widget'] = $this->proc->getContext()->beginWidget($classname, $this->target->getParams());
    }

  }

  protected function getWidget($classname)
  {
    if (!isset(XTagRenderWidgetBegin::$_widgets[$classname])) {
      throw new CException('Instance widget ' . $classname . ' not found in render stack');
    }

    return XTagRenderWidgetBegin::$_widgets[$classname]['widget'];
  }

  protected function renderStartElement($widget, $htmlOfStartElement)
  {
    $startNode = XTagRenderWidgetBegin::$_widgets[get_class($widget)]['item']->getNode();
    $this->replaceElement($startNode, $htmlOfStartElement);
  }

}
