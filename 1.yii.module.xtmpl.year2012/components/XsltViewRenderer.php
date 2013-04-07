<?php
/**
 * Implement IViewRenderer for replace CViewRenderer
 * (base Yii render) for using xslt templates.
 * @see CViewRenderer, IViewRenderer
 * User: sem
 * Date: 21.03.12
 * Time: 20:55
 */

class XsltViewRenderer extends CApplicationComponent implements IViewRenderer
{
  public $usePreloader = true;
  /**
   * Debug mode
   * @var bool
   * @exit
   */
  public $debug = false;

  /**
   * Profile render?
   * @var bool
   */
  public $profile = false;

  /**
   * Path to file for XSLTProcessor logs
   * @var null
   */
  public $profileLogPath = null;

  /**
   * Расширение файла представления.
   * @var string
   */
  public $fileExtension = '.xsl';

  /**
   * Replace protected/xsl to YIIBASEPATH/xsl
   * @var bool
   */
  public $replaceBasePath = '';

  /**
   * List of XSL DOMDocument property
   * @var array
   */
  public $xslDOMParams = array(
    'resolveExternals' => true,
    'substituteEntities' => true
  );

  /**
   * List of XML DOMDocument property
   * @var array
   */
  public $xmlDOMParams = array(
    'resolveExternals' => true,
    'substituteEntities' => true
  );

  /**
   * XSL  DOMDocument container for XSLT transformation
   * @var DOMDocument
   */
  protected $xslDocument = null;

  /**
   * Xml DOMDocument container for XSLT transformation
   * @var DOMDocument
   */
  protected $xmlDocument = null;


  /**
   * XSLT processor
   * @var XSLTProcessor
   */
  protected $xsltProcessor = null;

  /**
   * Processor for replace elements
   * @var null|xtagProcessor
   */
  protected $xtagProccessor = null;

  protected $context = null;

  protected $data = null;

  protected $file = null;

  protected $counter = 0;

  /**
   * Init
   */
  public function init()
  {
    parent::init();

    if ($this->profile && !$this->profileLogPath) {
      $this->profileLogPath = Yii::app()->basePath . DIRECTORY_SEPARATOR . 'runtime/renderer.xslt.log';
    }
    $xtmpl = Yii::app()->findModule('xtmpl');
  }

  /**
   * Render *.xsl file
   * @param $context
   * @param $file
   * @param $data
   * @param $return
   * @throws CException
   */
  public function renderFile($context, $file, $data, $return)
  {

    $this->context = $context;
    $this->data = $data;
    $this->file = $file;

    if (!is_file($file) || ($file = realpath($file)) === false) {
      throw new CException(Yii::t('yii', 'View file "{file}" does not exist.', array('{file}' => $file)));
    }

    if (isset($_GET['debug'])) {
      $this->debug = true;
    }

    // Получаем XSL шаблон
    $xsl = $this->getTemplate($file);

    // Получаем XML-данные
    $helperContext = array_pop(explode('/', $file));
    $xml = $this->getXML($data, $helperContext);

    // Процессим
    $result = $this->_transform($xml, $xsl);

    if ($result === false) {
      throw new CException('Error xslt transformation ' . $file);
    }

    if ($return) {
      return $result;
    } else {
      echo $result;
    }
  }

  /**
   * Public setter for parent::init()
   * @param xtagProcessor $xtagProccessor
   */
  public function setXtagProccessor($xtagProccessor)
  {
    $this->xtagProccessor = $xtagProccessor;
  }

  /**
   * Public getter for parent::init()
   * @return xtagProcessor
   */
  public function getXtagProccessor()
  {
    return $this->xtagProccessor;
  }

  /**
   * A custom error handler especially for XML errors.
   *
   * @link http://au.php.net/manual/en/function.set-error-handler.php
   * @param integer $errno
   * @param integer $errstr
   * @param integer $errfile
   * @param integer $errline
   */
  public function handlerXMLError($errno, $errstr, $errfile, $errline)
  {
    $message = 'XML error - ' . $errno . ', ' . str_replace('DOMDocument::', null, $errstr) . ". \nFile " . $errfile . ". \nLine " . $errline;
    throw new CException($message, $errno);
  }

  /**
   * A custom error handler especially for XSL errors.
   *
   * @link http://au.php.net/manual/en/function.set-error-handler.php
   * @param integer $errno
   * @param integer $errstr
   * @param integer $errfile
   * @param integer $errline
   */
  public function handlerXSLError($errno, $errstr, $errfile, $errline)
  {
    $message = 'XSLT error - ' . $errno . ', ' . str_replace('DOMDocument::', null, $errstr) . ". \nFile " . $errfile . ". \nLine " . $errline;
    throw new CException($message, $errno);
  }

