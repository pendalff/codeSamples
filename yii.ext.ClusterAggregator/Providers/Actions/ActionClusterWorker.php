<?php
/**
 * Base for all cluster actions
 * User: sem
 * Date: 07.06.12
 * Time: 18:14
 */
class ActionClusterWorker extends ActionClusterAbstract
{
  /**
   * Run action method
   */
  public function run() {
    $result = array(
      'errorCode' => 0,
      'result' => array()
    );

    try{
      $result['result'] = $this->getCommand()->process( $this->createContext() );
    }
    catch( CException $e )
    {
      $result['errorCode'] = $e->getCode()==2000 ? 2000 : 3000;
      $result['error'] = $e->getMessage();
    }

    return $result;
  }

}
