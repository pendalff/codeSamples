<?php
/**
 * Cache output render
 * User: sem
 * Date: 27.03.12
 * Time: 8:54
 */
class XTagRenderCache extends XTagRenderBase implements IXTagRender
{
  protected $id = 0;

  public function run( IXTagElement $target )
  {
    parent::run( $target );
    $html   = $this->target->getHtml();

    if( !empty( $html ) )
    {
      ob_start();
      ob_implicit_flush(false);
      $params = $this->target->getParams();
      $id     = isset($params['id']) ? $params['id'] : $this->generateId();
      if($this->proc->getContext()->beginCache( $id, $params )){
        echo $html;
        $widget = $this->proc->getContext()->endCache();
      }
      $html = ob_get_clean();

    }
    else
    {
      throw new XTagRenderException('Render Cache - element HTML param do not must be empty - current target:<pre>'.var_export($this->target, true).'</pre>');
    }

    $this->replaceElement( $this->target->getNode(), $html);
  }

  /**
   * @return string
   */
  protected function generateId()
  {
    return 'cached'.(++$this->id);
  }
}
