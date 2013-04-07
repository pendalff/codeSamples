<?php
/**
 * Created by JetBrains PhpStorm.
 * User: sem
 * Date: 27.03.12
 * Time: 8:54
 */
class XTagProcessor extends CApplicationComponent implements IXTagProcessor
{

  protected static $workers = array();

  /**
   * Template tags namespace
   * @var string
   */
  public $namespace    = 'x';

  /**
   * DTD for template tags namespace
   * Example of DTD see in url /xtmpl/xbase/dtd
   * @var null|string
   */
  public $urlDTD       = null;

  /**
   * Template tags for active elements, as tagname=>tag type,
   * @var string
   */
  public $elements  = null;

  /**
   * @var DOMDocument
   */
  protected $doc    = null;
  /**
   * @var array
   */
  protected $data   = null;
  /**
   * @var CBaseController
   */
  protected $context= null;

  /**
   * @var null
   */
  protected $viewRender  = null;

  /**
   * @var null
   */
  protected $file  = null;

  /**
   * @var null
   */
  protected $counter  = null;

  /**
   * Initializes xtag component.
   */
  public function init()
  {
    parent::init();
    if( !empty( $this->namespace ) && empty( $this->urlDTD ) ){
      $this->urlDTD = Yii::app()->urlManager->createUrl('/xtmpl/xbase/dtd', array());
    }
  }

  /**
   * @return IViewRenderer
   */
  public function getViewRender()
  {
    return $this->viewRender;
  }

  /**
   * @return \DOMDocument
   */
  public function getDoc()
  {
    return $this->doc;
  }

  /**
   * @return array
   */
  public function getData()
  {
    return $this->data;
  }

  /**
   * @return \CBaseController
   */
  public function getContext()
  {
    return $this->context;
  }

  /**
   * @return null|string
   */
  public function getXMLNamespace()
  {
    if( !empty( $this->namespace) && !empty( $this->urlDTD ) ){
      $ns = 'xmlns:'.$this->namespace.'="'.$this->urlDTD.'"';
    }
    return isset($ns) ? $ns : null;
  }

  /**
   * @return XTagFactory
   */
  public function getFactory($file = null){
    return XTagFactory::getInstance( $this, $file );
  }

  /**
   * Main process replace custom xml tags.
   * @param IViewRenderer   $viewRender
   * @param array           $viewData
   * @param DOMDocument     $doc
   * @param CBaseController $context
   * @param null|string $file
   * @param null|int $counter
   * @return DOMDocument
   */
  public function process( IViewRenderer  $viewRender, array $viewData, DOMDocument $doc, CBaseController  $context, $file = null, $counter = null  )
  {
    if( $viewRender->profile ){
      $start = microtime();
    }

    if( !isset( self::$workers[ $file ] ) )
    {
      self::$workers[ $file ] = array();
    }

    if( !isset( self::$workers[ $file ][ $counter ] ) )
    {
      self::$workers[ $file ][ $counter ] = clone $this;
    }

    /**
     * @var $that XTagProcessor
     */
    $that = self::$workers[ $file ][ $counter ];

    $that->doc        = $doc;
    $that->data       = $viewData;
    $that->context    = $context;
    $that->viewRender = $viewRender;
    $that->file       = $file;
    $that->counter    = $counter;

    $factory = $that->getFactory($file);

    foreach( $that->elements AS $element => $mapAttrs ){
      //create current contexts
      $list    = new XTagElementList();
      $listcontext = new XTagContext( $mapAttrs, $list );

      //get instance of builder
      $builder    = $factory->builder( $listcontext->getBuilder() );
      //get instance of parser
      $parser     = $factory->parser( $listcontext->getParser() );
      //set notifer for builder
      $parser->onProcessParse = array( $builder, 'onCurrent' );
      //run parser
      $parser->run( $listcontext );

      //render current items list
      $list->renderItems();
    }

    $baseRender = $factory->render('base');
    if( $baseRender->afterRenders() ){
      return $baseRender->getDocument();
    }

    if( $that->getViewRender()->profile ){
      $end = microtime();
      $message = var_export( array(
        'start' => $start,
        'end'   => $end,
        'total' => $end - $start,
        'item'  => 'xtagprocessor replace widgets and etc',
        'count' => $that->counter,
        'file'  => $that->file
      ), true);
      Yii::log( $message, CLogger::LEVEL_ERROR);
    }

    return $that->doc;
  }


}
