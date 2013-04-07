<?php
/**
 * Get Adviser by uid with remote/local parameter
 * User: sem
 * Date: 08.06.12
 * Time: 14:47
 */
abstract class ClusterAggregatorCommandAbstract implements IClusterAggregatorCommand
{

  public function __construct()
  {

  }

  /**
   * Return adviser current session data
   * @param ClusterAggregatorContextCommand $context
   * @return array
   */
  public function process( ClusterAggregatorContextCommand $context ){

    $data = array();
    $context->setData( $data );

    return $data;
  }


}
