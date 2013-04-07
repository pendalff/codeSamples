<?php
/**
 * GetAdviserSessionInfo
 * User: sem
 * Date: 08.06.12
 * Time: 19:11
 */
class ClusterAggregatorCommandGetAdviserExtraOrders extends ClusterAggregatorCommandAbstract
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
   * Return adviser current extra and meta orders
   * @param ClusterAggregatorContextCommand $context
   * @return array
   */
  public function process( ClusterAggregatorContextCommand $context ){

    $this->_chain->process( $context );

    $adviser = $context->getData();

    $response = array();
    $criteria = new CDbCriteria;
    $criteria->condition = 'PRIVATE_ROW_SKIPPED'.$adviser->id;
    $criteria->order = 'PRIVATE_ROW_SKIPPED';

    if( ($context->get('count',false)===true))
    {
      if(class_exists('ViewOrdersAggregate')){
        return array('cnt'=> ViewOrdersAggregate::model()->count($criteria));
      }
      else{
        return array('cnt'=> ExtraOrderModel::model()->count($criteria));
      }
    }

    if( is_int($offset = $context->get('offset',false)) && is_int($limit = $context->get('limit',false)) )
    {
      $criteria->limit = $limit;
      $criteria->offset = $offset;
    }

    //подумать как брать заказы ведь в проекте альбатрос пока нет metaorders
    if(class_exists('ViewOrdersAggregate')){

      foreach(ViewOrdersAggregate::model()->findAll($criteria) as $viewOrder){
        $order = $viewOrder->getOrder();

        if ($order instanceof ExtraOrderModel) {
          $response[] = $this->renderExtraOrderListItem($order,$context);
        }
        else if ($order instanceof PartnerMetaOrdersModel) {
          $response[] = $this->renderMetaOrderListItem($order,$context);
        }
      }
    }
    else{

      foreach(ExtraOrderModel::model()->findAll($criteria) as $order){
          $response[] = $this->renderExtraOrderListItem($order,$context);
        }
    }
    $context->setData( $response );

    return $response;
  }


  /**
   * render meta order
   * @param PartnerMetaOrdersModel $order
   * @param ClusterAggregatorContextCommand $context
   * @return array
   */
  public function renderMetaOrderListItem(PartnerMetaOrdersModel $order,ClusterAggregatorContextCommand $context)
  {
    $tempArr = array();
    if($context->get('count',false)===true){
      $tempArr['count'] = $order->cnt;
      return $tempArr;
    }
    $tempArr['id'] = $order->id;
    $tempArr['price'] = number_format($order->service->amount / 100, 2, ',', '');
    $tempArr['salary'] = number_format($order->service->adviser_rate / 100, 2, ',', '');
    $tempArr['status'] = $order->status;
    $tempArr['ts']  = $time = strtotime($order->date_created);
    $tempArr['created_at'] = date('d.m.Y H:i', $time);
    $tempArr['type'] = 'meta';

    $tempArr['service']['id'] = $order->service->id;
    $tempArr['service']['title'] = $order->service->name;

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
    }
    return $tempArr;
  }

  /**
   * render extra order
   * @param ExtraOrderModel $order
   * @return array
   */
  public function renderExtraOrderListItem(ExtraOrderModel $order,ClusterAggregatorContextCommand $context)
  {
    $tempArr = array();

    $tempArr['id'] = $order->id;
    $tempArr['price'] = number_format($order->price / 100, 2, ',', '');
    $tempArr['salary'] = number_format($order->salary / 100, 2, ',', '');
    $tempArr['status'] = $order->status;
    $tempArr['ts']  = $time = strtotime($order->created_at);
    $tempArr['created_at'] = date('d.m.Y H:i', $time);
    $tempArr['type'] = 'extra';

    $tempArr['service']['id'] = $order->service->id;
    $tempArr['service']['title'] = $order->service->title;

    $tempArr['user']['id'] = $order->user->id;
    $tempArr['user']['uid'] = $order->user->uid;
    $tempArr['user']['first_name'] = $order->user->firstname;
    $tempArr['user']['last_name'] = $order->user->lastname;
    $tempArr['user']['duplicate'] = $order->user->duplicate;

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