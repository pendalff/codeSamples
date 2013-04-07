<?php
class ClusterAggregatorCommandGetExtraOrderFile extends ClusterAggregatorCommandAbstract
{

  /**
   * Return adviser current extra and meta orders
   * @param ClusterAggregatorContextCommand $context
   * @throws CException
   * @return array|null
   */
  public function process( ClusterAggregatorContextCommand $context ){

    $response = array();


    $fileName = $context->get('file_name');

    $attrs = array(
      'file_name' => $fileName
    );

    $orderId  = $context->get('order_id', false);
    if( $orderId ){
      $attrs['order_id'] = $orderId;
    }

    $criteria = new CDbCriteria();
    $criteria->addColumnCondition($attrs);

    $fileModel = ExtraOrderFileModel::model()->find($criteria);

    if(!$fileModel instanceof ExtraOrderFileModel  ){
      return array();
    }

    $response = $fileModel->getAttributes();

    $filePath = BACKEND_STORAGE_EXTRA_ORDERS_PATH . $fileName;

    $response['content'] = '';

    if( file_exists( $filePath ) && $context->get('is_remote')){
      $response['content']  = base64_encode(file_get_contents($filePath));
    }

    $context->setData( $response );

    return $response;
  }

}