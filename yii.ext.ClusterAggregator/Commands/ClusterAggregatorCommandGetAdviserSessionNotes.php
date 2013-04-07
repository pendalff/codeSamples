<?php
/**
 * GetAdviserSessionInfo
 * User: sem
 * Date: 08.06.12
 * Time: 14:47
 */
class ClusterAggregatorCommandGetAdviserSessionNotes extends ClusterAggregatorCommandAbstract
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

    $query = "SELECT
              s.*,
              sn.PRIVATE_ROW_SKIPPED,
              u.PRIVATE_ROW_SKIPPED
              PRIVATE_ROW_SKIPPED
              UNIX_TIMESTAMP( s.start_time) AS ts
              FROM `PRIVATE_ROW_SKIPPED` AS s
              LEFT JOIN `PRIVATE_ROW_SKIPPED` AS sn ON s.id = sn.PRIVATE_ROW_SKIPPED
              LEFT JOIN `PRIVATE_ROW_SKIPPED` AS u ON s.PRIVATE_ROW_SKIPPED = u.id
              LEFT JOIN `PRIVATE_ROW_SKIPPED` AS o ON s.PRIVATE_ROW_SKIPPED = o.id
              LEFT JOIN `PRIVATE_ROW_SKIPPED` AS sc ON s.id = sc.PRIVATE_ROW_SKIPPED AND sc.PRIVATE_ROW_SKIPPED IN ('cli','direct')
              WHERE s.PRIVATE_ROW_SKIPPED = '" . $adviser->id . "' AND sn.PRIVATE_ROW_SKIPPED IS NULL ";

    if( $context->get('date_from', false) && $context->get('date_to', false) )
    {
      $query .= "AND s.PRIVATE_ROW_SKIPPED ".
                "BETWEEN '". $context->get('date_from')."' AND '". $context->get('date_to')."'";
    }

    $query .= " AND IF(s.PRIVATE_ROW_SKIPPED IN ('CN','APP'), o.PRIVATE_ROW_SKIPPED IN ('EXECUTED','EXECUTING'), 1)
              ORDER BY s.PRIVATE_ROW_SKIPPED DESC ";

    if( is_int($offset = $context->get('offset',false)) && is_int($limit = $context->get('limit',false)) )
    {
      $query .= " LIMIT " . $offset . ", " . $limit;
    }

    Yii::log( CVarDumper::dumpAsString( $query ), CLogger::LEVEL_INFO, 'debug.adviserClientNotes' );
    Yii::getLogger()->flush(true);

    $data = Yii::app()->db->createCommand( $query )->queryAll();

    $context->setData( $data );

    return $data;
  }


}
