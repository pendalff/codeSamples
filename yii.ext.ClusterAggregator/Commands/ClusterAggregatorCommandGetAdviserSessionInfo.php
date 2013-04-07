<?php
/**
 * GetAdviserSessionInfo
 * User: sem
 * Date: 08.06.12
 * Time: 14:47
 */
class ClusterAggregatorCommandGetAdviserSessionInfo extends ClusterAggregatorCommandAbstract
{
  /**
   * @var null|ClusterAggregatorCommandChain
   */
  private $_chain = null;

  public function __construct( )
  {
    $chain = new ClusterAggregatorCommandChain();
    $chain->appendCommand( new ClusterAggregatorCommandGetAdviser() );
    $this->_chain = $chain;
  }

  /**
   * Return adviser current session data
   * @param ClusterAggregatorContextCommand $context
   * @return array
   * @todo remove test json objects - replace with call to ps40
   */
  public function process( ClusterAggregatorContextCommand $context ){

    $this->_chain->process( $context );

    $adviser = $context->getData();

    $startedSessions = Yii::app()->ps40->GetAdviserStartedSessions( array( 'uid'=> $adviser->uid ) );

    if( is_object($startedSessions) && isset($startedSessions->GetStartedSessionsResult) && isset($startedSessions->GetStartedSessionsResult->StartedSessionResult)){
      $startedSessions = $startedSessions->GetStartedSessionsResult->StartedSessionResult;
      $startedSessions = (array)$startedSessions;
      reset( $startedSessions );
      $startedSessions = current( $startedSessions );
      $startedSessions = (array) $startedSessions;
    }

    $data = array();
    if( is_array($startedSessions) ){

      $sessionUid = $startedSessions['sessionId'];

      $q = "SELECT
                  PRIVATE_ROW_SKIPPED
                  UNIX_TIMESTAMP( PRIVATE_ROW_SKIPPED ) AS ts
                  FROM PRIVATE_ROW_SKIPPED as s
                  LEFT JOIN PRIVATE_ROW_SKIPPED as u ON s.PRIVATE_ROW_SKIPPED = u.id
                  LEFT JOIN PRIVATE_ROW_SKIPPED as o ON s.PRIVATE_ROW_SKIPPED = o.id
                  LEFT JOIN PRIVATE_ROW_SKIPPED as ur ON u.PRIVATE_ROW_SKIPPED = ur.id
                  LEFT JOIN PRIVATE_ROW_SKIPPED AS sc ON s.id = sc.PRIVATE_ROW_SKIPPED AND sc.PRIVATE_ROW_SKIPPED IN ('cli','incoming') AND sc.PRIVATE_ROW_SKIPPED IN ('CN','APP','direct')
                  WHERE s.session_uid = '".$sessionUid."'";
      $data = Yii::app()->db->createCommand( $q )->queryRow();
    }

    $context->setData( $data );

    return $data;
  }


}
