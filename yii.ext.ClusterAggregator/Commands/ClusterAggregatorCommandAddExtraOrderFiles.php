<?php
/**
 *
 */
class ClusterAggregatorCommandAddExtraOrderFiles extends ClusterAggregatorCommandAbstract
{
  /**
   * @var array allowed ext
   */
  public $allowed = array('.png','.jpg','.gif','.pdf','.doc','.xls','.docx','.xlsx');

  /**
   * @var array
   */
  public $mimeExtentions = array(
    'image/png' => '.png',
    'image/jpeg' => '.jpg',
    'image/gif' => '.gif',
    'application/pdf' => '.pdf',
    'application/msword' => '.doc',
    'application/vnd.ms-excel' => '.xls',
  );

  /**
   * @var string
   */
  public $savePathPrefix = BACKEND_STORAGE_EXTRA_ORDERS_PATH;

  /**
   * @var null
   */
  protected $orderId = null;
  /**
   * @var null
   */
  protected $type    = null;
  /**
   * @var null
   */
  protected $status  = null;

   /**
   * Return adviser current extra and meta orders
   * @param ClusterAggregatorContextCommand $context
   * @return array
   */
  public function process( ClusterAggregatorContextCommand $context ){

    $result = array(
      'files' => array(),
      'count' => 0
    );

    $this->orderId = $context->get('order_id');
    $this->type    = $context->get('type');
    $this->status  = $context->get('status');

    if( !$context->get('is_remote') )
    {
      $result = $this->addLocalFiles( $context );
    }
    else{
      $result = $this->addRemoteFiles( $context );
    }

    $result['count'] = count( $result['files']);

    $context->setData( $result );

    return $result;
  }

  /**
   * @param ClusterAggregatorContextCommand $context
   * @return array
   */
  protected function addLocalFiles( ClusterAggregatorContextCommand $context )
  {
    $result = array(
      'added' => 0,
      'files' => array()
    );
    $uploadedFiles = $this->uploadFiles( $context );
    //save
    if( $uploadedFiles['uploaded'] > 0 )
    {
      //prepare moving
      foreach( $uploadedFiles['files'] AS $key => $file )
      {
        $file['save_path'] = $this->savePathPrefix.$this->orderId.'_'.md5( $file['real_name']. microtime()).'.'.$file['ext'];
        $uploadedFiles['files'][$key] = $file;
      }
      //move
      $context->add('files', $uploadedFiles['files'] );
      $moveCommand = new ClusterAggregatorCommandMoveFiles();
      $moveResult  = $moveCommand->process( $context );
      //save
      if( $moveResult['moved'] > 0 ){
        $addCommand = new ClusterAggregatorCommandAddExtraOrderFile();
        foreach( $moveResult['files'] AS $file ){
          $context->add( 'file_name', $this->getFileName($file['save_path']) );
          $context->add( 'real_name', $file['real_name'] );
          $result['files'][]  = $addCommand->process( $context );
        }
      }
    }

    return $result;
  }

  /**
   * @param ClusterAggregatorContextCommand $context
   * @return array
   */
  protected function addRemoteFiles( ClusterAggregatorContextCommand $context )
  {
    $result = array(
      'added' => 0,
      'files' => array()
    );

    $files = $context->get('filesArr');

    $addCommand = new ClusterAggregatorCommandAddExtraOrderFile();
    $context->add('files', $files );
    foreach( $files AS $file )
    {
      if( $this->saveRemoteFile( $file ) )
      {
        $context->add( 'file_name', ($file['save_name']) );
        $context->add( 'real_name', $file['real_name'] );
        $result['files'][]  = $addCommand->process( $context );
      }
    }

    return $result;
  }

  /**
   * Upload file to temporary directory
   * @param ClusterAggregatorContextCommand $context
   * @return array|null
   */
  public function uploadFiles( ClusterAggregatorContextCommand $context )
  {
    //
    $context->add('allowed_extensions', $this->allowed );
    $uploadCommand = new ClusterAggregatorCommandUploadTemporaryFile();
    return $uploadCommand->process( $context );
  }

  /**
   * @param ClusterAggregatorContextCommand $context
   * @return array|null
   */
  public function prepareRemoteFile( ClusterAggregatorContextCommand $context )
  {
    $this->orderId = $context->get('order_id');

    $uploadedFiles = $this->uploadFiles( $context );

    if( $uploadedFiles['uploaded'] > 0 )
    {
      //prepare moving
      foreach( $uploadedFiles['files'] AS $key => $file )
      {
        $file['save_name'] = $this->orderId.'_'.md5( $file['real_name']. microtime()).'.'.$file['ext'];
        $file['save_path'] = $this->savePathPrefix.$file['save_name'];
        $uploadedFiles['files'][$key] = $file;
      }
      //move
      $context->add('files', $uploadedFiles['files'] );
      $moveCommand = new ClusterAggregatorCommandMoveFiles();
      $moveResult  = $moveCommand->process( $context );
      if( $moveResult['moved'] ){
        //prepare moving
        foreach( $uploadedFiles['files'] AS $key => $file )
        {
          $file['content']   = base64_encode( file_get_contents( $file['save_path'] ) );
          $uploadedFiles['files'][$key] = $file;
        }
      }
      return $uploadedFiles;
    }
    return array();
  }

  /**
   * @param $savePath
   * @return mixed
   */
  protected function getFileName( $savePath ){
    $parts = explode( DIRECTORY_SEPARATOR, $savePath) ;
    return array_pop( $parts );
  }

  /**
   * @param $fileArrElement
   * @return bool
   */
  protected function saveRemoteFile( $fileArrElement )
  {
    $savePath = $this->savePathPrefix.( $fileArrElement['save_name'] ) ;

    return false !== file_put_contents( $savePath, base64_decode( $fileArrElement['content']) );
  }
}