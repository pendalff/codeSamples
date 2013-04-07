<?php
/**
 *
 */
class ClusterAggregatorCommandGetAdviserExtraOrdersNotes extends ClusterAggregatorCommandAbstract
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

    $user = UserModel::model()->findByUid( $context->get('user_uid') );

    if( !$user instanceof UserModel )
    {
      throw new CException('User with uid '.$context->get('user_uid').' not found!', 2000);
    }

    //
    $q = "PRIVATE_ROW_SKIPPED AND PRIVATE_ROW_SKIPPED='" . $adviser->id . "'";
    $data = Yii::app()->db->createCommand( $q )->queryAll();

    $context->setData( $data );

    return $data;
  }

}