<?php
class ClusterAggregatorCommandMoveFiles extends ClusterAggregatorCommandAbstract
{

   /**
   * Return adviser current extra and meta orders
   * @param ClusterAggregatorContextCommand $context
   * @return array
   */
  public function process( ClusterAggregatorContextCommand $context ){

    $response = array(
      'files' => array(),
      'moved' => 0
    );

    $files  = $context->get('files');
    if( !empty($files) )
    {
      foreach( $files AS $file )
      {
        if( copy( $file['temp_path'], $file['save_path']) )
        {
          unlink( $file['temp_path'] );
          $response['files'][] = $file;
        }
      }
    }

    $response['moved'] = count($response['files']);

    $context->setData( $response );
    
    return $response;
  }

}