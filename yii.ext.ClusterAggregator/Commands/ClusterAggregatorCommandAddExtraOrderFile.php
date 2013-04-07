<?php
class ClusterAggregatorCommandAddExtraOrderFile extends ClusterAggregatorCommandAbstract
{

   /**
   * Return adviser current extra and meta orders
   * @param ClusterAggregatorContextCommand $context
   * @return array
   */
  public function process( ClusterAggregatorContextCommand $context ){

    $response = array();

    $orderId  = $context->get('order_id');
    $fileName = $context->get('file_name');
    $realName = $context->get('real_name');
    $type     = $context->get('type');
    $status   = $context->get('status');


    $criteria = new CDbCriteria();
    $criteria->addColumnCondition( array(
      'order_id' => $orderId,
      'file_name'=> $fileName
    ));

    $fileModel = ExtraOrderFileModel::model()->find($criteria);

    if( !$fileModel instanceof ExtraOrderFileModel )
    {
      $fileModel = new ExtraOrderFileModel();
    }

    $fileModel->order_id  = $orderId;
    $fileModel->date      = new CDbExpression('NOW()');
    $fileModel->type      = $type;
    $fileModel->real_name = $realName;
    $fileModel->file_name = $fileName;
    $fileModel->status    = $status;

    $response = array( 'file'=> array(), 'saved'=>false);

    if( $fileModel->save() ){
      $response['saved'] = true;
      $response['file']  = $fileModel->getAttributes();
    }

    $context->setData( $response );

    return $response;
  }

}