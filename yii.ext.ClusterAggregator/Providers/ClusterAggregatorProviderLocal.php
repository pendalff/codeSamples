<?php
/**
 * Local provider
 * User: sem
 * Date: 21.06.12
 * Time: 12:32
 * @class ClusterAggregatorProviderLocal
 * @see IClusterAggregatorProviderMethodsImplementer
 * @method
 */
class ClusterAggregatorProviderLocal implements IClusterAggregatorProviders
{

  /**
   * @var IClusterAggregatorProviderMethodsImplementer
   */
  private $_methodsImplementer = NULL;

  /**
   * @var string
   */
  private $_name = '';

  /**
   * Init here
   */
  public function init()
  {

  }


  /**
   * @param $commandName
   * @param array $commandParams
   * @return array|null
   */
  public function runCommand( $commandName, array $commandParams = array())
  {
    $aggregator = Yii::app()->clusterAggregator;

    return  $aggregator->createCommand( $commandName )
              ->process( $aggregator->createContext( $commandParams ) );
  }

  /**
   * @param IClusterAggregatorProviderMethodsImplementer $methodsImplementer
   */
  public function setMethodsImplementer(IClusterAggregatorProviderMethodsImplementer $methodsImplementer)
  {
    $this->_methodsImplementer = $methodsImplementer;
  }

  /**
   * @return IClusterAggregatorMethodsOfProvider|IClusterAggregatorProviderMethodsImplementer|null
   */
  public function getMethodsImplementer()
  {
    return $this->_methodsImplementer;
  }

  /**
   * @param $name
   * @param $arguments
   * @return array|mixed
   */
  public function __call($name, $arguments)
  {
    return call_user_func_array( array($this->getMethodsImplementer(), $name), $arguments);
  }

  /**
   * @param $name
   */
  public function setName($name)
  {
    $this->_name = $name;
  }

  /**
   * @return string
   */
  public function getName()
  {
    return $this->_name;
  }

  public function getWSDLProviders()
  {
    // TODO: Implement getWSDLProviders() method.
  }
}
