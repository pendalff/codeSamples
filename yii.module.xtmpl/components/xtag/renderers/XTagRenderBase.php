<?php
/**
 * Base of renders
 * User: sem
 * Date: 27.03.12
 * Time: 8:54
 */
class XTagRenderBase extends CComponent implements IXTagRender
{
  /**
   * Replaces for current document
   * @var array of pair id=>html
   */
  private static $placeholders = array();

  /**
   * @var DOMDocument
   */
  protected $doc = null;

  /**
   * @var IXTagElement|XTagElement
   */
  protected $target = null;

  /**
   * @var IXTagProcessor $proc
   */
  protected $proc = null;

  /**
   * @param IXTagProcessor $proc
   */
  public function __construct(IXTagProcessor & $proc)
  {
    $this->proc = $proc;
    $this->doc = $this->proc->getDoc();
  }

  public function run(IXTagElement $target)
  {
    $this->target = $target;
  }


  /**
   * After all widget (& etc) render - replace placeholders,
   * that can not be replaced by means DOMDocument
   * @static
   * @param string $placeholderStart
   * @param string $placeholderEnd
   */
  public function afterRenders($placeholderStart = '<\!--\[', $placeholderEnd = '\]-->')
  {

    if ($this->hasPlaceholders()) {
      $XML = $this->doc->saveXML();
      foreach ($this->getPlaceholders() as $holderId => $holderXML) {
        $XML = preg_replace('/' . $placeholderStart . $holderId . $placeholderEnd . '/iU', $holderXML, $XML);
      }
      return $this->replaceCurrentDocument($XML);
    }
    return false;
  }

  /**
   * @param $name
   * @param $content
   */
  public function addPlaceholder($name, $content)
  {
    if (!isset(XTagRenderBase::$placeholders[$name]))
      XTagRenderBase::$placeholders[$name] = $content;
  }

  /**
   * @return array
   */
  public function getPlaceholders()
  {
    return XTagRenderBase::$placeholders;
  }

  /**
   * @return int
   */
  public function hasPlaceholders()
  {
    return (bool)count(XTagRenderBase::$placeholders) > 0;
  }

  /**
   * @return DOMDocument
   */
  public function getDocument()
  {
    return $this->doc;
  }

  /**
   * Replace element with $rawXML html string.
   * If $rawXML html string is empty - element will be removed from document
   * @param DOMElement $node
   * @param string $rawXML
   * @return bool
   */
  protected function replaceElement(DOMElement $node, $rawXml = '')
  {

    if (trim((string)$rawXml)) {
      $fragment = $this->createFragment($rawXml);
      $node->parentNode->replaceChild($fragment, $node);
    } else {
      $node->parentNode->removeChild($node);
    }
    return true;
  }

  /**
   * Replace current document with new html\xml data
   * (потому что фрагмент или нода не могут создать огрызок тега, бладжвашу, и как итог приходится
   * пересоздавать документ)
   * @param $rawXml
   */
  protected function replaceCurrentDocument($rawXml)
  {
    @$loaded = $this->doc->loadXML($rawXml, LIBXML_NOERROR OR LIBXML_NOWARNING);
    //if($loaded)  $this->doc = $doc;
    if (!$loaded) {
      throw new CException('Unable load new content with replaced widgets, current raw XML is ' . $rawXml);
      /* $doc = new DOMDocument('1.0'); $al = $doc->loadHTML( $rawXml );*/
    }
    self::$placeholders = array();
    return $loaded;
  }

  /**
   * Create nodes from html string for replacing in document.
   * (upd: т.к. не найдено простого способа загрузить в фрагмент
   * невалидный xml (отдельный открывающий или закрывающий тег, например)
   * пользуемся плейсхолдерами
   * @param  string $rawXml - html code for import
   * @return DOMDocumentFragment|DOMElement
   */
  protected function createFragment($rawXml)
  {
    //$created  = false;
    //@$fragment = $this->doc->createDocumentFragment();
    //@$created  = @$fragment->appendXML( (string) $rawXml );
    //if( !$created ){
    $holderId = md5($rawXml);
    $fragment = $this->doc->createComment('[' . $holderId . ']');
    $this->addPlaceholder($holderId, $rawXml);
    //}

    return $fragment;
  }

}
