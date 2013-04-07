<?php
/**
 * Base for all cluster actions
 * User: sem
 * Date: 07.06.12
 * Time: 18:14
 */
abstract class ActionClusterAbstract extends CAction implements IClusterAggregatorProviderSoapAction
{

  /**
   * @var null|string - postfix classname of command for run
   */
  public $commandName = null;

  /**
   * @var null|IClusterAggregatorCommand
   */
  private $_command  = null;

  private $_isInited = false;

  private $_params;

  /**
   * @var ClusterAggregator
   */
  private $_aggregator = null;

  /**
   * Init action params
   */
  public function init()
  {
    $this->_params = $this->getController()->attributes;
    $this->_aggregator = Yii::app()->getComponent('clusterAggregator');
    $this->setCommand( $this->getAggregator()->createCommand( $this->commandName ) );
    $this->_isInited = true;
  }

  /**
   * @throws CException
   * @return mixed
   */
  public function getParams()
  {
    if( !$this->_isInited ){
      throw new CException('Action with parent ActionClusterAbstract need call parent::init() before get parameters from ProviderSoap!');
    }

    if( !isset($this->_params['is_remote']) ){
      $this->_params['is_remote'] = true;
    }

    return $this->_params;
  }

  /**
   * @param $name
   * @param null $default
   * @param bool $structDefined
   * @return null
   * @throws CException
   */
  public function getParam( $name, $default =null, $structDefined = false )
  {
      $params = $this->getParams();
    if ($structDefined && !isset($params[$name]))
        {
          throw new CException('Parameter ' . $name . ' need defined as method parametr in ProviderSoap class!');
        }

      return isset($params[$name]) ? $params[$name] : $default;
  }

  /**
   * @return ClusterAggregatorContextCommand
   */
  public function createContext()
  {
    return $this->getAggregator()->createContext( $this->getParams() );
  }

  /**
   * @return \ClusterAggregator
   */
  public function getAggregator()
  {
    return $this->_aggregator;
  }

  /**
   * @param \IClusterAggregatorCommand|null $command
   */
  public function setCommand( IClusterAggregatorCommand $command )
  {
    $this->_command = $command;
  }

  /**
   * @return \IClusterAggregatorCommand|null
   */
  public function getCommand()
  {
    return $this->_command;
  }
}
