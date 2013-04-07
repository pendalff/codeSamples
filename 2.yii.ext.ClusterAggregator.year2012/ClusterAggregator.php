<?php
/**
 *
 * @author: sem
 * Date: 05.06.12
 * Time: 0:56
 */
class ClusterAggregator extends CApplicationComponent
{

  /**
   * @var string $providerKey
   */
  const AGGREGATOR_KEY = 'aggregator_source';

  /**
   * @var string
   */
  public $currentProject = '';

  public $primaryProviderName ='astro7';

  /**
   * @var string - path alias to this component
   */
  public $pathPrefix = '';

  /**
   * @var bool
   */
  public $debug = false;

  /**
   * @var array - list of receivers config
   */
  private $reciversConfig = array();

  /**
   * @var array - list of providers config
   */
  private $providersConfig = array();

  /**
   * @var IClusterAggregatorReceiver[] $instances
   */
  private $recivers = array();


  /**
   * @var IClusterAggregatorReceiver[] $instances
   */
  private $disabledRecivers = array();

  /**
   * @var null|ClusterAggregatorMethodsProvider
   */
  private $_methodProvider = null;

  /**
   * @var array
   */
  private $_receiverFilters = array();

  /**
   * Init recievers and etc
   * @throws CException
   */
  public function init()
  {
    if(!$this->pathPrefix)
    {
      throw new CException('ClusterAggregator need parameter pathPrefix for correct work!');
    }

    Yii::import( $this->pathPrefix. '.*');
    Yii::import( $this->pathPrefix. '.Exceptions.*');
    Yii::import( $this->pathPrefix. '.Commands.*');
    Yii::import( $this->pathPrefix. '.Receivers.*');
    Yii::import( $this->pathPrefix. '.Receivers.Clients.*');
    Yii::import( $this->pathPrefix. '.Providers.*');
    Yii::import( $this->pathPrefix. '.Service.*');

    if( count($this->getReciversConfig())) {
      foreach( $this->getReciversConfig() AS $key => $receiver )
      {
        if(isset($receiver['class'])){
          $classname = $receiver['class'];
          if( class_exists($classname) ){
            $enabled = $receiver['enabled'];
            unset($receiver['enabled']);
            unset($receiver['class']);
            /**
             * @var IClusterAggregatorReceiver $sourceObject
             */
            $sourceObject = new $classname( $this, $receiver );
            $sourceObject->setName( $key );
            if($enabled == true){
              $this->recivers[ $key ] = $sourceObject;
            }
            else
            {
              $this->disabledRecivers[ $key ] = $sourceObject;
            }
          }
          else{
            throw new CException('Class for receiver  '.$key.' - '.$receiver['class'].' not available - '.$classname );
          }
        }
      }
    }

    $this->_methodProvider = new ClusterAggregatorMethodsProvider( $this );

    parent::init();
  }

  /**
   * Provide methods
   * @param string $method
   * @param array $params
   * @throws ClusterAggregatorException
   * @return mixed|ClusterAggregatorMethodsProvider
   * @link ClusterAggregatorMethodsProvider
   */
  public function __call( $method, $params )
  {
    if( method_exists($this->_methodProvider, $method) ){
      return call_user_func_array( array( $this->_methodProvider, $method), $params );
    }
    else{
      throw new ClusterAggregatorException('Method '.$method.' not available for ClusterAggregator or is not exist in ClusterAggregatorMethodsProvider!', 1000 );
    }
  }

  /**
   * @param array $data
   * @return bool
   */
  public function isLocalDataset( array $data )
  {
    return ( isset( $data[ self::AGGREGATOR_KEY ]) && $data[ self::AGGREGATOR_KEY ] == $this->primaryProviderName ) ? true : false;
  }

  /**
   * Check for empty dataset from provider
   * @param array $data
   * @return bool
   */
  public function emptyDataset( array $data )
  {
    return array_keys($data) === array( self::AGGREGATOR_KEY )  ? true : false;
  }

  /**
   * Check for empty dataset from provider
   * @param array $data
   * @return bool
   */
  public function emptyCollection( ClusterAggregatorDataProvider $data )
  {
    $result = $data->getData();
    return (count($result) == 0) || (count($result) > 0 && $this->emptyDataset( $data->getRow()));
  }


