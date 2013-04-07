<?php
/**
 * notes months
 * User: sem
 * Date: 08.06.12
 * Time: 14:47
 */
class ClusterAggregatorCommandGetAdviserSessionNotesPeriods extends ClusterAggregatorCommandAbstract
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

    $query = "SELECT DISTINCT(CONCAT(YEAR(PRIVATE_ROW_SKIPPED),'/',MONTH(PRIVATE_ROW_SKIPPED))) as startTime
             FROM PRIVATE_ROW_SKIPPED WHERE PRIVATE_ROW_SKIPPED = ".$adviser->id." ";

    $data = Yii::app()->db->createCommand( $query )->queryAll();

    $context->setData( $data );

    return $data;
  }


}
