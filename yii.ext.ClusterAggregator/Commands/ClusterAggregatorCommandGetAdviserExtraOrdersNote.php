<?php
/**
 *
 */
class ClusterAggregatorCommandGetAdviserExtraOrdersNote  extends ClusterAggregatorCommandAbstract
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

    $extraOrder = ExtraOrderModel::model()->findByPk( $context->get('order_id') );

    if( !$extraOrder instanceof ExtraOrderModel )
    {
      throw new CException('Extra Order with id '.$context->get('order_id').' not found!', 2000);
    }

    $data = array();

    $extraOrderNote = ExtraOrderNoteModel::model()->findByAttributes(
      array(
        'adviser_id' => $adviser->id,
        'order_id'   => $extraOrder->id,
      )
    );

    $data['note']    = $extraOrderNote ? $extraOrderNote->getAttributes() : array();
    $data['order']   = $extraOrder;
    $data['adviser'] = $adviser;

    $context->setData( $data );

    return $data;
  }

}