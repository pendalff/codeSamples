<?php
/**
 * XML helper
 * User: sem
 * Date: 21.03.12
 * Time: 21:13
 */
class XMLHelper extends CComponent
{

  public $lowerkeys = false;

  protected $xml = null;

  protected $context = null;

  protected static $instance = array();

  final protected function __construct()
  {
  }

  final protected function __clone()
  {
  }

  public static function getInstance()
  {
    if (!self::$instance) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  /**
   * Конвертация массива в XML объект
   * @static
   * @param array|object $data    - данные
   * @param string $rootNodeName  - корневой элемент xml.
   * @param SimpleXMLElement $xml - используется рекурсивно
   * @param bool $asString
   * @param string $context       - контекст конвертации, опционально (используется как сценарий конвертации модели в xml)
   * @return string|SimpleXMLElement  XML data
   */
  public static function toXml($data, $rootNodeName = 'data', $xml = null, $asString = true, $context = null)
  {
    $that = self::getInstance();
    $that->setContext($context);
    if ($xml === null) {
      $template = "<?xml version='1.0' encoding='utf-8'?>" . "<$rootNodeName />";
      $template = str_replace('protected/xsl/', Yii::app()->getBasePath() . DIRECTORY_SEPARATOR . 'xsl' . DIRECTORY_SEPARATOR, $template);
      $xml = $that->xml = simplexml_load_string($template);
    }


    foreach ((array)$data as $nodeName => $nodeValue) {

      // нельзя применять числовое название полей в XML
      if (is_numeric($nodeName)) {
        $nodeName = $rootNodeName;
      }
      // удаляем не латинские символы
      $nodeName = preg_replace('/[^a-z\-\_\.\:0-9]/i', '', $nodeName);
      $nodeName = preg_replace('/^([\d]+)(.*)/i', 'novalid_\1_\2', $nodeName);
      $nodeName = $that->lowerkeys ? strtolower($nodeName) : $nodeName;


      if (is_array($nodeValue)) {
        $node = Arr::is_assoc($nodeValue) ? $xml->addChild($nodeName) : $xml;

        $that->xml = self::toXml($nodeValue, $nodeName, $node, false, $context);
      } elseif (is_object($nodeValue)) {
        $nodeValue = $that->objectToArray($nodeValue);
        if (is_array($nodeValue)) {
          $node = Arr::is_assoc($nodeValue) ? $xml->addChild($nodeName) : $xml;
          $that->xml = self::toXml($nodeValue, $nodeName, $node, false, $context);
        } else {
          $value = htmlentities($nodeValue, ENT_COMPAT, 'UTF-8');
          $xml->addChild($nodeName, $value);
        }
      } else {
        $value = htmlentities($nodeValue, ENT_COMPAT, 'UTF-8');
        $xml->addChild($nodeName, $value);
      }
    }

    return ($asString) ? $that->xml->asXML() : $that->xml;
  }

  /**
   * Convert object (in view data) to array for xmlable
   * @param $object
   * @return array
   */
  protected function objectToArray($object)
  {
    $context = $this->getContext();
    if ($object instanceof IXMLable || method_exists($object, 'toXML')) {
      return $object->toXML(null, null, null, $context);
    } elseif (!$object instanceof IXMLable && $object instanceof CModel) {
      if ($object->asa(IXMLable::BEHAVIOR_NAME))
        return $object->toXML(null, null, null, $context);
      else
        return $object->getAttributes();
    } else {
      return array();
    }
  }

  /**
   * Очистить текущий xml
   * @static
   * @return void
   */
  public static function clearXML()
  {
    $that = self::getInstance();
    $that->xml = null;
  }

  public function setContext($context)
  {
    $this->context = $context;
  }

  public function getContext()
  {
    return $this->context;
  }

}
