<?php
/**
 * Base for aggregator recievers
 * @author: sem
 * Date: 05.06.12
 * Time: 0:56
 */
abstract class ClusterAggregatorReceiverAbstract extends CComponent implements IClusterAggregatorReceiver
{
  /**
   * @var null|ClusterAggregator
   */
  protected $_aggregator = null;

  /**
   * @var array
   */
  protected $_params = array();


  /**
   * @var string
   */
  protected $_name   = '';

  /**
   *
   * @param ClusterAggregator $aggregator
   * @param array $params
   */
  public function __construct( ClusterAggregator $aggregator, array $params = array() )
  {
    $this->_aggregator = $aggregator;
    $this->_params     = $params;
    $this->init();
  }

  /**
   *
   */
  public function init()
  {
  }

  /**
   * @param $name
   * @param array $params
   * @return mixed
   * @throws CException
   */
  public function invoke( $methodName, array $arguments = array() )
  {

  }

  /**
   * @return bool
   */
  public function getDebug()
  {
    return $this->getAggregator()->getDebug();
  }

  /**
   * @return \ClusterAggregator|null
   */
  public function getAggregator()
  {
    return $this->_aggregator;
  }
  /**
   * @return array
   */
  public function isRemote()
  {
    return $this->getParam('is_remote', false);
  }

  /**
   * @return array
   */
  public function getParams()
  {
    return $this->_params;
  }


  /**
   * @return array
   */
  public function getParam( $name, $default = null)
  {
    return isset($this->_params[$name]) ? $this->_params[$name] : $default;
  }


  /**
   * @param string $methodName
   * @param array $methodParams
   * @return \ClusterAggregatorContextCommand
   */
  protected function createContext( $methodName , $methodParams = array() )
  {
    var_dump($methodName);die;
    return $this->getAggregator()->createContext( array_merge(
      $this->mapArguments( $methodName , $methodParams),
      $this->getParams()
    ));
  }

  /**
  * @param string $methodName
  * @param array $methodParams
  * @return \IClusterAggregatorCommand
  */
  protected function createCommand( $commandName )
  {
    return $this->getAggregator()->createCommand( $commandName );
  }


  /**
   * @param string $methodName
   * @param array $methodParams
   * @return array|ClusterAggregatorDataProvider
   */
  protected function runCommand( $commandName, $methodName, $methodParams = array() )
  {
    return  $this->createCommand( $commandName ) ->process( $this->createContext( $methodName, $methodParams ) );
  }

  /**
   * @param $method
   * @param array $parameters
   * @return array
   * @throws CException
   */
  protected function mapArguments( $method, $parameters = array() ){
    $reflection = new ReflectionMethod( $method );
    $args = array();
    foreach( $reflection->getParameters()  AS $parametr ){
      $default = $parametr->isDefaultValueAvailable() ? $parametr->getDefaultValue() : null;
      $val = isset($parameters[$parametr->getPosition()]) ? $parameters[$parametr->getPosition()] : $default;

      if( !$parametr->isDefaultValueAvailable() && !isset($parameters[$parametr->getPosition()])){
        throw new CException('Method '.$method. ' of class '.get_class($this).' need parameter '.$parametr->getName().' at position '.$parametr->getPosition().', and is not have default value');
      }
      $args[ $parametr->getName() ] = $val;
    }
    return $args;
  }

  /**
   * @param string $name
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
}
