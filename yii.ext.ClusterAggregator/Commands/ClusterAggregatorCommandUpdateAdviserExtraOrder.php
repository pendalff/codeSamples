<?php
/**
 *
 * User: sem
 * Date: 08.06.12
 * Time: 14:47
 */
class ClusterAggregatorCommandUpdateAdviserExtraOrder extends ClusterAggregatorCommandAbstract
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
  public function process( ClusterAggregatorContextCommand $context )
  {

    $data = array(
      'updated' => false,
      'message' => null,
    );


    try{
      $this->_chain->process( $context );
    }
    catch( CException $e ){
      if( $e->getCode() == 2000 ){
        $data['error'] = $e->getMessage();
        $context->setData( $data );
        return $data;
      }
      else{
        throw $e;
      }
    }

    $adviser = $context->getData();

    $user = UserModel::model()->findByUid( $context->get('user_uid') );

    if( !$user instanceof UserModel )
    {
      throw new CException('User with uid '.$context->get('user_uid').' not found!', 2000);
    }

    $order = ExtraOrderModel::model()->findByPk( $context->get('order_id'));

    if( !$order instanceof ExtraOrderModel )
    {
      throw new CException('ExtraOrder with id '.$context->get('order_id').' not found!', 2000);
    }

    $status  = $context->get('status');

    $comment = $context->get('comment');

    if($status == 'confirmed' || $status == 'declined') {
      $order->status = $status;
      $order->result_note = htmlspecialchars( $comment );
      $order->confirmed_at = new CDbExpression('NOW()');
      if($order->save()){
        ExtraOrderHistoryModel::createHistoryRecord(
          $order->id,
          Yii::t('extra_orders', 'Status has been changed'),
          NULL,
          $status);
        $data['updated'] = true;
        $data['message'] = $status == 'confirmed' ? ('You have confirmed order successfully'):('You have declined order successfully');
        if($status  == 'confirmed') {
          $this->sendUserConfirm( $adviser, $user, $order );
        }
      }
    }

    if($status == 'performed') {
      $order->status = $status;
      $order->result_note = htmlspecialchars( $comment );
      $order->performed_at = new CDbExpression('NOW()');

      if($order->save()){
        ExtraOrderHistoryModel::createHistoryRecord(
          $order->id,
          Yii::t('extra_orders', 'Status has been changed'),
          NULL,
          $status);
        $data['updated'] = true;
        $data['message'] = 'You have performed order successfully';
        $this->sendPerform( $adviser, $user, $order, $comment );
      }
    }

    if( empty($status) ){
      $order->result_note = htmlspecialchars( $comment );
      if( $order->save() ){
        $data['updated'] = true;
        $data['message'] = 'You have saved order successfully';
      }
    }

    $context->setData( $data );

    return $data;
  }

  /**
   * Send 24 schedule for user
   * @param AdviserModel $adviser
   * @param UserModel $user
   * @param ExtraOrderModel $order
   */
  protected function sendUserConfirm( AdviserModel $adviser, UserModel $user, ExtraOrderModel $order ){
    $tags = array();
    $tags['PRIVATE_ROW_SKIPPED']  = $user->sex == 'Male' ? 'ый':'ая';
    Yii::app()->InfoEvent->sendEmail( $user->email, 24, $tags, null, false, 'normal', false, $adviser->id );
  }

  /**
   * Send 28 schedule for support
   * @param AdviserModel $adviser
   * @param UserModel $user
   * @param ExtraOrderModel $order
   * @param $comment
   * @return void
   */
  protected function sendPerform( AdviserModel $adviser, UserModel $user, ExtraOrderModel $order, $comment ){
    $tags = array();

    $user = $user->getAttributes();
    $adviser = $adviser->getAttributes();

    $service = $order->service;
    $service = $service->getAttributes();

    $order   = $order->getAttributes();

    $tags['ID'] = $order['id'];
    $tags['PRIVATE_ROW_SKIPPED']        = $user['firstname'];
    /*PRIVATE_ROW_SKIPPED*/

    $command = new ClusterAggregatorCommandGetExtraOrderFilesList();

    $contextUser = new ClusterAggregatorContextCommand(
      array(
        'order_id'      => $order['id'],
        'type'          => 'user',
        'status'        => 'active',
        'return_count'  => true
      )
    );
    $count = $command->process( $contextUser );
    unset($contextUser);
    $tags['/*PRIVATE_ROW_SKIPPED*/']           = $count['count'];

    $contextResult = new ClusterAggregatorContextCommand(
      array(
        'order_id' => $order['id'],
        'type'     => 'user',
        'status'   => 'active',
        'return_count'  => true
      )
    );
    $count = $command->process( $contextResult );
    unset($contextUser);
    $tags['/*PRIVATE_ROW_SKIPPED*/']           = $count['count'];

    $tags['/*PRIVATE_ROW_SKIPPED*/']       = number_format($service['salary']/100, 0,',','').' руб.';
    $tags['/*PRIVATE_ROW_SKIPPED*/']   = $service['annotation'];
    $tags['/*PRIVATE_ROW_SKIPPED*/']  = $service['description'];
    $tags['/*PRIVATE_ROW_SKIPPED*/'] = $service['requirements'];

    Yii::app()->InfoEvent->sendEmail( ConfigModel::getVal('support_email'), 28, $tags );
  }
}
