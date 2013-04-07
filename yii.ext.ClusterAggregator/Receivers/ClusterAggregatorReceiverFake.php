<?php
/**
 * Is recevers need for remote sources create correct reciever with params
 * @author: sem
 * Date: 05.06.12
 * Time: 0:56
 */
class ClusterAggregatorReceiverFake extends ClusterAggregatorReceiverAbstract
{
  /**
   * @param $name
   * @param array $params
   * @return mixed
   * @throws CException
   */
  public function invoke( $name, array $params = array())
  {
    return array();
  }

}

