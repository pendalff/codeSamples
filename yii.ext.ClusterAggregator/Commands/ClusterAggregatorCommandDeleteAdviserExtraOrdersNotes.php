<?php
/**
 * User: sem
 * Date: 08.06.12
 * Time: 14:47
 */
class ClusterAggregatorCommandDeleteAdviserExtraOrdersNotes extends ClusterAggregatorCommandAbstract
{
  /**
   * @var null|ClusterAggregatorCommandChain
   */
  private $_chain = null;

  public function __construct( )
  {
    $chain = new ClusterAggregatorCommandChain();
    $chain->appendCommand( new ClusterAggregatorCommandGetAdviserExtraOrdersNote() );
    $this->_chain = $chain;
  }

  /**
   * Return adviser current session data
   * @param ClusterAggregatorContextCommand $context
   * @return array
   * @todo remove test json objects - replace with call to ps40
   */
  public function process( ClusterAggregatorContextCommand $context )
  {
    $data = array(
      'deleted' => false,
      'order_title' => null,
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
    }

    $noteData = $context->getData();
    $adviser  = $noteData['adviser'];
    $order    = $noteData['order'];
    $note     = $noteData['note'];

    $data['note']    = $note;

    $data['order_title'] = $order->service->title;

     if ( $note ){
      $note = ExtraOrderNoteModel::model()->findByPk( $note['id'] );
      if( $note && $note->delete() ) {
        $data['deleted'] = true;
      }
    }

    $context->setData( $data );

    return $data;
  }


}