  /**
   * Uses DomDocument to transform the document.
   * Any errors that occur are handled by custom error handlers, handlerXMLError or
   * handlerXSLTError.
   * @param string $xml
   *  The XML for the transformation to be applied to
   * @param string $xsl
   *  The XSL for the transformation
   * @return string
   */
  protected function _transform($xml, $xsl, $domLoadOptions = null)
  {
    $worker = clone $this;
    $worker->initXSLTProcess();

    // Set up error handling
    if (function_exists('ini_set')) {
      $ehOLD = ini_set('html_errors', false);
    }

    // Load the xslt xml document
    set_error_handler(array($worker, 'handlerXSLError'));
    $worker->xslDocument->loadXML($xsl, $domLoadOptions);
    restore_error_handler();

    // Load the xml document
    set_error_handler(array($worker, 'handlerXMLError'));
    $worker->xmlDocument->loadXML($xml, $domLoadOptions);
    restore_error_handler();

    // render xtag elements
    if ($worker->xtagProccessor === null) {
      $worker->initXTagProcessor();
    }

    if ($worker->xtagProccessor) {
      $worker->counter++;
      $worker->xslDocument = $worker->xtagProccessor->process($worker, $worker->data, $worker->xslDocument, $worker->context, $worker->file, $worker->counter);
    }

    if ($worker->debug) {
      //header("Content-type: text/html; charset=UTF-8");
      echo "<html><head><title>Debug xsltViewRender</title></head><body>";
      echo "<h2>Input XML</h2><div>" . $xml . "</div>";
      echo "<h2>XML in DOMDocument</h2><div>" . $worker->xmlDocument->saveXML() . "</div>";
      echo "<h2>Input XSL</h2><div>" . $xsl . "</div>";
      echo "<h2>XSL in DOMDocument</h2><div>" . $worker->xslDocument->saveXML() . "</div>";
      //echo "<h2>View data dump</h2><div><pre>"; print_r( $this->data ); echo "</pre></div>";
      die("<h1>xsltViewRenderer debug enabled</h1></body></html>");
    }

    // Load the xsl template
    set_error_handler(array($worker, 'handlerXSLError'));
    $worker->xsltProcessor->importStyleSheet($worker->xslDocument);
    restore_error_handler();

    // Start the transformation
    set_error_handler(array($worker, 'handlerXMLError'));
    $processed = $worker->xsltProcessor->transformToXML($worker->xmlDocument);

    // Restore error handling
    if (function_exists('ini_set') && isset($ehOLD)) {
      ini_set('html_errors', $ehOLD);
    }
    restore_error_handler();

    return $processed;
  }

  protected function initXTagProcessor()
  {
    if ($this->xtagProccessor === null && $xtags = Yii::app()->getComponent('xtagProcessor')) {
      $this->setXtagProccessor($xtags);
    }
  }

  /**
   * Init processors and etc
   */
  protected function initXSLTProcess()
  {
    $this->xsltProcessor = new XsltProcessor;

    if ($this->profile && $this->profileLogPath) {
      $this->xsltProcessor->setProfiling($this->profileLogPath);
    }

    // Create instances of the DomDocument class
    $this->xmlDocument = new DomDocument('1.0', 'UTF-8');

    foreach ($this->xmlDOMParams AS $prop => $val) {
      $this->xmlDocument->$prop = $val;
    }

    $this->xslDocument = new DomDocument('1.0', 'UTF-8');

    foreach ($this->xslDOMParams AS $prop => $val) {
      $this->xslDocument->$prop = $val;
    }

  }

  /**
   * Get current viewData as string of XML
   * @return string
   */
  protected function getXML($data, $context = null)
  {
    XMLHelper::clearXML();
    $data['lang'] = isset($data['lang']) ? $data['lang'] : Yii::app()->params['lang'];

    // Run preloader functions
    //@todo выпилите это. preloader должен работать на beforeRender event
    if ($this->usePreloader && Yii::app()->xslt && Yii::app()->xslt->preloader) {
      if (!empty(Yii::app()->xslt->preloadFunc) && is_array(Yii::app()->xslt->preloadFunc)) {
        foreach (Yii::app()->xslt->preloadFunc as $keyFunc => $func) {
          if (in_array($keyFunc, array('xajax', 'menu_html'))) {
            continue;
          }
          //var_dump($keyFunc,$func);
          if (!isset($data[$keyFunc])) {
            $data[$keyFunc] = Preloader::$func();
          }

        }
      }
    }

    return XMLHelper::toXml($data, 'page', null, true, $context);
  }

  /**
   * @param $file
   * @return string
   */
  protected function getTemplate($file)
  {
    $templateFile = file_get_contents($file);
    return $this->getLayer($templateFile);
  }

  /**
   * Обвязка xslt шаблона
   * @param $content
   * @return string
   */
  protected function getLayer($content)
  {
    $template = '<?xml version="1.0" encoding="UTF-8"?' . ">\n";
    $template .= '<!DOCTYPE xsl:stylesheet [
    <!ENTITY % HTMLsymbol PUBLIC
    "-//W3C//ENTITIES Symbols for XHTML//EN"
    "protected/xsl/xhtml-symbol.ent">
    <!ENTITY % HTMLspecial PUBLIC
    "-//W3C//ENTITIES Special for XHTML//EN"
    "protected/xsl/xhtml-special.ent">
    <!ENTITY % HTMLlat1 PUBLIC
    "-//W3C//ENTITIES Special for XHTML//EN"
    "protected/xsl/xhtml-lat1.ent">
      %HTMLspecial;
      %HTMLlat1;
      %HTMLsymbol;
    ]>';

    $template .=
        "<xsl:stylesheet version='1.0' " . $this->getNamespaces() . " xmlns:exsl='http://exslt.org/common' extension-element-prefixes='exsl'>\n" .
            '<xsl:output method="html" encoding="utf-8" omit-xml-declaration="yes" indent="no"/>';
    $template .= $content;
    $template .= '</xsl:stylesheet>';
    if ($this->replaceBasePath) {
      $prefix = is_string($this->replaceBasePath) ? $this->replaceBasePath : BASE_PATH . '/backend/y/';
      $template = str_replace('protected/xsl/', $prefix . 'protected/xsl/', $template);
    }
    return $template;
  }

  /**
   * @return string
   */
  protected function getNamespaces()
  {
    $namespaces = array(
      'xmlns:xsl="http://www.w3.org/1999/XSL/Transform"'
    );

    if (Yii::app()->getComponent('xtagProcessor') && method_exists(Yii::app()->getComponent('xtagProcessor'), 'getXMLNamespace')) {
      $namespaces[] = Yii::app()->getComponent('xtagProcessor')->getXMLNamespace();
    }
    return implode(' ', $namespaces);
  }

}