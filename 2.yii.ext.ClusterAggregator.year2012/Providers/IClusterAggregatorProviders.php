<?php
/**
 * Interface for providers
 * User: sem
 * Date: 21.06.12
 * Time: 12:26
 */
interface IClusterAggregatorProviders
{
  /**
   * @abstract
   * @return void
   */
  public function setMethodsImplementer( IClusterAggregatorProviderMethodsImplementer $methodsProvider );

  /**
   * @abstract
   * @return IClusterAggregatorProviderMethodsImplementer
   */
  public function getMethodsImplementer();


  /**
   * @abstract
   * @param $commandName
   * @param array $commandParams
   * @return array
   */
  public function runCommand( $commandName, array $commandParams = array() );

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

  public function getWSDLProviders();
}
