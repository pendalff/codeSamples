<?php
/**
 * notes for user
 * User: sem
 * Date: 08.06.12
 * Time: 14:47
 */
class ClusterAggregatorCommandGetAdviserClientNotes extends ClusterAggregatorCommandAbstract
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
   * @todo remove test json objects - replace with call to ps40
   */
  public function process( ClusterAggregatorContextCommand $context ){

    $this->_chain->process( $context );

    $adviser = $context->getData();

    $user = UserModel::model()->findByUid( $context->get('user_uid') );

    if( !$user instanceof UserModel )
    {
      throw new CException('User with uid '.$context->get('user_uid').' not found!', 2000);
    }

    $query = "PRIVATE_ROW_SKIPPED";

    if( $context->get('phone_number', false) )
    {
      $query.="PRIVATE_ROW_SKIPPED";
    }

    $query .= "PRIVATE_ROW_SKIPPED";

    $data = Yii::app()->db->createCommand( $query )->queryAll();

    $context->setData( $data );

    return $data;
  }


}
