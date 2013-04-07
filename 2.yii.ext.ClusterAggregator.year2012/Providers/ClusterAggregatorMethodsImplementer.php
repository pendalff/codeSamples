<?php
/**
 * Methods Templater
 * User: sem
 * Date: 21.06.12
 * Time: 12:38
 */
class ClusterAggregatorMethodsImplementer implements IClusterAggregatorProviderMethodsImplementer
{
  /**
   * @var IClusterAggregatorProviders|null
   */
  private $_provider = null;

  /**
   * @var IClusterAggregatorReceiver|null
   */
  private $_reciever = null;

  /**
   * @static
   * @param IClusterAggregatorProviders $provider
   * @param IClusterAggregatorReceiver $reciever
   * @return ClusterAggregatorMethodsImplementer
   */
  public static function factory( IClusterAggregatorProviders $provider, IClusterAggregatorReceiver $reciever )
  {
    return new self( $provider, $reciever);
  }

  /**
   * @param IClusterAggregatorProviders $provider
   * @param IClusterAggregatorReceiver $reciever
   */
  public function __construct( IClusterAggregatorProviders $provider, IClusterAggregatorReceiver $reciever )
  {
    $this->_provider = $provider;
    $this->_reciever = $reciever;
  }

  protected function getCommandName( $methodName )
  {
    $parts = explode('::', $methodName);
    return array_pop( $parts );
  }

  /**
   * Run the command with params by method name
   * @param $methodName
   * @param $methodParams
   * @param bool $needMapping
   * @return array
   */
  protected function run( $methodName, $methodParams, $needMapping = true )
  {
    $method = $this->getCommandName( $methodName );

    if($needMapping){
      $methodParams = $this->mapArguments($methodName, ($methodParams));
    }

    $methodParams = array_merge( $this->_reciever->getParams(), $methodParams );

    return $this->_provider->runCommand( $method, $methodParams );
  }

  /**
   * @param int $adviser_uid
   * @param bool|null $only_check
   * @return array
   * @soap
   * @command
   */
  public function getAdviser($adviser_uid, $only_check =null)
  {
    return $this->run( __METHOD__, func_get_args() );
  }

  /**
   * @param int $adviser_uid
   * @return array
   * @soap
   * @command
   */
  public function getAdviserSessionInfo($adviser_uid)
  {
    return $this->run( __METHOD__, func_get_args() );
  }

  /**
   * Adviser notes for user
   * @param int $adviser_uid
   * @param int $user_uid
   * @param string|null $phone_number
   * @return array
   * @soap
   * @command
   */
  public function getAdviserClientNotes($adviser_uid, $user_uid, $phone_number = null)
  {
    return $this->run( __METHOD__, func_get_args() );
  }

  /**
   * Adviser notes for sessions
   * @param int $adviser_uid
   * @param string|null $date_from
   * @param string|null $date_to
   * @param int|null $offset
   * @param int|null $limit
   * @return array
   * @soap
   * @command
   */
  public function getAdviserSessionNotes($adviser_uid, $date_from = null, $date_to = null, $offset = null, $limit = null)
  {
    return $this->run( __METHOD__, func_get_args() );
  }

  /**
   * count adviser notes
   * @param int $adviser_uid
   * @param string|null $date_from
   * @param string|null $date_to
   * @return array
   * @soap
   * @command
   */
  public function getAdviserSessionNotesCount($adviser_uid, $date_from = null, $date_to = null)
  {
    return $this->run( __METHOD__, func_get_args() );
  }

  /**
   * Adviser notes sessions months
   * @param int $adviser_uid
   * @return array
   * @soap
   * @command
   */
  public function getAdviserSessionNotesPeriods($adviser_uid)
  {
    return $this->run( __METHOD__, func_get_args() );
  }

  /**
   * get session note
   * @param int $adviser_uid
   * @param int $session_id
   * @return array
   * @soap
   * @command
   */
  public function getAdviserSessionNote($adviser_uid, $session_id)
  {
    return $this->run( __METHOD__, func_get_args() );
  }

  /**
   * Add/update session note
   * @param int $adviser_uid
   * @param int $session_id
   * @param string $comment
   * @param string|null $created_at
   * @return array
   * @soap
   * @command
   */
  public function addAdviserSessionNote($adviser_uid, $session_id, $comment, $created_at = null)
  {
    return $this->run( __METHOD__, func_get_args() );
  }

  /**
   * remove session note
   * @param int $adviser_uid
   * @param int $session_id
   * @return array
   * @soap
   * @command
   */
  public function deleteAdviserSessionNote($adviser_uid, $session_id)
  {
    return $this->run( __METHOD__, func_get_args() );
  }

  /**
   * get extraorder note
   * @param int $adviser_uid
   * @param int $order_id
   * @return array
   * @soap
   * @command
   */
  public function getAdviserExtraOrdersNote($adviser_uid, $order_id)
  {
    return $this->run( __METHOD__, func_get_args() );
  }

