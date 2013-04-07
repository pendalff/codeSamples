<?php
class ClusterAggregatorCommandDeleteExtraOrderFile extends ClusterAggregatorCommandAbstract
{

   /**
   * Return adviser current extra and meta orders
   * @param ClusterAggregatorContextCommand $context
   * @return array
   */
  public function process( ClusterAggregatorContextCommand $context ){

    $response = array(
      'deleted' => false
    );

    $orderId  = $context->get('order_id');
    $fileName = $context->get('file_name');

    $criteria = new CDbCriteria();
    $criteria->addColumnCondition( array(
      'order_id' => $orderId,
      'file_name'=> $fileName
    ));

    $fileModel = ExtraOrderFileModel::model()->find($criteria);

    if(!$fileModel instanceof ExtraOrderFileModel  ){
      throw new CException('ExtraOrderFile with id '.$context->get('order_id').' and file_name '.$fileName.' not found!', 2000);
    }



    $filePath = BACKEND_STORAGE_EXTRA_ORDERS_PATH.$fileName;
    
    if( file_exists( $filePath ) ){
      if( @unlink($filePath) ){
        $response['deleted'] = $fileModel->delete();
      }
    }

    $context->setData( $response );

    return $response;
  }

}