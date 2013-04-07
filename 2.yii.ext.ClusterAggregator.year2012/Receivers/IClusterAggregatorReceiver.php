<?php
/**
 *
 * @author: sem
 * Date: 05.06.12
 * Time: 1:26
 */
interface IClusterAggregatorReceiver
{
  /**
   * @abstract
   * @param ClusterAggregator $aggregator
   * @param array $params
   * @internal param array $dsn
   */
  public function __construct( ClusterAggregator $aggregator, array $params = array() );

  /**
   * @abstract
   * @return void
   */
  public function init();

  /**
   * Invoke get data from (remote) source
   * @abstract
   * @param $methodName
   * @param array $arguments
   * @return mixed
   */
  public function invoke( $methodName, array $arguments = array() );

  /**
   * @abstract
   * @return bool
   */
  public function isRemote();

  /**
   * @abstract
   * @return array
   */
  public function getParams();

  /**
   * @abstract
   * @return string
   */
  public function getName();

  /**
   * @abstract
   * @return void
   */
  public function setName( $name );
}
