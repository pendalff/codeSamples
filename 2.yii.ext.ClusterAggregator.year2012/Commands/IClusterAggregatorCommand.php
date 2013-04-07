<?php
/**
 * Interface of aggregator commands
 * User: sem
 * Date: 08.06.12
 * Time: 14:26
 */
interface IClusterAggregatorCommand
{
  /**
   *
   */
  public function __construct();

  /**
   * @abstract
   * @param ClusterAggregatorContextCommand $context
   * @return array|null
   * @throws CException
   */
  public function process( ClusterAggregatorContextCommand $context );

}
