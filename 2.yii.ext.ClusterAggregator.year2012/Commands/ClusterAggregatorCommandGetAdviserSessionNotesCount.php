<?php
/**
 * GetAdviserSessionInfo
 * User: sem
 * Date: 08.06.12
 * Time: 14:47
 */
class ClusterAggregatorCommandGetAdviserSessionNotesCount extends ClusterAggregatorCommandAbstract
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

    $query = " SELECT count(PRIVATE_ROW_SKIPPED) as count FROM PRIVATE_ROW_SKIPPED AS s
           LEFT JOIN `PRIVATE_ROW_SKIPPED` AS o ON s.PRIVATE_ROW_SKIPPED = o.id
           LEFT JOIN `PRIVATE_ROW_SKIPPED` AS sn ON s.id = sn.PRIVATE_ROW_SKIPPED
           WHERE s.PRIVATE_ROW_SKIPPED = '" . $adviser->id . "' AND sn.PRIVATE_ROW_SKIPPED IS NULL
           AND IF(s.PRIVATE_ROW_SKIPPED IN ('CN','APP'), o.PRIVATE_ROW_SKIPPED IN ('EXECUTED','EXECUTING'), 1)";

    if( $context->get('date_from', false) && $context->get('date_to', false) )
    {
      $query .= " AND s.PRIVATE_ROW_SKIPPED ".
          "BETWEEN '". $context->get('date_from')."' AND '". $context->get('date_to')."'";
    }

    $data = Yii::app()->db->createCommand( $query )->queryAll();

    $context->setData( $data );

    return $data;
  }


}
