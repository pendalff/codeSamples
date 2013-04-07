<?php
/**
 * Soap provider
 * User: sem
 * Date: 04.06.12
 * Time: 18:13
 */

Yii::import( Yii::app()->clusterAggregator->getPathPrefix().'.Providers.Actions.IClusterAggregatorProviderSoapAction');
Yii::import( Yii::app()->clusterAggregator->getPathPrefix().'.Providers.Actions.ActionClusterAbstract');


class ClusterAggregatorProviderSoap extends CController implements IWebServiceProvider, IClusterAggregatorProviders
{
  /**
   * WSDL-methods input params
   *
   * @var array
   */
  public $attributes = array();

  /**
   * Authorization flag
   *
   * @var bool
   */
  protected  $auth = false;

  /**
   * Aggregator
   * @var null|ClusterAggregator
   */
  protected $aggregator = null;

  /**
   * @var IClusterAggregatorProviderMethodsImplementer
   */
  protected $_methodsImplementer = NULL;

  /**
   * @var string
   */
  protected $_name = '';

  /**
   * Return list of a actions
   *
   * @return array
   */
  public function actions()
  {
    $actions = array();
    foreach( $this->getWSDLProviders() AS $classForReflection ){
      $actionsNew = $this->getActionsByClass( $classForReflection );
      $actions = CMap::mergeArray( $actions, $actionsNew );
    }

    $actions= array_merge(array(
      'quote' => array(
        'class'     => 'ClusterWebServiceAction',
        'classMap'  =>  array(
           //'SessionModel'=>'SessionModel',
        ),
      )
      ), $actions
    );

    return $actions;
  }

  /**
   * Create this action list with reflection
   * @param $className
   * @return array
   */
  protected function getActionsByClass( $className )
  {
    $prefix = Yii::app()->clusterAggregator->getPathPrefix().'.Providers.Actions';

    $actions = array();
    $methods = new ReflectionClass( $className );
    $methods = $methods->getMethods(ReflectionMethod::IS_PUBLIC);
    foreach( $methods AS $method ){
      $comment=$method->getDocComment();
      if (strpos($comment, '@soap') === false)
      {
        continue;
      }

      //if no @command - use normal action
      if(strpos($comment, '@command') === false){
        $actions[ $method->getName() ] = $prefix.'.ActionCluster'. ucfirst($method->getName());
      }
      else{
        //use ActionClusterWorker for command-related actions run
        $actions[ $method->getName() ] = array(
          'class'=> $prefix.'.ActionClusterWorker',
          'commandName' => ucfirst( $method->getName() ),
        );
      }
    }
    return $actions;
  }

  /**
   * Before call soapserver we set ProviderName from auth header. А иначе БОЛТ, не будет кина...
   *
   * @param CWebService $service
   * @throws ClusterAggregatorException
   * @return bool
   */
  public function beforeWebMethod($service)
  {

    $simple = $GLOBALS['HTTP_RAW_POST_DATA'];
    $p = xml_parser_create();
    xml_parser_set_option($p, XML_OPTION_CASE_FOLDING, false);
    xml_parse_into_struct($p, $simple, $vals, $index);
    xml_parser_free($p);
    $headers = array();
    if(!empty($vals)) {
      $headerFlag = 0;
      foreach($vals as $k=>$v) {
        if($v['tag'] === 'SOAP-ENV:Header' && $v['type'] === 'open') {
          $headerFlag = 1;
        }
        if($headerFlag == 1 && isset($v['value'])) {
          $headers[str_ireplace(array('ns2:', 'ns3:'), array('', ''), $v['tag'])] = $v['value'];
        }
        if($v['tag'] === 'SOAP-ENV:Header' && $v['type'] === 'close') {
          break;
        }
      }

      if( isset( $headers['provider'] ) && ($providerName = $headers['provider']) && !empty($providerName)){
        $this->setName( $headers['provider'] );
      }
      else{
        throw new ClusterAggregatorException('No parameter "provider" on auth header set, unknown here project name for create instance concrete IClusterAggregatorProviderMethodsImplementer');
      }
    }

    return true;
  }

  /**
   * This method is invoked after the requested remote method is invoked
   *
   * @param CWebService $service
   * @return void
   */
  public function afterWebMethod($service) {}

  /**
   * Authorization (simple, we is fast writing)
   * @param string $provider
   * @param string $login
   * @param string $password
   * @throws SoapFault
   * @return bool result
   * @soap
   */
  public function auth( $provider, $login, $password )
  {
    if(!isset(Yii::app()->clusterAggregator->getProvidersConfig(true)->$provider)){
      throw new ClusterAggregatorException('Soap provider with name '.$provider.' not configured!');
    }

    $providerConfig = Yii::app()->clusterAggregator->getProvidersConfig(true)->$provider;

    if( !isset($providerConfig['login']) || !isset($providerConfig['password']) )
    {
      throw new ClusterAggregatorException('Soap provider authorization not configured!');
    }

    if( $login == $providerConfig['login'] && $password == $providerConfig['password']){
      $this->auth = true;
    }

    return $this->auth;
  }


  /**
   * Check authorization and invoke action
   *
   * @param string $name The name of action
   * @return array
   */
  public function runWsdlAction($name) {
    if ($this->auth!==true) {
      return array(
        'errorCode' => 1000
      );
    } else {

      $action = $this->createActionFromMap( $this->actions(), $name, $name );
      //init
      if( method_exists($action, 'init') )
      {
        $action->init();
      }

      //run
      $result = @$action->run();

      if (YII_DEBUG) {
        $logFile = Yii::app()->params['wsdlLogsPath'] .'/wsdl-cluster-'. date('Y-m-d') .'.log';
        $fh = @fopen($logFile, 'a+');
        if ($fh) {
          @fwrite($fh, "\n\n---\n\nDate/time:\n". date('Y-m-d H:i:s') ."\n\nIP:\n". $_SERVER['REMOTE_ADDR'] ."\n\nMethod:\n". $name ."\n\nRequest:\n". $GLOBALS['HTTP_RAW_POST_DATA'] ."\n\nResponse:\n" . print_r($result, 1));
          @fclose($fh);
        }
      }

      return $result;
    }
  }

  /**
   * @param $commandName
   * @param array $commandParams
   * @return array
   */
  public function runCommand( $commandName, array $commandParams = array() )
  {
    $this->attributes = $commandParams;

    return $this->runWsdlAction($commandName);
  }


  /**
   * @param \IClusterAggregatorProviderMethodsImplementer $methodsImplementer
   */
  public function setMethodsImplementer(IClusterAggregatorProviderMethodsImplementer $methodsImplementer)
  {
    $this->_methodsImplementer = $methodsImplementer;
  }

  /**
   * @return \IClusterAggregatorProviderMethodsImplementer
   */
  public function getMethodsImplementer()
  {
    return $this->_methodsImplementer;
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

  public function getWSDLProviders()
  {
    return array( get_class($this), 'IClusterAggregatorProviderMethodsImplementer');
  }

  function __call($name, $arguments)
  {
    $aggregator = Yii::app()->clusterAggregator;

    $methodProvider = $aggregator->createProviderMethods(
      $this,
      $aggregator->getReciever($this->getName())
    );

    return  call_user_func_array( array($methodProvider, $name), $arguments );
  }

}
