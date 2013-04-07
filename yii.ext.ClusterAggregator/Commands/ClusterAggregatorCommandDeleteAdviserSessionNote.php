<?php
/**
 * User: sem
 * Date: 08.06.12
 * Time: 14:47
 */
class ClusterAggregatorCommandDeleteAdviserSessionNote extends ClusterAggregatorCommandAbstract
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
      'deleted' => false,
      'session_uid' => null,
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
    $note     = $noteData['note'];
    $session  = $noteData['session'];

    $data['session_uid'] = $session->session_uid;

     if ( $note ){
      $note = SessionNoteModel::model()->findByPk( $note['id'] );
      if( $note->delete() ) {
        $data['deleted'] = true;
      }
    }

    $context->setData( $data );

    return $data;
  }


}
