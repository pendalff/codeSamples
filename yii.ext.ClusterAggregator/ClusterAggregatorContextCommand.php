<?php
/**
 * Context for commands
 * User: sem
 * Date: 05.06.12
 * Time: 12:56
 */
class ClusterAggregatorContextCommand extends CMap
{

  /**
   * Result of current command working, if it need
   * @var null|ClusterAggregatorDataProvider|mixed
   */
  private $_data = null;


  /**
   * @static
   * @param $dataArray
   * @param bool $readOnly
   * @return \ClusterAggregatorContextCommand
   */
  public static function factory( $dataArray=null, $readOnly=false )
  {
    return new self( $dataArray, $readOnly );
  }

  /**
   * Get parameter from context
   * @param $key
   * @return mixed
   */
  public function get( $key, $structSetted = true )
  {
    if( !$this->contains($key) && $structSetted ){
      throw new CException( 'Parameter '.$key.' not find in object of '.get_class( $this) );
    }

    return $this->itemAt($key);
  }


  /**
   * Set result of current command working, if it need
   * @param $data
   * @return ClusterAggregatorContextCommand
   */
  public function setData( $data )
  {
    $this->_data = $data;
    return $this;
  }

  /**
   * @return ClusterAggregatorDataProvider|mixed
   */
  public function getData()
  {
    return $this->_data;
  }

}
