<?php
/**
 * Widget`s render
 * User: sem
 * Date: 27.03.12
 * Time: 8:54
 */
class XTagRenderWidget extends XTagRenderBase implements IXTagRender
{

  public function run( IXTagElement $target )
  {
    parent::run( $target );
    $html   = $this->target->getHtml();
    if( empty( $html ) )
    {
      $html =  $this->proc->getContext()->widget( $this->target->getClass(), $this->target->getParams(), true );

    }
    else
    {
      $widget = $this->proc->getContext()->beginWidget( $this->target->getClass(), $this->target->getParams() );
      echo $html;
      ob_start();
      ob_implicit_flush(false);
      $widget = $this->proc->getContext()->endWidget();
      $html = ob_get_clean();
    }

    $this->replaceElement( $this->target->getNode(), $html);
  }

}
