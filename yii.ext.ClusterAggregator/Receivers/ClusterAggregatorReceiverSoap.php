<?php
/**
 * Soap source
 * @author: sem
 * Date: 05.06.12
 * Time: 0:56
 * @soap
 * @see IClusterAggregatorMethodsImplementer
 */
class ClusterAggregatorReceiverSoap extends ClusterAggregatorReceiverAbstract
{

  /**
   * @var ClusterAggregatorReceiverClientSoap|null
   */
  private $_client = null;

  /**
   *
   */
  public function init()
  {
    Yii::import( $this->getAggregator()->getPathPrefix().'.Receivers.Clients.ClusterAggregatorReceiverClientSoap');
    $params = $this->getParams();
    if( isset( $params['url'])
         && isset($params['login']) && isset($params['password']) && isset($params['provider'])
    ){
        $url = $params['url'];
        unset($params['url']);
        $this->_client = new ClusterAggregatorReceiverClientSoap( $url , $params );

        $this->_client->__setSoapHeaders(array(
          new SoapHeader( 'http://soapinterop.org/echoheader/', 'auth', (object)array(
            'provider' => $params['provider'],
            'login'    => $params['login'],
            'password' => $params['password']
          ), false)
        ));
    }
    else{
      throw new CException('Class '.get_class($this).' need params: url, name of provider, login, password, getted '.var_export($params,1));
    }
  }

  /**
   * Hack for files content
   * @param $name
   * @param $params
   * @return array
   */
  protected function beforeInvoke( $name, $params )
  {

    if( $name == 'addExtraOrderFiles'){
      /** @var $aggregator ClusterAggregator  */
      $aggregator = $this->getAggregator();

      $context = $aggregator->createContext( $params );

      /** @var $command ClusterAggregatorCommandAddExtraOrderFiles */
      $command  = $aggregator->createCommand( $name );

      $params['filesArr'] = array();

      $prepared = $command->prepareRemoteFile( $context );
      if( $prepared['uploaded'] > 0 )
      {
        $params['filesArr'] = $prepared['files'];
      }
    }

    return $params;
  }

  /**
   * @param $name
   * @param array $params
   * @return mixed
   * @throws CException
   */
  public function invoke( $name, array $params = array()){

    $callback = array( $this->getClient(), $name );

    if( !is_callable( $callback )){
      throw new CException('Unknown method call in SOAP client, params is '.var_export( $this->getParams() ));
    }

    $params = $this->beforeInvoke( $name, $params );

    try{

      $result = call_user_func_array( $callback, $params );

    }
    catch( SoapFault $e ){

      if( Yii::app()->clusterAggregator->getDebug() ){
        print $e->getMessage() ."\n";

        print $this->getClient()->__getLastRequest();

        print $this->getClient()->__getLastResponse();
        die;
      }

      throw $e;
    }

    if( !$result['errorCode'] ){
      return is_array($result['result']) && count($result['result']) ? $result['result'] : array();
    }
    else{
      throw new CException('Result of call SOAP method '.$name.' returned with errorCode '.$result['errorCode'].':'.var_export( $result, 1), $result['errorCode'] );
    }
  }

  /**
   * @return ClusterAggregatorReceiverClientSoap|null
   */
  protected function getClient(){
    return $this->_client;
  }

}
