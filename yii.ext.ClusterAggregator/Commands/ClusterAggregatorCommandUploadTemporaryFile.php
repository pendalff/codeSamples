<?php
class ClusterAggregatorCommandUploadTemporaryFile extends ClusterAggregatorCommandAbstract
{

   /**
   * Return adviser current extra and meta orders
   * @param ClusterAggregatorContextCommand $context
   * @return array
   */
  public function process( ClusterAggregatorContextCommand $context ){

    $response = array(
      'files' => array(),
      'uploaded' => 0
    );

    /** @var $files CUploadedFile[] */
    $files  = CUploadedFile::getInstancesByName($context->get('file_fields'));
    if( !empty($files) )
    {
      foreach( $files AS $file ){
        if(!$file->getError() && $this->allowedExtension( $context, $file ))
        {
          $savePath = $this->getSavePath( $file );
          if( $file->saveAs($savePath) )
          {
              $response['files'][] = array(
                'temp_path' => $savePath,
                'real_name' => $file->getName(),

                'ext'      => $file->getExtensionName(),
                'type'     => $file->getType(),
                'size'     => $file->getSize()
              );
          }
        }
      }
    }

    $response['uploaded'] = count($response['files']);

    $context->setData( $response );

    return $response;
  }

  /**
   * Temporary path for file
   * @param CUploadedFile $file
   * @return string
   */
  protected function getSavePath( CUploadedFile $file )
  {
    $ext = $file->getExtensionName() ? '.'.$file->getExtensionName() : '';

    return BACKEND_STORAGE_PATH.DIRECTORY_SEPARATOR. $file->getName() . '_' . md5( $file->getName(). microtime()).$ext;
  }

  /**
   * Check .ext allow
   * @param ClusterAggregatorContextCommand $context
   * @param CUploadedFile $file\
   */
  protected function allowedExtension( ClusterAggregatorContextCommand $context, CUploadedFile $file )
  {
      return   count($context->get('allowed_extensions')) > 0 ?
                          in_array( '.'.$file->getExtensionName(), $context->get('allowed_extensions')) : true;
  }
}