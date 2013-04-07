<?php
/**
 * schedules
 * User: sem
 * Date: 08.06.12
 * Time: 14:47
 */
class ClusterAggregatorCommandGetAdviserSchedulesAppOrders extends ClusterAggregatorCommandAbstract
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
   */
  public function process( ClusterAggregatorContextCommand $context ){

    $this->_chain->process( $context );

    $adviser = $context->getData();

    $time = $context->get('time');
    $hour = $context->get('hour');

    $q =("SELECT
    o.*, u.PRIVATE_ROW_SKIPPED, u.PRIVATE_ROW_SKIPPED, u.PRIVATE_ROW_SKIPPED,
    UNIX_TIMESTAMP( o.`startTime` ) AS ts
    FROM `PRIVATE_ROW_SKIPPED` AS o
    LEFT JOIN `PRIVATE_ROW_SKIPPED` AS u ON u.PRIVATE_ROW_SKIPPED = o.PRIVATE_ROW_SKIPPED
    WHERE o.`PRIVATE_ROW_SKIPPED` IN ('CONFIRMED', 'WAITING',  'EXECUTING')
    AND o.`PRIVATE_ROW_SKIPPED`='".$adviser->uid."'
    AND o.`PRIVATE_ROW_SKIPPED` = 'APP'
    AND o.`PRIVATE_ROW_SKIPPED` >= '".date('Y-m-d', $time)." ".$hour.":00:00'
    AND o.`PRIVATE_ROW_SKIPPED` <= '".date('Y-m-d', $time)." ".$hour.":59:59'");

    $data = Yii::app()->db->createCommand( $q )->queryAll();

    $context->setData( $data );

    return $data;
  }


}
