<?php
/**
 *
 * @author: sem
 * Date: 05.06.12
 * Time: 0:56
 */
class ClusterAggregatorMethodsProvider
{

  /**
   * @var ClusterAggregator|null
   */
  private $aggregator = null;
  
  function __construct( ClusterAggregator $aggregator )
  {
    $this->aggregator = $aggregator;
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

  protected function getCommandName( $methodName )
  {
    $parts = explode('::', $methodName);
    return array_pop( $parts );
  }

  /**
   * Run the command with params by method name
   * @param $methodName
   * @param $methodParams
   * @param null $sortKey
   * @param null|string $sortDesc
   * @param array $concrete_sources
   * @return ClusterAggregatorDataProvider
   */
  protected function run( $methodName, $methodParams, $sortKey = null , $sortDesc = 'DESC', $concrete_sources = array() )
  {
    $method = $this->getCommandName( $methodName );

    $data = $this->aggregator->getDataCollection( $method, $methodParams, $sortKey, $concrete_sources );

    if( $sortKey && $sortDesc )
      $this->aggregator->applySort( $data, $sortKey, $sortDesc);

    return  $data;
  }

  /**
   * @param null $adviser_uid
   * @param null $only_check
   * @return ClusterAggregatorDataProvider
   */
  public function getAdviser( $adviser_uid = null, $only_check =null)
  {
    return $this->run( __METHOD__, func_get_args(), 'ts', 'DESC');
  }


  /**
   * @param null $adviser_uid
   * @return ClusterAggregatorDataProvider
   */
  public function getAdviserSessionInfo( $adviser_uid = null )
  {
    return $this->run( __METHOD__, func_get_args(), 'ts', 'DESC');
  }

  /**
   * Get session notes
   * @param int $adviser_uid
   * @param int $user_uid
   * @param string|null $phone_number
   * @return \ClusterAggregatorDataProvider
   */
  public function getAdviserClientNotes( $adviser_uid , $user_uid, $phone_number = null )
  {
    $sourceData =  $this->run( __METHOD__, func_get_args(), null, null);
    $sourceData->allowDuplicatesDelete();
    $this->aggregator->applySort( $sourceData, 'ts', 'DESC' );
    return $sourceData;
  }

  /**
   * Get session notes
   * @param $adviser_uid
   * @param null|string $date_from
   * @param null|string $date_to
   * @param null|int $offset
   * @param null|int $limit
   * @return ClusterAggregatorDataProvider
   */
  public function getAdviserSessionNotes( $adviser_uid, $date_from = null, $date_to = null, $offset = null, $limit = null )
  {
    return $this->run( __METHOD__, func_get_args(), 'ts', 'DESC');
  }

  /**
   * Get session notes
   * @param $adviser_uid
   * @return ClusterAggregatorDataProvider
   */
  public function getAdviserSessionNotesPeriods( $adviser_uid )
  {
    $sourceData =  $this->run( __METHOD__, func_get_args(), 'startTime', null);

    $sourceData->allowDuplicatesDelete();
    $sourceData->getPagination()->setPageSize(100);
    $sourceData->getData(1);
    return $sourceData;
  }

  /**
   * Get session notes
   * @param $adviser_uid
   * @param null $date_from
   * @param null $date_to
   * @return int
   */
  public function getAdviserSessionNotesCount( $adviser_uid, $date_from = null, $date_to = null )
  {
    $sourceData =  $this->run( __METHOD__, func_get_args(), 'count', 'DESC');
    $total = 0;
    foreach( $sourceData->getData() AS $row ){
      $total += $row['count'];
    }

    return $total;
  }

  /**
   * Get session notes
   * @param $adviser_uid
   * @param $session_id
   * @param string|null $concreteSource
   * @return \ClusterAggregatorDataProvider
   */
  public function getAdviserSessionNote( $adviser_uid, $session_id, $concreteSource = null )
  {
    return $this->run( __METHOD__, array(
      'adviser_uid'=>$adviser_uid,
      'session_id'   =>$session_id,
    ), 'ts', 'DESC', $concreteSource ? array( $concreteSource ) : array());
  }

  /**
   * Add/update session note
   * @param $destination
   * @param $adviser_uid
   * @param $session_id
   * @param $comment
   * @param null $created_at
   * @return \ClusterAggregatorDataProvider
   */
  public function addAdviserSessionNote( $destination, $adviser_uid , $session_id, $comment, $created_at = null )
  {
    return $this->run( __METHOD__, array(
      'adviser_uid'=>$adviser_uid,
      'session_id'   =>$session_id,
      'comment'     => $comment,
      'created_at'  => $created_at
    ), null, null, array($destination));
  }


  /**
   * Add/update session note
   * @param $destination
   * @param $adviser_uid
   * @param $session_id
   * @return \ClusterAggregatorDataProvider
   */
  public function deleteAdviserSessionNote( $destination, $adviser_uid , $session_id )
  {
    return $this->run( __METHOD__, array(
      'adviser_uid'=>$adviser_uid,
      'session_id'   =>$session_id
    ), null, null, array($destination));
  }

  /**
   * Get extra order notes
   * @param int $adviser_uid
   * @param int $user_uid
   * @param string|null $concrete_source
   * @return ClusterAggregatorDataProvider
   */
  public function getAdviserExtraOrdersNotes( $adviser_uid, $user_uid, $concrete_source = null )
  {
    return $this->run( __METHOD__, array(
      'adviser_uid'=>$adviser_uid,
      'user_uid'   =>$user_uid
    ), 'ts', 'DESC', $concrete_source ? array($concrete_source) : array());
  }

  /**
   * Get extra order note
   * @param int $adviser_uid
   * @param int $order_id
   * @param string|null $concrete_source
   * @return ClusterAggregatorDataProvider
   */
  public function getAdviserExtraOrdersNote( $adviser_uid, $order_id, $concrete_source = null )
  {
    return $this->run( __METHOD__, array(
      'adviser_uid'=>$adviser_uid,
      'order_id'   =>$order_id
    ), 'ts', 'DESC', $concrete_source ? array($concrete_source) : array());
  }

  /**
   * add/update extra order note
   * @param $destination
   * @param int $adviser_uid
   * @param int $order_id
   * @param $comment
   * @param null $created_at
   * @return ClusterAggregatorDataProvider
   */
  public function addAdviserExtraOrdersNote( $destination, $adviser_uid , $order_id, $comment, $created_at = null  )
  {
    return $this->run( __METHOD__, array(
      'adviser_uid'=>$adviser_uid,
      'order_id'   =>$order_id,
      'comment'     => $comment,
      'created_at'  => $created_at
    ), null, null, array($destination) );
  }

  /**
   * Delete extra order note
   * @param $destination
   * @param int $adviser_uid
   * @param int $order_id
   * @return ClusterAggregatorDataProvider
   */
  public function deleteAdviserExtraOrdersNote( $destination, $adviser_uid , $order_id )
  {

    return $this->run( __METHOD__, array(
      'adviser_uid' => $adviser_uid,
      'order_id'  => $order_id
    ), null, null, array($destination) );
  }

  /**
   * Get extra and meta orders
   * @param int $adviser_uid
   * @param int|null $limit
   * @param int|null $offset
   * @param bool|null $count
   * @return ClusterAggregatorDataProvider
   */
  public function getAdviserExtraOrders( $adviser_uid, $limit = null, $offset = null, $count = null )
  {
    return $this->run( __METHOD__, func_get_args(), 'ts', 'DESC' );
  }


  /**
   * Get extra and meta order
   * @param int $adviser_uid
   * @param int $order_id
   * @param string|null $concrete_source
   * @return ClusterAggregatorDataProvider
   */
  public function getAdviserExtraOrMetaOrder( $adviser_uid, $order_id, $concrete_source = null )
  {
    return $this->run( __METHOD__, func_get_args(), 'ts', 'DESC', $concrete_source );
  }


  /**
   * @param int $order_id
   * @param string|null $type
   * @param string|null $status
   * @param bool $return_count
   * @return \ClusterAggregatorDataProvider
   */
  public function getExtraOrderFilesList( $order_id, $type = null, $status = null, $return_count = false)
  {
    return $this->run( __METHOD__, func_get_args(), 'ts' );
  }

  /**
   * @param int $adviser_uid
   * @param int $user_uid
   * @param int $order_id
   * @param string $status
   * @param string $comment
   * @return \ClusterAggregatorDataProvider
   */
  public function updateAdviserExtraOrder( $adviser_uid, $user_uid, $order_id, $status, $comment )
  {
    return $this->run( __METHOD__, func_get_args(), 'ts' );
  }

  /**
   * @param string $file_fields
   * @param int $order_id
   * @param string $status
   * @param string $type
   * @param array $filesArr
   * @return array
   */
  public function addExtraOrderFiles( $file_fields, $order_id, $status, $type, $filesArr = array() )
  {
    return $this->run( __METHOD__, func_get_args(), 'ts' );
  }

  /**
   * @param string $file_name
   * @param int|null $order_id
   * @return array
   * @soap
   * @command
   */
  public function getExtraOrderFile( $file_name, $order_id = null )
  {
    return $this->run( __METHOD__, func_get_args(), 'ts' );
  }


  /**
   * @param string $file_name
   * @param int $order_id
   * @return array
   * @soap
   * @command
   */
  public function deleteExtraOrderFile( $file_name, $order_id )
  {
    return $this->run( __METHOD__, func_get_args(), 'ts' );
  }
  /**
   * Update Meta order for expert
   * @param int $adviser_uid
   * @param int $user_uid
   * @param int $order_id
   * @param string $status
   * @param string $comment
   * @return array
   * @soap
   * @command
   */
  public function updateAdviserMetaOrder( $adviser_uid, $user_uid, $order_id, $status, $comment)
  {
    return $this->run( __METHOD__, func_get_args(), 'ts' );
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
    $data = $this->run( __METHOD__, func_get_args(), 'ts' );
    $data->getPagination()->setPageSize(200);
    return $data;
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
    return $this->run( __METHOD__, func_get_args(), 'ts' );
  }

  /**
   * @param int $adviser_uid
   * @param int|string $time
   * @param int|string $hour
   * @return array
   * @soap
   * @command
   */
  public function getAdviserSchedulesHours( $adviser_uid, $time, $hour ){
    return $this->run( __METHOD__, func_get_args());
  }


  /**
   * @param int $adviser_uid
   * @param int|string $current_week
   * @param int|string $current_year
   * @param array $hours
   * @return array
   * @soap
   * @command
   */
  public function updateAdviserSchedules( $adviser_uid, $current_week, $current_year, $hours )
  {
    return $this->run( __METHOD__, func_get_args());
  }

}
