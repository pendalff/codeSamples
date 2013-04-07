<?php
/**
 * Base of parser
 * User: sem
 * Date: 27.03.12
 * Time: 8:54
 */
class XTagParserBase extends CComponent  implements IXTagParser
{
  /**
   * @var IXTagProcessor $proc
   */
  protected $proc = null;
  /**
   * @var  DOMDocument $doc
   */
  protected $doc = null;

  /**
   * @var CMap - context of parser
   */
  protected $context = null;

  /**
   * @var IXTagContext - context of parser
   */
  protected $mapper = null;

  /**
   * @param IXTagProcessor $proc
   */
  public function __construct( IXTagProcessor & $proc ){
    $this->proc = $proc;
    $this->doc  = $proc->getDoc();
  }

  public function run( IXTagContext $mapper ){
    $this->mapper  = $mapper;
    $this->context = $mapper->getParserContext();

    $this->parse();

    return $mapper;
  }

  /**
   * @param $event
   */
  public function onProcessParse( CEvent $event ){
    $this->raiseEvent('onProcessParse', $event);
  }

  protected function parse()
  {
    return true;
  }

  /**
   * @param DOMElement $node
   * @return mixed|null|string
   */
  protected function getClassname( DOMElement & $node  )
  {
    return $this->getAttributeByMapper( $node, 'classname', $this->getType( $node ) );
  }

  /**
   * @param DOMElement $node
   * @return mixed|null|string
   */
  protected function getType( DOMElement & $node )
  {
    return $this->getAttributeByMapper( $node, 'type', $node->tagName );
  }

  /**
   * @param DOMElement $node
   * @param $contextKey
   * @param null $default
   * @return mixed|null|string
   */
  protected function getAttributeByMapper(  DOMElement & $node, $contextKey, $default = null)
  {
    $attr = $this->context->itemAt($contextKey);
    if( strpos( $attr, '@')===0 ){
      return $node->getAttribute( str_replace('@','', $attr) );
    }
    return $default ? $default : $attr;
  }

  /**
   * Return attrs without link to (attr)->Type and (attr)->Classname
   * @param DOMElement $node
   * @return array
   */
  protected function getAttributes( DOMElement & $node )
  {
      $attList = array();
      /**
       * @var $attr DOMAttr
       */
      foreach( $node->attributes AS $key => $attr ){
        $value  = $attr->value;
        if( !in_array($value, array( $this->getType($node), $this->getClassname( $node ) )))
        {
          $attList[$key] = $this->createParam( $key, $value );
        }
      }

      return $attList;
  }

  /**
   * Create new empty param struct
   * @param null $name
   * @param $value
   * @param null $type
   * @param null|mixed $default
   * @return array
   */
  protected function createParam( $name = null, $value, $type = null, $default = null )
  {
    $currentValue = array();
    $currentValue['type']    = $type;
    $currentValue['name']    = $name;
    $currentValue['val']     = $value;
    if( $default!== null ){
      $currentValue['default'] = $default;
    }
    return $currentValue;
  }

  /**
   * Parse param node for get <param> and <option> values
   * @param DOMElement $node
   * @return array|string
   */
  protected function getParams( DOMElement & $node, $elementTag = 'param', $recurciveTag = 'option' )
  {
    $childsNodes = $this->getChildrenByTagName( $node, $elementTag );
    if( count($childsNodes) )
    {
      $val = array();
      foreach( $childsNodes AS $child ){
        /** @var DOMElement $child  */
        array();
        $optionValue = ( empty($recurciveTag) ?
            $this->getParams( $child )
            :
            $this->getParams( $child, $recurciveTag ) );
        $optionName     = $child->getAttribute('name');
        $optionType     = $child->getAttribute('type');
        $optionDefault  = ($child->hasAttribute('default') ? $child->getAttribute('default') : null);
        $currentValue= $this->createParam( $optionName, $optionValue, $optionType, $optionDefault );
        if( $optionName ){
          $val[ $optionName ]   = $currentValue;
        }else{
          $val[]   = $currentValue;
        }
      }
    }
    else{
      $val = $this->getHtml( $node );
      if( empty($val) || ($node->nodeName == $this->context->itemAt('tagName')  && $elementTag == $this->context->itemAt('paramTagName'))  ){
        $val = array();
      }
    }
    return isset($val) ? $val : null;
  }

  /**
   * Get the html content of an element
   * @param DOMNode $node
   * @param bool $outer remove outter tags
   * @param bool $trim  remove spaces
   * @param string $mode - node save mod
   * @return string html
   */
  protected function getHtml( DOMNode $node, $outer=false, $trim = true, $mode = 'XML' )
  {
    $saveMode = 'save' . $mode;

    if(!method_exists($this->doc, $saveMode)){
      throw new CException('method '.$saveMode.' not allowed');
      return '';
    }

    $html = $this->doc->$saveMode($node);
    if (!$outer){
      $html = substr($html,strpos($html,'>')+1,-(strlen($node->nodeName)+3));
    }

    return ($trim) ? trim($html) : $html;
  }


  /**
   * Get children by name
   * @param DOMElement $node
   * @param string $elementTag
   * @return array of childs DOMElements with tag name
   */
  protected function getChildrenByTagName( DOMElement & $node, $elementTag = 'param' )
  {
    $nodeList = array();
    for ($child = $node->firstChild; $child != null; $child = $child->nextSibling){
      if ( $child->nodeType == 1 && $elementTag == $child->nodeName) {
        $nodeList[] = $child;
      }
    }
    return $nodeList;
  }

  /**
   * Get elems.
   * For NS elems you need correct DTD and configure with url here.
   * Example DTD output see in default/index
   * @param $tag
   * @return DOMNodeList
   */
  protected function getElements( $tagName )
  {
    if( $this->context->itemAt('namespace') ){
      return $this->doc->getElementsByTagNameNS( $this->context->itemAt('namespace'), $tagName );
    }
    return $this->doc->getElementsByTagName( $tagName );
  }

}
