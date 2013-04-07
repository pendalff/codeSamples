<?php
/**
 * Get Adviser by uid with remote/local parameter
 * User: sem
 * Date: 08.06.12
 * Time: 14:47
 */
class ClusterAggregatorCommandGetAdviser extends ClusterAggregatorCommandAbstract
{

  /**
   * Return adviser current session data
   * @param ClusterAggregatorContextCommand $context
   * @return ClusterAggregatorDataProvider|array
   * @throws CException
   */
  public function process( ClusterAggregatorContextCommand $context ){

    if( $context->get('is_remote') )
    {
      $adviser = AdviserModel::model()->find('primary_proj_uid=:adviser_primary_uid', array(
        ':adviser_primary_uid' => $context->get('adviser_uid')
      ));
    }
    else
    {
      $adviser = AdviserModel::model()->find('uid=:adviser_uid', array(
        ':adviser_uid' =>  $context->get('adviser_uid')
      ));
    }

    if( !$adviser instanceof AdviserModel )
    {
      if($context->get('only_check',false)){
        return array();
      }
      throw new ClusterAggregatorExceptionAdviserNotFound('Adviser with uid '.$context->get('adviser_uid').' not found!', 2000);
    }

    if($context->get('only_check',false))
    {
      $adviser = $adviser->getAttributes();
    }

    $context->setData( $adviser );

    return $adviser;
  }


}
