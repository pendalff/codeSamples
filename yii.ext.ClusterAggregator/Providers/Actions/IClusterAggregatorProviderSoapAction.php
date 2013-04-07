<?php
/**
 * Base for all cluster actions
 * User: sem
 * Date: 07.06.12
 * Time: 18:14
 */
interface IClusterAggregatorProviderSoapAction
{
  public function init();

  //public function run();

  /**
   * @throws CException
   * @return array
   */
  public function getParams();

  /**
   * @throws CException
   * @return array
   */
  public function getParam( $name, $default =null, $structDefined = false );

  /**
   * @return ClusterAggregatorContextCommand
   */
  public function createContext();

}
