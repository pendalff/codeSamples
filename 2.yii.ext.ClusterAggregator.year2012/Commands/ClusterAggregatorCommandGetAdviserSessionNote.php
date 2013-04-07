<?php
/**
 * GetAdviserSessionNote
 * User: sem
 * Date: 08.06.12
 * Time: 14:47
 */
class ClusterAggregatorCommandGetAdviserSessionNote extends ClusterAggregatorCommandAbstract
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
   * Return adviser current session note data
   * @param ClusterAggregatorContextCommand $context
   * @throws CException
   * @return array|null
   */
  public function process( ClusterAggregatorContextCommand $context ){

    $this->_chain->process( $context );

    $adviser = $context->getData();

    $session = SessionModel::model()->findByAttributes(array(
      'adviser_id' => $adviser->id,
      'id' => $context->get('session_id')
    ));

    if (!$session) {
      throw new CException('Session for adviser uid'.$context->get('adviser_uid').' with id '.$context->get('session_id').' not found!', 2000);
    }

    $criteria = new CDbCriteria();
    $criteria->addColumnCondition(
      array(
        'session_id' => $session->id
      )
    );
    $note = SessionNoteModel::model()->find( $criteria);

    $data = array(
      'note'    => $note ? $note->getAttributes() : array(),
      'session' => $session,
      'adviser' => $adviser,
    );

    $context->setData( $data );

    return $data;
  }


}
