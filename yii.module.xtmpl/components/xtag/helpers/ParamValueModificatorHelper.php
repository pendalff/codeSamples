<?php
/**
 * Helper class for
 * @author: sem
 * Date: 28.03.12
 * Time: 21:44
 */
class ParamValueModificatorHelper extends CComponent implements IXTagValueModificator
{

  /**
   * List of classes/objects modification value plugins
   * @var array
   */
  protected $modificators = array(
    //'classname'=> null
  );

  public function __construct( XTagBuilderBehavior $modiferApplyObject )
  {
    if( $modiferApplyObject instanceof IXTagValueModificationImplementer )
    {
      if( count($this->modificators)){
        foreach( $this->modificators AS $class => $modifer ){
           if(!is_object( $modifer )){
             $this->modificators[$class] = new $class( $modiferApplyObject );
           }
        }
      }
      $this->modificators[] = $this;
      $modiferApplyObject->addModifersToList( $this->modificators );
    }
  }

  public function createArray( $inputArray, $params = array())
  {

    if( is_array($inputArray) || $inputArray instanceof Traversable )
    {
      $key   = isset($params['key'])   ? $params['key']   : 'key';
      $value = isset($params['value']) ? $params['value'] : 'value';
      $count = isset($params['count']) ? $params['count'] : null;

      $data  = array();
      $i     = 1;
      foreach( $inputArray AS $k => $val ){

        $dataKey = $count ? $count.$i : $k;

        $data[ $dataKey ]  = array(
                              $key   => $k,
                              $value => $val
                            );
        $i++;
      }
      return $data;
    }
  }

  public function uppercase( $value )
  {
    return  mb_strtoupper( (string) $value );
  }

  public function ucfirst( $value )
  {
    return  ucfirst( (string) $value );
  }

}
