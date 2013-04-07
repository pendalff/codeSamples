<?php
/**
 * Widget`s render
 * User: sem
 * Date: 27.03.12
 * Time: 8:54
 */
class XTagRenderWidgetEnd extends XTagRenderWidgetBegin implements IXTagRender
{

  public function run(IXTagElement $target)
  {
    $this->target = $target;

    $htmlStart = ob_get_clean();

    ob_start();
    $widget = $this->proc->getContext()->endWidget();
    $this->renderStartElement($widget, $htmlStart);

    $htmlEnd = ob_get_clean();
    $this->replaceElement($this->target->getNode(), $htmlEnd);
  }
}
