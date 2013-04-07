<?php
/**
 * Local data source
 * @author: sem
 * Date: 05.06.12
 * Time: 0:56
 * @soap
 * @method getAdviserSessionNotes
 */
class ClusterAggregatorReceiverLocal extends ClusterAggregatorReceiverAbstract
{
  /**
   * @var null|ClusterAggregatorProviderLocal
   */
  private $_provider = null;
  /**
   * Init provider
   */
  public function init()
  {
    $this->_provider = new ClusterAggregatorProviderLocal();
    $this->_provider->init();
    $methodsImplementer = $this->getAggregator()->createProviderMethods( $this->_provider, $this );
    $this->_provider->setMethodsImplementer( $methodsImplementer );

  }

  /**
   * @param $name
   * @param array $params
   * @return mixed
   * @throws CException
   */
  public function invoke( $name, array $params = array())
  {
    return call_user_func_array( array($this->_provider, $name), $params);
  }

}

