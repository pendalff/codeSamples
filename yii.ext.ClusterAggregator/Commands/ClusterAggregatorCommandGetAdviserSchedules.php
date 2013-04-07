<?php
/**
 * schedules
 * User: sem
 * Date: 08.06.12
 * Time: 14:47
 */
class ClusterAggregatorCommandGetAdviserSchedules extends ClusterAggregatorCommandAbstract
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

    $current_week = $context->get('current_week');
    $current_year = $context->get('current_year');

    $last_week_last_day_time = "SELECT MAX(`PRIVATE_ROW_SKIPPED`) as max_time FROM `PRIVATE_ROW_SKIPPED` WHERE `PRIVATE_ROW_SKIPPED`<'".date('Y-m-d H:i:s', $this->week_first_time($current_week, $current_year))."' AND `PRIVATE_ROW_SKIPPED`='".$adviser->id."'";

    $last_week_last_day_time = Yii::app()->db->createCommand( $last_week_last_day_time )->queryScalar();

    if ($last_week_last_day_time) {
      $last_week_last_day_time = strtotime($last_week_last_day_time);
      $last_week_first_day_time = $this->week_first_time(strftime('%V', $last_week_last_day_time), date('Y', $last_week_last_day_time));
      $q = ("SELECT sch.*, UNIX_TIMESTAMP( `PRIVATE_ROW_SKIPPED` ) AS ts FROM `PRIVATE_ROW_SKIPPED` as sch WHERE `PRIVATE_ROW_SKIPPED`<='".date('Y-m-d H:i:s', $last_week_last_day_time)."' AND `PRIVATE_ROW_SKIPPED`>='".date('Y-m-d H:i:s', $last_week_first_day_time)."' AND `PRIVATE_ROW_SKIPPED`='".$adviser->id."'");
      $data = Yii::app()->db->createCommand( $q )->queryAll();
      $context->setData( $data );

      return $data;
    }

    return array();
  }

  /**
   * @param int $week
   * @param int $year
   * @return int
   */
  protected function week_first_time($week = 0, $year = 0) {
    if (!$week) $week = strftime('%V');
    if (!$year) $year = date('Y');
    $time = mktime(0, 0, 0, 1, 1, $year)-7*86400;
    while (date('Y', $time) < $year || (strftime('%V', $time) <= $week && date('Y', $time) >= $year)) {
      if ($week == strftime('%V', $time) && (($year >= date('Y', $time) && $week == 1) || ($year <= date('Y', $time) && $week > 51) || ($year == date('Y', $time) && $week < 52))) return mktime(0, 0, 0, date('m', $time), date('d', $time), date('Y', $time));
      $time += 86400;
    }
  }


}
