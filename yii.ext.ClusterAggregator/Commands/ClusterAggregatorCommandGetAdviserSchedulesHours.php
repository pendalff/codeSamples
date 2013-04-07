<?php
/**
 * schedules
 * User: sem
 * Date: 08.06.12
 * Time: 14:47
 */
class ClusterAggregatorCommandGetAdviserSchedulesHours extends ClusterAggregatorCommandAbstract
{

  protected $current_week = null;

  protected $current_year = null;

  protected $week_days    = null;

  protected $startTime    = null;

  protected $endTime      = null;

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


    $current_week = $this->current_week = $context->get('current_week');
    $current_year = $this->current_year = $context->get('current_year');
    $days         = $this->week_days = $this->week_days($current_week, $current_year);

    reset($days);
    $startTime = $this->startTime = current($days);
    $endTime   = $this->endTime   = (array_pop( $days ) + 24 * 3600)-1;

    $ordersQuery = "SELECT
    COUNT( PRIVATE_ROW_SKIPPED ) as cnt,
    DAYOFWEEK( PRIVATE_ROW_SKIPPED ) AS day ,
    HOUR( PRIVATE_ROW_SKIPPED ) AS hour
  FROM  ".OrderModel::model()->tableName()."
  WHERE
         `PRIVATE_ROW_SKIPPED` IN ('CONFIRMED',  'WAITING',  'EXECUTING')
    AND  `PRIVATE_ROW_SKIPPED` =  '".$adviser->uid."'
    AND  `PRIVATE_ROW_SKIPPED` =  'APP'
    AND (`PRIVATE_ROW_SKIPPED` BETWEEN STR_TO_DATE(   FROM_UNIXTIME('{$startTime}'),  '%Y-%m-%d %H:%i:%s' ) AND STR_TO_DATE(  FROM_UNIXTIME('{$endTime}'),  '%Y-%m-%d %H:%i:%s' ))
  GROUP BY PRIVATE_ROW_SKIPPED , PRIVATE_ROW_SKIPPED";

    $data['orders'] = $this->normalizeResult(Yii::app()->db->createCommand($ordersQuery)->queryAll());


    $schedulesQuery = "SELECT
    COUNT( * ) as cnt,
    DAYOFWEEK( time ) AS day ,
    HOUR( time ) AS hour
  FROM  ".AdviserSchedulesModel::model()->tableName()."
  WHERE `PRIVATE_ROW_SKIPPED`='".$adviser->id."'
    AND (
     `time` BETWEEN STR_TO_DATE(   FROM_UNIXTIME('{$startTime}'),  '%Y-%m-%d %H:%i:%s' )
    AND STR_TO_DATE(  FROM_UNIXTIME('{$endTime}'),  '%Y-%m-%d %H:%i:%s' )
    )
  GROUP BY day , hour";
    $data['values'] = $this->normalizeResult(Yii::app()->db->createCommand($schedulesQuery)->queryAll());

    $absentQuery = "SELECT *  FROM  PRIVATE_ROW_SKIPPED
  WHERE `PRIVATE_ROW_SKIPPED`='".$adviser->id."'
    AND
     (
     `PRIVATE_ROW_SKIPPED` BETWEEN
     STR_TO_DATE( FROM_UNIXTIME('{$startTime}'),  '%Y-%m-%d %H:%i:%s' )
     AND
     STR_TO_DATE( FROM_UNIXTIME('{$endTime}'),  '%Y-%m-%d %H:%i:%s' )
    )
    AND
     (
       (`PRIVATE_ROW_SKIPPED` BETWEEN
       STR_TO_DATE( FROM_UNIXTIME('{$startTime}'),  '%Y-%m-%d %H:%i:%s' )
       AND
       STR_TO_DATE( FROM_UNIXTIME('{$endTime}'),  '%Y-%m-%d %H:%i:%s' ))
       OR
      `PRIVATE_ROW_SKIPPED` >= STR_TO_DATE( FROM_UNIXTIME('{$endTime}'),  '%Y-%m-%d %H:%i:%s' )
    )";

    $data['absent'] = $this->getAbsentValues(Yii::app()->db->createCommand($absentQuery)->queryAll());

    $data['aq'] = $absentQuery;
    $context->setData( $data );

    return $data;
  }

  /**
   * @param array $rowset
   * @return array
   */
  protected function normalizeResult( array $rowset )
  {

    $result = array();
    foreach( $rowset AS $row ){
      $day = $row['day']-1; //т.к. мускуль выдает 2 для понедельника, а php формат w - 1 для понедельника
      if(!isset($result[ $day ]))
      {
        $result[ $day ] = array();
      }
      $result[ $day ][ $row['hour'] ]+=$row['cnt'];
    }

    return $result;
  }

  /**
   * @param array $rowset
   * @return array
   */
  protected function getAbsentValues( array $rowset )
  {

    $result = array();
    foreach( $rowset AS $row ){
      $minStart = strtotime( $row['startDate'] );
      $maxEnd   = strtotime( $row['endDate'] );

      if( $minStart < $this->startTime ){
        $minStart = $this->startTime;
      }
      if( $maxEnd > $this->endTime ){
        $maxEnd = $this->endTime;
      }

      for( $i = $minStart; $i <= $maxEnd; $i = $i + 3600 ){
        $day  = date('w', $i);
        $hour = date('G', $i);

        if(!isset($result[ $day ]))
        {
          $result[ $day ] = array();
        }
        $result[$day][$hour] = 1;
      }
    }
    return $result;
  }


  protected function week_days($pWeek = 0, $pYear = 0) {
    $array = null;

    if (!$pWeek)
        {
          $lWeek = strftime('%V');
        }
    else {
      $lWeek = $pWeek;
    }

    if (!$pYear)
        {
          $lYear = date('Y');
        }
    else {
      $lYear = $pYear;
    }

    $lTime = mktime(0, 0, 0, 1, 1, $lYear);

    $lTimesArray = array();

    $lPremierWeek = date('w', mktime(0, 0, 0, 1, 1, $lYear)) - 1;

    $lIfNewYear = 0;
    if ($lPremierWeek < 4)
        {
          $lIfNewYear = 1;
        }


    $lStrTime = date("c", $lTime) . " +" . ($lWeek - $lIfNewYear) . " week -" . $lPremierWeek . " day";
    $lDate = strtotime($lStrTime);
    for ($i = 0; $i < 7; $i++) {
      $lTimesArray[date("w", $lDate)] = $lDate;
      $lDate = strtotime(date("c", $lDate) . " +1 day");
    }

    return $lTimesArray;
  }

}
