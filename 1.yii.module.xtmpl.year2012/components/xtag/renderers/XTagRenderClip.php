<?php
/**
 * Clip render
 * User: sem
 * Date: 27.03.12
 * Time: 8:54
 */
class XTagRenderClip extends XTagRenderBase implements IXTagRender
{
  protected $id = 0;

  public function run( IXTagElement $target )
  {
    parent::run( $target );
    $html   = $this->target->getHtml();
    if( !empty( $html ) )
    {
      $params = $this->target->getParams();
      $id     = isset($params['id']) ? $params['id'] : $this->generateId();
      $widget = $this->proc->getContext()->beginClip( $id, $params );
      echo $html;
      $widget = $this->proc->getContext()->endClip();
      $html = ob_get_clean();

    }
    else
    {
      throw new XTagRenderException('Render clip HTML do not must be empty - current target:<pre>'.var_export($this->target, true).'</pre>');
    }

    $this->replaceElement( $this->target->getNode(), $html);
  }

  /**
   * @return string
   */
  protected function generateId()
  {
    return 'clip'.(++$this->id);
  }
}