  /**
   * Apply sort by custom key and direction to data provider
   * @param ClusterAggregatorDataProvider $provider
   * @param string $sortKey
   * @param string $sortDirection
   */
  public function applySort( ClusterAggregatorDataProvider $provider, $sortKey = 'ts', $sortDirection = 'DESC')
  {
    $provider->setSort( array(
      'defaultOrder' => $sortKey.' '.$sortDirection
    ));

    $attributes = array();
    foreach($provider->getKeys() AS $attr ){
      $attributes[ $attr ] = array(
        'asc'=> $attr,
	      'desc'=> $attr. ' DESC',
	      'label'=> ucfirst($attr),
	      'default'=> $sortDirection,
      );
    }
    $provider->setSort( array(
      'attributes'   => $attributes
    ));
  }

  /**
   * Get full datacollection
   * @param $method
   * @param $params
   * @param string $aggregateKey
   * @internal param string $aggregateSortDirection
   * @return \ClusterAggregatorDataProvider
   */
  public function getDataCollection( $method , $params, $aggregateKey = 'ts', $receivers_filters = array() )
  {
    $receivers_filters = array_merge( $receivers_filters, $this->getReceiverFilters() );

    foreach( $this->recivers AS $name => $source )
    {
      if( count( $receivers_filters) && !in_array($name, $receivers_filters) ){
        continue;
      }

      try
      {
        $data = $source->invoke($method, $params);

        if (!isset($provider)) {
          $provider = $this->createDataProvider($data, $name, $aggregateKey);
        }
        else   {
          $provider->mergeWith($this->createDataProvider($data, $name, $aggregateKey));
        }
      }
      catch( ClusterAggregatorExceptionAdviserNotFound $e )
      {

      }
      catch( CException $e )
      {
        if ($e->getCode() != 2000)
        {
          throw $e;
        }
      }
      //where CException throwed with code 2000 and filters sources setted to 1 pos
      if (!isset($provider)) {
        $provider = $this->createDataProvider($data, $name, $aggregateKey);
      }
    }
    $this->setReceiverFilters( array() );
    return $provider;
  }

  /**
   * @param array $data
   * @param string $keyForSort
   * @param string $sortDirection
   * @return ClusterAggregatorDataProvider
   */
  public function createDataProvider( array $data, $providerName, $primaryKey = 'ts' )
  {
    $provider = ClusterAggregatorDataProvider::factory( $data, array(
      'keyField' => $primaryKey,
      'providerName' => $providerName
    ));

    return $provider;
  }

  /**
   * @param array $data
   * @return ClusterAggregatorContextCommand
   */
  public function createContext( array $data = array() )
  {
    return ClusterAggregatorContextCommand::factory( $data,  false );
  }

  /**
   * @param $name
   * @return \IClusterAggregatorCommand
   */
  public function createCommand( $name )
  {
    return ClusterAggregatorCommandFactory::factory( $name );
  }

  /**
   * @param IClusterAggregatorProviders $provider
   * @param IClusterAggregatorReceiver $reciever
   * @return ClusterAggregatorMethodsImplementer|IClusterAggregatorProviderMethodsImplementer
   */
  public function createProviderMethods(IClusterAggregatorProviders $provider, IClusterAggregatorReceiver $reciever )
  {
    return ClusterAggregatorMethodsImplementer::factory( $provider, $reciever );
  }

  /**
   * @return boolean
   */
  public function getDebug()
  {
    return $this->debug;
  }

  /**
   * @return void
   */
  public function setRecivers( $param )
  {
    $this->reciversConfig = $param;
  }

  /**
   * @return array
   */
  public function getReciversConfig()
  {
    return $this->reciversConfig;
  }

  /**
   * @return array
   */
  public function setProviders( $param )
  {
    $this->providersConfig = $param;
  }

  /**
   * @return array
   */
  public function getProvidersConfig( $as_object = false )
  {
    return (!$as_object) ? $this->providersConfig : (object) $this->providersConfig;
  }

  /**
   * @return string
   */
  public function getPathPrefix()
  {
    return $this->pathPrefix;
  }

  /**
   * @param array|string $receiverFilters
   */
  public function setReceiverFilters( $receiverFilters )
  {
    if (!$receiverFilters) {
      return;
    }

    if( !is_array( $receiverFilters) ){
      $receiverFilters = array( $receiverFilters );
    }

    $this->_receiverFilters = $receiverFilters;
  }

  /**
   * @return array
   */
  public function getReceiverFilters()
  {
    return $this->_receiverFilters;
  }

  /**
   * @param $name
   * @return IClusterAggregatorReceiver|null
   */
  public function getReciever( $name )
  {
    $recivers = $this->getRecivers();
    return isset($recivers[$name]) ? $recivers[$name] : null;
  }

  public function getRecivers()
  {
    return array_merge($this->recivers,$this->disabledRecivers);
  }

}
