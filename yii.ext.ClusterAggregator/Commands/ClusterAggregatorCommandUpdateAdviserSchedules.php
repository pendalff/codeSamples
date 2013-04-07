<?php
/**
 *
 * @author: sem
 * Date: 24.06.12
 * Time: 21:54
 */
class ClusterAggregatorCommandUpdateAdviserSchedules  extends ClusterAggregatorCommandAbstract
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
    $result = array(
      'updated' => false,
      'message' => 'Не удалось обновить расписание - возможно заблокировано изменение текущего дня или присутствует бронирование в ближайшее время'
    );

    $this->_chain->process( $context );

    $adviser = $context->getData();

    $current_week = $context->get('current_week');
    $current_year = $context->get('current_year');
    $hours = $context->get('hours');

    $days = $this->week_days($current_week, $current_year);
    $first_time = $days[1];
    reset($days);
    $startTime = $this->startTime = current($days);
    $endTime   = $this->endTime   = (array_pop( $days ) + 24 * 3600)-1;

    if( $context->get('force_slaves', false) && $context->get('is_remote') ){
      Yii::app()->db->createCommand("DELETE
            FROM `/*PRIVATE_ROW_SKIPPED*/`
            WHERE
              `/*PRIVATE_ROW_SKIPPED*/`='".$adviser->id."'
              AND
              `/*PRIVATE_ROW_SKIPPED*/` BETWEEN
              STR_TO_DATE(
              FROM_UNIXTIME('{$startTime}'),  '%Y-%m-%d %H:%i:%s' )
              AND
              STR_TO_DATE(
              FROM_UNIXTIME('{$endTime}'),  '%Y-%m-%d %H:%i:%s' )")->query();
    }

    for ($i = 1; $i <= 7; $i++) {
      for ($j = 0; $j < 24; $j++) {
        $time = mktime($j, 0, 0, date('m', $first_time), date('d', $first_time)+$i-1, date('Y', $first_time));

        if (
          isset($hours[$i][$j]) &&
          (!(date('d.m.Y') == date('d.m.Y', $time) && date('H') >= 8)
          ||
          $adviser->lock_current_day !='y')
          && $time > time()
          &&
          !Yii::app()->db->createCommand(
            "SELECT
            COUNT(*)
            FROM `/*PRIVATE_ROW_SKIPPED*/`
            WHERE `/*PRIVATE_ROW_SKIPPED*/`
            IN ('CONFIRMED', 'WAITING', 'EXECUTING')
            AND `/*PRIVATE_ROW_SKIPPED*/`='APP'
            AND `/*PRIVATE_ROW_SKIPPED*/`>='".date('Y-m-d H', $time).":00:00'
            AND `/*PRIVATE_ROW_SKIPPED*/`<'".date('Y-m-d H', $time).":00:00' + INTERVAL 1 HOUR
            AND `/*PRIVATE_ROW_SKIPPED*/`='".$adviser->uid."'")->queryScalar()
        )
        {
          Yii::app()->db->createCommand("DELETE
				  FROM `/*PRIVATE_ROW_SKIPPED*/`
				  WHERE
				    `/*PRIVATE_ROW_SKIPPED*/`='".$adviser->id."'
				    AND
				    `/*PRIVATE_ROW_SKIPPED*/`='".date('Y-m-d H', $time).":00:00'")->query();

          if ($hours[$i][$j]) {
            $updated = Yii::app()->db->createCommand("INSERT INTO `/*PRIVATE_ROW_SKIPPED*/`
            SET `/*PRIVATE_ROW_SKIPPED*/`='".$adviser->id."',
            `/*PRIVATE_ROW_SKIPPED*/`='".date('Y-m-d H', $time).":00:00'")->query();
          }
          $result['updated'] = true;
          $result['message'] = 'Расписание обновлено';
        }
      }
    }

    $context->setData( $result );

    return $result;
  }

  protected function week_days($pWeek = 0, $pYear = 0) {
    $array = null;

    if (!$pWeek)
    $lWeek = strftime('%V');
    else
    $lWeek = $pWeek;

    if (!$pYear)
    $lYear = date('Y');
    else
    $lYear = $pYear;

    $lTime = mktime(0, 0, 0, 1, 1, $lYear);

    $lTimesArray = array();

    $lPremierWeek = date('w', mktime(0, 0, 0, 1, 1, $lYear)) - 1;

    $lIfNewYear = 0;
    if ($lPremierWeek < 4)
    $lIfNewYear = 1;


    $lStrTime = date("c", $lTime) . " +" . ($lWeek - $lIfNewYear) . " week -" . $lPremierWeek . " day";
    $lDate = strtotime($lStrTime);
    for ($i = 0; $i < 7; $i++) {
      $lTimesArray[date("w", $lDate)] = $lDate;
      $lDate = strtotime(date("c", $lDate) . " +1 day");
    }

    return $lTimesArray;
  }
}
