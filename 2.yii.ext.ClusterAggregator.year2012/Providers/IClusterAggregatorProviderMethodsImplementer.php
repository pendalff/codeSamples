<?php
/**
 * Is a from the forgetfulness of the methods signatures for providers
 * User: sem
 * Date: 21.06.12
 * Time: 12:48
 */
interface IClusterAggregatorProviderMethodsImplementer
{
  /**
   * @static
   * @abstract
   * @param IClusterAggregatorProviders $provider
   * @param IClusterAggregatorReceiver $reciever
   * @return IClusterAggregatorProviderMethodsImplementer/ClusterAggregatorMethodsImplementer
   */
  public static function factory(IClusterAggregatorProviders $provider, IClusterAggregatorReceiver $reciever);

  /**
   * @param IClusterAggregatorProviders $provider
   * @param IClusterAggregatorReceiver $reciever
   */
  public function __construct( IClusterAggregatorProviders $provider, IClusterAggregatorReceiver $reciever );

  /**
   * @param int $adviser_uid
   * @param bool|null $only_check
   * @return array
   * @soap
   * @command
   */
  public function getAdviser($adviser_uid, $only_check =null);

  /**
   * @param int $adviser_uid
   * @return array
   * @soap
   * @command
   */
  public function getAdviserSessionInfo( $adviser_uid );

  /**
   * Adviser notes for user
   * @param int $adviser_uid
   * @param int $user_uid
   * @param string|null $phone_number
   * @return array
   * @soap
   * @command
   */
  public function getAdviserClientNotes( $adviser_uid, $user_uid, $phone_number = null );

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
  public function getAdviserSessionNotes( $adviser_uid, $date_from = null, $date_to = null, $offset = null, $limit = null );

  /**
   * count adviser notes
   * @param int $adviser_uid
   * @param string|null $date_from
   * @param string|null $date_to
   * @return array
   * @soap
   * @command
   */
  public function getAdviserSessionNotesCount( $adviser_uid, $date_from = null, $date_to = null);


  /**
   * Adviser notes sessions months
   * @param int $adviser_uid
   * @return array
   * @soap
   * @command
   */
  public function getAdviserSessionNotesPeriods( $adviser_uid );

  /**
   * get session note
   * @param int $adviser_uid
   * @param int $session_id
   * @return array
   * @soap
   * @command
   */
  public function getAdviserSessionNote( $adviser_uid , $session_id );


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
  public function addAdviserSessionNote( $adviser_uid , $session_id, $comment, $created_at = null);

  /**
   * remove session note
   * @param int $adviser_uid
   * @param int $session_id
   * @return array
   * @soap
   * @command
   */
  public function deleteAdviserSessionNote( $adviser_uid , $session_id );

  /**
   * get extraorder note
   * @param int $adviser_uid
   * @param int $order_id
   * @return array
   * @soap
   * @command
   */
  public function getAdviserExtraOrdersNote( $adviser_uid , $order_id );


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
  public function addAdviserExtraOrdersNote( $adviser_uid , $order_id, $comment, $created_at = null);

  /**
   * remove extraorder note
   * @param int $adviser_uid
   * @param int $order_id
   * @return array
   * @soap
   * @command
   */
  public function deleteAdviserExtraOrdersNote( $adviser_uid , $order_id );

  /**
   * Get extra orders notes
   * @param int $adviser_uid
   * @param int $user_uid
   * @return array
   * @soap
   * @command
   */
  public function getAdviserExtraOrdersNotes( $adviser_uid, $user_uid );

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
  public function getAdviserExtraOrders( $adviser_uid, $limit = null, $offset = null, $count = null );

  /**
   *  return array meta and extra orders
   * @param int $adviser_uid
   * @param int $order_id
   * @return array
   * @soap
   * @command
   */
  public function getAdviserExtraOrMetaOrder($adviser_uid, $order_id);


  /**
   * @param int $order_id
   * @param string|null $type
   * @param string|null $status
   * @param bool $return_count
   * @return array
   * @soap
   * @command
   */
  public function getExtraOrderFilesList( $order_id, $type = null, $status = null, $return_count = false );

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
  public function updateAdviserExtraOrder( $adviser_uid, $user_uid, $order_id, $status, $comment );


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
  public function addExtraOrderFiles( $file_fields, $order_id, $status, $type, $filesArr = array() );


  /**
   * @param string $file_name
   * @param int|null $order_id
   * @return array
   * @soap
   * @command
   */
  public function getExtraOrderFile( $file_name, $order_id = null );

  /**
   * @param string $file_name
   * @param int $order_id
   * @return array
   * @soap
   * @command
   */
  public function deleteExtraOrderFile( $file_name, $order_id  );

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
  public function updateAdviserMetaOrder( $adviser_uid, $user_uid, $order_id, $status, $comment);


  /**
   * @param int $adviser_uid
   * @param int|string $current_week
   * @param int|string $current_year
   * @return array
   * @soap
   * @command
   */
  public function getAdviserSchedules( $adviser_uid, $current_week, $current_year );

  /**
   * @param int $adviser_uid
   * @param int|string $time
   * @param int|string $hour
   * @return array
   * @soap
   * @command
   */
  public function getAdviserSchedulesAppOrders( $adviser_uid, $time, $hour );

  /**
   * @param int $adviser_uid
   * @param int|string $current_week
   * @param int|string $current_year
   * @return array
   * @soap
   * @command
   */
  public function getAdviserSchedulesHours( $adviser_uid, $current_week, $current_year );

  /**
   * @param int $adviser_uid
   * @param int|string $current_week
   * @param int|string $current_year
   * @param array $hours
   * @param bool $force_slaves
   * @return array
   * @soap
   * @command
   */
  public function updateAdviserSchedules( $adviser_uid, $current_week, $current_year, $hours, $force_slaves = true );

}
