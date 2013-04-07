<?php
/**
 * Provide toXML method as model behavior
 * (way for usage objects in view data for xslt render)
 * @author: sem
 * Date: 14.04.12
 * Time: 6:05
 */
class XMLableModelBehavior extends CModelBehavior implements IXMLable
{

  /**
   * Strategy of convert
   * @var int|null
   */
  protected $strategy = null;

  /**
   * Params of convert
   * @var array|null
   */
  protected $params = null;

  /**
   * Current item
   * @var CModel|CActiveRecord|null $current
   */
  protected $current = null;

  /**
   * owner  item
   * @var CModel|CActiveRecord|null $current
   */
  protected $parent = null;


  protected $context = null;

  /**
   * @var array
   */
  protected $data = array();

  public function getXMLableStrategy($context = null)
  {
    if (method_exists($this->current, 'getXMLableStrategy')) {
      $strategy = $this->current->getXMLableStrategy($context);
    }
    return (isset($strategy)) ? $strategy : IXMLable::CONVERT_WITH_RELATIONS_ALL;
  }

  public function getXMLableParams($context = null)
  {
    if (method_exists($this->current, 'getXMLableParams')) {
      $params = $this->current->getXMLableParams($context);
    }

    return (isset($params)) ? $params : array();
  }

  /**
   * @param null $strategy
   * @param array|null $params
   * @param null $parent
   * @return array
   * @throws CException
   */
  public function toXML($strategy = null, array $params = null, $parent = null, $context = null)
  {
    $this->current = $this->getOwner();
    $this->parent = $parent;
    $this->strategy = $strategy;
    $this->params = $params;
    $this->context = $context;
    //override with object method and current context
    $this->strategy = $this->getXMLableStrategy($context);
    $this->params = $this->getXMLableParams($context);


    if (!($this->current instanceof CModel)) {
      throw new CException('You need use XMLableModelBehavior for CModel|CActiveRecord classes, current class is ' . get_class($this->current));
    }

    $need_relations = in_array($strategy, array(
      IXMLable::CONVERT_BYLIST_WITH_RELATIONS_ALL,
      IXMLable::CONVERT_BYLIST_WITH_RELATIONS_BYLIST,
      IXMLable::CONVERT_WITH_RELATIONS_ALL,
      IXMLable::CONVERT_WITH_RELATIONS_BYLIST));

    if ($need_relations &&
        (!($this->current instanceof CActiveRecord)
            ||
            !method_exists($this->current, 'relations'))
    ) {
      throw new CException('You need use XMLableModelBehavior with strategy needly "relations" method, current class is ' . get_class($this->current));
    }

    return $this->convert();
  }

  protected function convert()
  {
    $this->data = $this->convertCurrentAttributes();

    switch ($this->strategy) {
      case  IXMLable::CONVERT_BYLIST_WITH_RELATIONS_ALL:
      case  IXMLable::CONVERT_WITH_RELATIONS_ALL:
        $this->convertCurrentRelations(false);
        break;

      case  IXMLable::CONVERT_BYLIST_WITH_RELATIONS_BYLIST:
      case  IXMLable::CONVERT_WITH_RELATIONS_BYLIST:
        $this->convertCurrentRelations(true);
        break;

      default:
        //$this->convertRelations( false );
        break;
    }
    //echo "<pre>"; var_dump($this->data); echo "</pre>"; die;

    return $this->data;
  }

  /**
   * return array of model fields
   * @return array
   * @throws CException
   */
  protected function convertCurrentAttributes()
  {
    switch ($this->strategy) {
      case  IXMLable::CONVERT_BYLIST:
      case  IXMLable::CONVERT_BYLIST_WITH_RELATIONS_ALL:
      case  IXMLable::CONVERT_BYLIST_WITH_RELATIONS_BYLIST:
        if (!isset($this->params['attributes']) || !is_array($this->params['attributes'])) {
          throw new CException('Strategy of convert current object need parameter "attributes" with array of needly fieldnames');
        }

        return $this->current->getAttributes($this->params['attributes']);
        break;

      case  IXMLable::CONVERT_SIMPLE:
      case  IXMLable::CONVERT_WITH_RELATIONS_ALL:
      case  IXMLable::CONVERT_WITH_RELATIONS_BYLIST:
      default:

        return $this->current->getAttributes();
        break;
    }
  }

  /**
   * Convert relations
   * @param bool $by_list
   * @throws CException
   */
  protected function convertCurrentRelations($by_list = false)
  {

    if ($by_list && (!isset($this->params['relations']) || !is_array($this->params['relations']))) {
      throw new CException('Strategy convert current object to XMLArray need parameter "relations" with array relations for convert, see IXMLable::toXML()');
    }

    foreach ($this->current->relations() AS $name => $define) {
      if ($by_list && !in_array($name, array_keys($this->params['relations']))) {
        continue;
      }

      $relation = $this->current->$name;
      if ($relation) {
        $this->convertRelation($name, $relation);
      }
    }

    if (isset($this->params['relations']) && is_array($this->params['relations'])) {
      $dynamicRelations = array_diff(array_keys($this->params['relations']), array_keys($this->current->relations()));

      foreach ($dynamicRelations AS $name) {
        $attributes = $this->params['relations'][$name];
        $method = 'get' . $name;
        if ($attributes && method_exists($this->current, $method) && (!Arr::is_array($attributes) || !Arr::is_assoc($attributes))) {
          $params = Arr::is_array($attributes) ? $attributes : array($attributes);
          $relation = call_user_func_array(array($this->current, $method), $params);
        } else {
          $relation = $this->current->$name;
        }

        if ($relation !== NULL) {
          $this->convertRelation($name, $relation);
        }
      }
    }

  }

  /**
   * Convert single relation object
   * @param $relation
   */
  protected function convertRelation($name, $relation)
  {
    //collection
    if (is_array($relation)) {
      foreach ($relation AS $item) {
        $this->data[$name][] = $this->getRelationAsArray($name, $item);
      }
    } //single
    else {
      $this->data[$name] = $this->getRelationAsArray($name, $relation);
    }
  }

  protected function getRelationAsArray($name, $object)
  {

    if (is_array($object)) {
      if (!empty($this->params['relations'][$name])) {

        $filtered = array();
        $attributes = $this->params['relations'][$name];
        if (is_array($attributes) && Arr::is_assoc($object)) {
          foreach ($attributes AS $key) {
            if (in_array($key, array_keys($object))) {
              $filtered[$key] = $object[$key];
            }
          }
        }

        return $filtered;
      }
      return $object;
    }

    if (!is_object($object)) {
      return $object;
    }

    $attrList = (isset($this->params['relations']) &&
        isset($this->params['relations'][$name]) &&
        !empty($this->params['relations'][$name]))
        ?
        $this->params['relations'][$name]
        :
        null;

    if ($object instanceof IXMLable || method_exists($object, 'toXML')) {
      return $object->toXML($this->strategy, $this->params, $this->current, $this->context);
    } elseif (!$object instanceof IXMLable && $object instanceof CModel) {

      if (!$object->asa(IXMLable::BEHAVIOR_NAME)) {
        return $object->getAttributes($attrList);
      } else {
        return $object->toXML($this->strategy, $this->params, $this->current, $this->context);
      }
    } else {
      return array();
    }
  }

}
