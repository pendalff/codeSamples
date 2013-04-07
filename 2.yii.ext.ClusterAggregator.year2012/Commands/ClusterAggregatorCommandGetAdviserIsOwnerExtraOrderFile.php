<?php
class ClusterAggregatorCommandGetAdviserIsOwnerExtraOrderFile extends ClusterAggregatorCommandAbstract
{

  public function __construct( )
  {
    $chain = new ClusterAggregatorCommandChain();
    $chain->appendCommand( new ClusterAggregatorCommandGetAdviser() );
    $this->_chain = $chain;
  }

  /**
   * Return adviser is owner for file
   * @param ClusterAggregatorContextCommand $context
   * @return array
   * @todo remove test json objects - replace with call to ps40
   */
  public function process( ClusterAggregatorContextCommand $context ){

    $this->_chain->process( $context );

    $adviser = $context->getData();


    $response = array(
      'isOwner' => false
    );

    $fileName = $context->get('file_name');

    $query="SELECT
              COUNT(PRIVATE_ROW_SKIPPED) as count
            FROM PRIVATE_ROW_SKIPPED as eof
            INNER JOIN PRIVATE_ROW_SKIPPED as eo ON eof.PRIVATE_ROW_SKIPPED = eo.PRIVATE_ROW_SKIPPED
            WHERE
              eo.PRIVATE_ROW_SKIPPED = '".$adviser->id."'
              AND
              PRIVATE_ROW_SKIPPED = '".$fileName."'
              AND
              eof.PRIVATE_ROW_SKIPPED = 'active'";

    $data = Yii::app()->db->createCommand( $query )->queryScalar();
    $context->setData( $response );

    return $response;
  }

}