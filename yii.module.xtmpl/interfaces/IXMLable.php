<?php
/**
 * Interface with method convert object to array for getting XML (usage XMLHelper)
 * @author: sem
 * Date: 14.04.12
 * Time: 4:21
 */
interface IXMLable
{
  /**
   * Client code checked has behavior with this key
   */
  const BEHAVIOR_NAME = 'xmlable';

  /**
   * Strategy - get all attributes of object
   */
  const CONVERT_SIMPLE = 0;
  /**
   * Strategy - get attributes of object by list in $params['attributes']
   */
  const CONVERT_BYLIST = 1;
  /**
   * Strategy - get attributes of object by list in $params['attributes']
   */
  const CONVERT_WITH_RELATIONS_ALL = 2;

  /**
   * Strategy - get all attributes of object
   * and relation by list
   * as $params['relations'] = array( 'realtionName' => array('relationAttributesList)
   */
  const CONVERT_WITH_RELATIONS_BYLIST = 3;

  /**
   * Strategy - get attributes of object by list in $params['attributes']
   * and all relations
   */
  const CONVERT_BYLIST_WITH_RELATIONS_ALL = 4;

  /**
   * Strategy - get attributes of object by list in $params['attributes']
   * and relation by list
   * as  - $params['relations'] = array( 'realtionName' => array('relationAttributesList)
   */
  const CONVERT_BYLIST_WITH_RELATIONS_BYLIST = 5;

  /**
   * Object to Array
   * Client code convert this array to XML with XMLHelper::toXML
   * @see XMLHelper::toXML
   * @abstract
   * @param null $strategy
   * @param array|null $params
   * @param null $parent
   * @param null $context
   */
  public function toXML($strategy = null, array $params = null, $parent = null, $context = null);

  /**
   * @abstract
   * return int $strategy with value of IXMLable constant
   * @param null|string $context
   */
  public function getXMLableStrategy($context = null);

  /**
   * @abstract
   * return array $params with value of IXMLable constant
   * @param null $context
   */
  public function getXMLableParams($context = null);
}
