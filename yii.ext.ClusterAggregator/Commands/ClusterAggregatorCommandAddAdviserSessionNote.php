<?php
/**
 * GetAdviserSessionInfo
 * User: sem
 * Date: 08.06.12
 * Time: 14:47
 */
class ClusterAggregatorCommandAddAdviserSessionNote extends ClusterAggregatorCommandAbstract
{
  /**
   * @var null|ClusterAggregatorCommandChain
   */
  private $_chain = null;

  public function __construct( )
  {
    $chain = new ClusterAggregatorCommandChain();
    $chain->appendCommand( new ClusterAggregatorCommandGetAdviserSessionNote() );
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
      'session_uid' => null,
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
    $session  = $noteData['session'];
    $note     = $noteData['note'];

    $data['note']    = $note;

    $data['session_uid'] = $session->session_uid;
    if (!count($note)) {
      $note = new SessionNoteModel();
      $note->setAttributes(array(
        'adviser_id'=> $adviser->id,
        'session_id'=> $session->id,
        'operator_id' => null,
        'text'=> $context->get('comment'),
        'created_at'=> Yii::app()->getDateFormatter()->format('yyyy-MM-dd HH:mm:ss', $context->get('created_at',false) ? strtotime($context->get('created_at')) : time() )
      ), false);

      if( $note->validate() && $note->save() ) {
        $data['created'] = true;
      }
    } else {
      $note = SessionNoteModel::model()->findByPk( $note['id'] );
      if( $note->operator_id == null ){
        $note->text = $context->get('comment');
        $note->adviser_id = $adviser->id;
        if( $note->save() ) {
          $data['updated'] = true;
        }
      }
    }

    $context->setData( $data );

    return $data;
  }


}
