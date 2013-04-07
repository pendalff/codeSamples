<?php
/**
 * Object for single template-active element.
 * User: sem
 * Date: 27.03.12
 * Time: 10:09
 */
class XTagElement extends CComponent implements IXTagElement
{
  /**
   * Current DOMNode
   * @var DOMNode $node
   */
  protected $node   = null;

  /**
   * Class of element
   * @var null
   */
  protected $class  = null;
  /**
   * Params
   * @var null
   */
  protected $params = null;

  /**
   * Inner html of element
   * @var null
   */
  protected $html   = null;

  protected $builder= null;
  protected $render = null;

  /**
   * Constructor.
   * @param DOMNode $node
   * @param array $properties
   */
  public function __construct( DOMNode & $node, array $properties )
  {
    foreach($properties as $name=>$value){
      $this->$name=$value;
    }
    $this->node = $node;
  }
  /**
   * @return null|string
   */
  public function getClass()
  {
    return $this->class;
  }

  /**
   * @return string|null  current html content
   */
  public function getHtml()
  {
    return $this->html;
  }

  /**
   * @return \DOMNode element node in DOMDocument
   */
  public function getNode()
  {
    return $this->node;
  }

  /**
   * @return null|array element params
   */
  public function getParams()
  {
    if( $this->builder instanceof IXTagBuilder && method_exists($this->builder, 'buildParams')){
      return $this->builder->buildParams( array('params'=>$this->params));
    }
    return $this->params;
  }

  /**
   * @return IXTagRender|XTagRenderBase
   */
  public function getRender(){
     return $this->render;
  }

  /**
   * @param IXTagRender $render
   */
  public function setRender( IXTagRender & $render ){
     $this->render = $render;
  }

}
