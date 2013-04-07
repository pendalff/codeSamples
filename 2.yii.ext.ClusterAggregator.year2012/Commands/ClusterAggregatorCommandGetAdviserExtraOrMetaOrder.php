<?php
/**
 * User: Максим
 * Date: 18.06.12
 * Time: 17:33
 */

class ClusterAggregatorCommandGetAdviserExtraOrMetaOrder extends ClusterAggregatorCommandAbstract
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
   * Return adviser current order data
   * @param ClusterAggregatorContextCommand $context
   * @return array
   * @todo remove test json objects - replace with call to ps40
   */
  public function process( ClusterAggregatorContextCommand $context ){

    $this->_chain->process( $context );

    $adviser = $context->getData();

    $response = array();
    $criteria = new CDbCriteria;
    $criteria->condition = 'PRIVATE_ROW_SKIPPED='.$context->get('order_id').' and  PRIVATE_ROW_SKIPPED = '.$adviser->id;
    $criteria->order = 't.PRIVATE_ROW_SKIPPED DESC';
    //подумать как брать заказы ведь в проекте альбатрос пока нет metaorders
    if(class_exists('ViewOrdersAggregate')){

      foreach(ViewOrdersAggregate::model()->findAll($criteria) as $viewOrder){
        $order = $viewOrder->getOrder();

        if ($order instanceof ExtraOrderModel) {
          $response[] = $this->renderExtraOrderListItem($order);
        }
        else if ($order instanceof PartnerMetaOrdersModel) {
          $response[] = $this->renderMetaOrderListItem($order);
        }
      }
    }
    else{

      foreach(ExtraOrderModel::model()->findAll($criteria) as $order){
        $response[] = $this->renderExtraOrderListItem($order);
      }
    }
    $context->setData( $response );

    return $response;
  }

  /**
   * render meta order
   * @param PartnerMetaOrdersModel $order
   * @return array
   */
  public function renderMetaOrderListItem(PartnerMetaOrdersModel $order)
  {
    $tempArr = array();

    $tempArr['id'] = $order->id;
    $tempArr['price'] = number_format($order->service->amount / 100, 2, ',', '');
    $tempArr['salary'] = number_format($order->service->adviser_rate / 100, 2, ',', '');
    $tempArr['status'] = $order->status;
    $tempArr['created_at'] = date('d.m.Y H:i', strtotime($order->date_created));
    $tempArr['confirmed_at'] = $order->date_confirmed;
    $tempArr['performed_at'] = $order->date_performed;
    $tempArr['type'] = 'meta';
    $tempArr['comment']['id']    = null;
    $tempArr['comment']['text']  = $order->result_note;
    $tempArr['service']['id'] = $order->service->id;
    $tempArr['service']['title'] = $order->service->name;
    $tempArr['user_note_str'] = ($order->order_parameters);
    $tempArr['result_note'] = $order->result_note;
    $tempArr['operator_note'] = $order->operator_note;
/*
    foreach( $tempArr['user'] AS $param ){
      $tempArr['user_note_v'][] = $param['param_name_ru'].':'.$param['param_value'];
    }
*/
    $tempArr['expert'] = array(
      'id' => $order->adviser->id,
      'uid' => $order->adviser->uid,
      'pseudonym' => $order->adviser->pseudonym,
    );

    if (isset($order->user)) {
      $tempArr['user']['id'] = $order->user->id;
      $tempArr['user']['uid'] = $order->user->uid;
      $tempArr['user']['first_name'] = $order->user->firstname;
      $tempArr['user']['last_name'] = $order->user->lastname;
      $tempArr['user']['duplicate'] = $order->user->duplicate;
      $tempArr['user']['phone_number'] = $order->user->phoneNumber;
    }
    return $tempArr;
  }

  /**
   * render extra order
   * @param ExtraOrderModel $order
   * @return array
   */
  public function renderExtraOrderListItem(ExtraOrderModel $order)
  {
    $tempArr = array();

    $tempArr['id'] = $order->id;
    $tempArr['price'] = number_format($order->price / 100, 2, ',', '');
    $tempArr['salary'] = number_format($order->salary / 100, 2, ',', '');
    $tempArr['status'] = $order->status;
    $tempArr['created_at'] = date('d.m.Y H:i', strtotime($order->created_at));
    $tempArr['confirmed_at'] = $order->confirmed_at;
    $tempArr['performed_at'] = $order->performed_at;
    $tempArr['type'] = 'extra';

    $tempArr['service']['id'] = $order->service->id;
    $tempArr['service']['title'] = $order->service->title;

    $tempArr['user']['id'] = $order->user->id;
    $tempArr['user']['uid'] = $order->user->uid;
    $tempArr['user']['first_name'] = $order->user->firstname;
    $tempArr['user']['last_name'] = $order->user->lastname;
    $tempArr['user']['duplicate'] = $order->user->duplicate;
    $tempArr['user']['phone_number'] = $order->user->phoneNumber;
    $tempArr['result_note'] = $order->result_note;
    $tempArr['user_note'] = ($order->user_note);
    $tempArr['operator_note'] = $order->operator_note;
    $tempArr['expert'] = array(
      'id' => $order->adviser->id,
      'uid' => $order->adviser->uid,
      'pseudonym' => $order->adviser->pseudonym,
    );

    if ($order->comment) {
      $tempArr['comment']['id'] = $order->comment->id;
      $tempArr['comment']['text'] = $order->comment->text;
    }

    return $tempArr;
  }
}