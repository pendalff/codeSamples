<?php
/**
 * Behavior for value modificators
 * @author: sem
 * Date: 28.03.12
 * Time: 22:33
 */
class XTagBuilderParamValueModificationable extends XTagBuilderBehavior implements IXTagValueModificationImplementer
{

  /**
   * Modifer symbol
   * @var string
   */
  protected $modiferSymbol = '||';

  public $modiferParamsAsArray = array(
    'createArray'
  );
  /**
   * Array of modifer value plugins
   * @var array
   */
  public $modifersList = array();

  public function addModifersToList(array $modifers)
  {
    $this->modifersList = array_merge($this->modifersList, $modifers);
  }

  /**
   * Before param value sets
   * @param CEvent $event
   */
  public function beforeBuild(CEvent $event)
  {
    $sender = $event->sender;
    if ($sender instanceof XTagBuilderParamValue) {
      if (isset($event->params['val']) && strpos($event->params['val'], $this->modiferSymbol) !== false) {
        $event->params = $this->parseModificator($event->params);
      }

    }
  }

  /**
   * After param value sets
   * @param CEvent $event
   */
  public function afterBuild(CEvent $event)
  {
    $sender = $event->sender;
    if ($sender instanceof XTagBuilderParamValue) {
      $param = $event->params;
      if (isset($event->params['modificator'])) {
        if (empty($this->modifersList)) {
          new ParamValueModificatorHelper($this);
        }
        $modificators = & $event->params['modificator'];

        do {
          $value = $event->params['val'];
          $current_modificator = array_shift($modificators);
          $methodName = $current_modificator[0];
          foreach ($this->modifersList AS $modiferPlugin) {
            if (method_exists($modiferPlugin, $methodName)) {
              $modiferParams = $current_modificator[1];
              array_unshift($modiferParams, $value);
              $value = call_user_func_array(array($modiferPlugin, $methodName), $modiferParams);
            }
          }
          $event->params['val'] = $value;
        } while (count($modificators) > 0);
        unset($event->params['modificator']);
      }
    }
  }

  /**
   * Parse param value modificators
   * @param $param
   * @return mixed
   */
  protected function parseModificator($param)
  {

    $params = explode('|', $param['val']);

    do {
      $param['val'] = trim(html_entity_decode($params[0]));
      $modificator = array_pop($params);
      $modificator = html_entity_decode($modificator);

      if (strpos($modificator, ' ') !== false || strpos($modificator, "'") !== false || strpos($modificator, '"') !== false) { //модификатор с доп. параметрами
        $modificator_type = substr($modificator, 0, strpos($modificator, ' '));

        $modificator_params = substr($modificator, strpos($modificator, ' '));
        if (!empty($modificator_params) && (strpos($modificator_params, "'") !== false || strpos($modificator_params, '"') !== false)) {
          $modificator_params = $this->parseAttributes($modificator_params);
          if (in_array($modificator_type, $this->modiferParamsAsArray)) {
            $modificator_params = array($modificator_params);
          }
        } else {
          $modificator_params = array();
        }
      } else {
        //модификатор без параметров
        $modificator_type = $modificator;
        $modificator_params = array();
      }


      $current_modifer = array($modificator_type, $modificator_params);
      if (isset($param['modificator']) && is_array($param['modificator'])) {
        array_unshift($param['modificator'], $current_modifer);
      } else {
        $param['modificator'] = array($current_modifer);
      }

    } while (count($params) > 1);

    return $param;
  }

  protected function parseAttributes($string)
  {
    $xmlelement = new SimpleXMLElement("<element $string />");
    $array = json_decode(json_encode($xmlelement->attributes()), TRUE);
    return $array['@attributes'];
  }
}
