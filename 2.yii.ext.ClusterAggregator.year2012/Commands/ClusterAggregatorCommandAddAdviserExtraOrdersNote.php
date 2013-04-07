<?php
/**
 * GetAdviserSessionInfo
 * User: sem
 * Date: 08.06.12
 * Time: 14:47
 */
class ClusterAggregatorCommandAddAdviserExtraOrdersNote extends ClusterAggregatorCommandAbstract
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
      'created' => false,
      'updated' => false,
      'order_title' => null,
      'note'    => array()
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
    if (!count($note)) {
      $note = new ExtraOrderNoteModel();
      $note->setAttributes(array(
        'adviser_id'=> $adviser->id,
        'order_id'=> $order->id,
        'operator_id' => null,
        'text'=> $context->get('comment'),
        'created_at'=> Yii::app()->getDateFormatter()->format('yyyy-MM-dd HH:mm:ss', $context->get('created_at',false) ? strtotime($context->get('created_at')) : time() )
      ), false);

      if( $note->validate() && $note->save() ) {
        $data['created'] = true;
      }
    } else {
      $note = ExtraOrderNoteModel::model()->findByPk( $note['id'] );
      $note->text = $context->get('comment');

      if( $note->save() ) {
        $data['updated'] = true;
      }
    }

    $context->setData( $data );

    return $data;
  }


}
