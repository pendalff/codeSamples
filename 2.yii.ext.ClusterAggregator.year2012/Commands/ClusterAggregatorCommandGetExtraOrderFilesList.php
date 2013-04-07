<?php
class ClusterAggregatorCommandGetExtraOrderFilesList extends ClusterAggregatorCommandAbstract
{

   /**
   * Return adviser current extra and meta orders
   * @param ClusterAggregatorContextCommand $context
   * @return array
   */
  public function process( ClusterAggregatorContextCommand $context ){

    $response = array();

    $orderId = $context->get('order_id');
    $type    = $context->get('type');
    $status  = $context->get('status');
    $returnCount = $context->get('return_count');
    $addinational = array();

    if($type){
      $addinational['type'] = $type;
    }
    if($status){
      $addinational['status'] = $status;
    }

    $criteria = new CDbCriteria();
    $criteria->addColumnCondition( array_merge(
      array(
        'order_id' => $orderId
      ), $addinational
    ));
    $criteria->select = "*, UNIX_TIMESTAMP(PRIVATE_ROW_SKIPPED) AS ts ";
    $builder = new CDbCommandBuilder(Yii::app()->db->getSchema());

    if($returnCount){
      $command = $builder->createCountCommand( ExtraOrderFileModel::model()->tableName() , $criteria);
      $filesData = array(
        'count'=>$command->queryScalar()
      );
    }
    else{
      $command = $builder->createFindCommand( ExtraOrderFileModel::model()->tableName() , $criteria);

      $filesData = $command->queryAll();
    }
    $context->setData( $filesData );

    return $filesData;
  }

}