  /**
   * Add/update extraorder note
   * @param int $adviser_uid
   * @param int $order_id
   * @param string $comment
   * @param string|null $created_at
   * @return array
   * @soap
   * @command
   */
  public function addAdviserExtraOrdersNote($adviser_uid, $order_id, $comment, $created_at = null)
  {
    return $this->run( __METHOD__, func_get_args() );
  }

  /**
   * remove extraorder note
   * @param int $adviser_uid
   * @param int $order_id
   * @return array
   * @soap
   * @command
   */
  public function deleteAdviserExtraOrdersNote($adviser_uid, $order_id)
  {
    return $this->run( __METHOD__, func_get_args() );
  }

  /**
   * Get extra orders notes
   * @param int $adviser_uid
   * @param int $user_uid
   * @return array
   * @soap
   * @command
   */
  public function getAdviserExtraOrdersNotes($adviser_uid, $user_uid)
  {
    return $this->run( __METHOD__, func_get_args() );
  }

  /**
   * Get extra and meta orders
   * @param int $adviser_uid
   * @param int|null $limit
   * @param int|null $offset
   * @param bool|null $count
   * @return array
   * @soap
   * @command
   */
  public function getAdviserExtraOrders( $adviser_uid, $limit = null, $offset = null, $count = null )
  {
    return $this->run( __METHOD__, func_get_args() );
  }

  /**
   *  return array meta and extra orders
   * @param int $adviser_uid
   * @param int $order_id
   * @return array
   * @soap
   * @command
   */
  public function getAdviserExtraOrMetaOrder($adviser_uid, $order_id)
  {
    return $this->run( __METHOD__, func_get_args() );
  }

  /**
   * @param int $order_id
   * @param string|null $type
   * @param string|null $status
   * @param bool $return_count
   * @return array
   * @soap
   * @command
   */
  public function getExtraOrderFilesList($order_id, $type = null, $status = null, $return_count = false)
  {
    return $this->run( __METHOD__, func_get_args() );
  }

  /**
   * @param int $adviser_uid
   * @param int $user_uid
   * @param int $order_id
   * @param string $status
   * @param string $comment
   * @return array
   * @soap
   * @command
   */
  public function updateAdviserExtraOrder($adviser_uid, $user_uid, $order_id, $status, $comment)
  {
    return $this->run( __METHOD__, func_get_args() );
  }

  /**
   * @param string $file_fields
   * @param int $order_id
   * @param string $status
   * @param string $type
   * @param array $filesArr
   * @return array
   * @soap
   * @command
   */
  public function addExtraOrderFiles( $file_fields, $order_id, $status, $type, $filesArr = array() )
  {
    return $this->run( __METHOD__, func_get_args());
  }

  /**
   * @param string $file_name
   * @param int|null $adviser_uid
   * @return array
   * @soap
   * @command
   */
  public function getExtraOrderFile( $file_name, $adviser_uid = null )
  {
    return $this->run( __METHOD__, func_get_args());
  }

  /**
   * @param string $file_name
   * @param int $order_id
   * @return array
   * @soap
   * @command
   */
  public function deleteExtraOrderFile( $file_name, $order_id  )
  {
    return $this->run( __METHOD__, func_get_args());
  }

  /**
   * @param int $adviser_uid
   * @param int $user_uid
   * @param int $order_id
   * @param string $status
   * @param string $comment
   * @return array
   * @soap
   * @command
   */
  public function updateAdviserMetaOrder($adviser_uid, $user_uid, $order_id, $status, $comment)
  {
    return $this->run( __METHOD__, func_get_args() );
  }


  /**
   * @param int $adviser_uid
   * @param int|string $current_week
   * @param int|string $current_year
   * @return array
   * @soap
   * @command
   */
  public function getAdviserSchedules( $adviser_uid, $current_week, $current_year )
  {
     return $this->run( __METHOD__, func_get_args() );
  }

  /**
   * @param int $adviser_uid
   * @param int|string $time
   * @param int|string $hour
   * @return array
   * @soap
   * @command
   */
  public function getAdviserSchedulesAppOrders( $adviser_uid, $time, $hour )
  {
    return $this->run( __METHOD__, func_get_args());
  }


  /**
   * @param int $adviser_uid
   * @param int|string $current_week
   * @param int|string $current_year
   * @return array
   * @soap
   * @command
   */
  public function getAdviserSchedulesHours( $adviser_uid, $current_week, $current_year )
  {
    return $this->run( __METHOD__, func_get_args());
  }


  /**
   * @param int $adviser_uid
   * @param int|string $current_week
   * @param int|string $current_year
   * @param array $hours
   * @param bool $force_slaves - force delete all slave projects
   * @return array
   * @soap
   * @command
   */
  public function updateAdviserSchedules( $adviser_uid, $current_week, $current_year, $hours, $force_slaves = true )
  {
    return $this->run( __METHOD__, func_get_args());
  }


  /**
   * For lazy-lazy mapping params to actions
   * @param $method
   * @param array $parameters
   * @return array
   * @throws CException
   */
  public function mapArguments( $method, $parameters = array() ){
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
    //return array('STRUCT'=>$args,'BE'=>$parameters,$method);
    return $args;
  }
}
