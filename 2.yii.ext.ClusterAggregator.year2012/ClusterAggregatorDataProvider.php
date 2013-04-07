<?php
/**
 * Provider, extend of standart yii interface for fetching/sorting/paging datasets
 * User: sem
 * Date: 05.06.12
 * Time: 12:56
 */
class ClusterAggregatorDataProvider extends CArrayDataProvider
{
  /**
   * Setted to true for default noting remove full duplicated dataRows
   * ( duplicates checked as === without aggregator_source )
   * @var bool
   */
  private $duplicatesDeleted = true;

  /**
   * @var null
   */
  public $providerName = null;

  /**
   * Constructor.
   * @param array $rawData the data that is not paginated or sorted. The array elements must use zero-based integer keys.
   * @param array $config configuration (name=>value) to be applied as the initial property values of this class.
   */
  public function __construct( array $rawData,$config=array() )
  {
    if( $this->isAssoc( $rawData) )
    {
      $rawData = array( $rawData );
    }

    $this->rawData=$rawData;
    foreach($config as $key=>$value)
      $this->$key=$value;

    $this->addAggregatorKey();
  }

  /**
   * @static
   * @param $dataArray
   * @param array $config
   * @return ClusterAggregatorDataProvider
   */
  public static function factory( array $dataArray, $config = array() )
  {
    return new self( $dataArray, $config );
  }

  /**
   * @param ClusterAggregatorDataProvider $dataProvider
   */
  public function mergeWith( ClusterAggregatorDataProvider $dataProvider )
  {
    //если провайдера данных не пуст и содержит иные ключи, нежели метка провайдера - мержим
    if(!empty($dataProvider->rawData) && array_keys(current($dataProvider->rawData))!==array(ClusterAggregator::AGGREGATOR_KEY) )
        $this->rawData = array_merge( $this->rawData, $dataProvider->rawData );
  }

  /**
   * @param bool $callRemoveDuplicates
   * @return array
   */
  public function fetchData( $callRemoveDuplicates = true )
  {
    $data = parent::fetchData();
    return $callRemoveDuplicates ? $this->removeDuplicates( $data ) : $data;
  }

  /**
   * Return first single data row
   * @return mixed
   */
  public function getRow()
  {
      return array_shift( $this->getData( true ) );
  }

  /**
   * @param $dataSet
   * @return mixed
   */
  protected  function removeDuplicates( $dataSet )
  {
    if( !$this->duplicatesDeleted ){
      $testArr = array();
      $order = $this->getSort()->getOrderBy() ? $this->getSort()->getOrderBy() : $this->getSort()->defaultOrder;
      $sortedKey = trim(str_ireplace(array('ASC','DESC'),'', $order));
      if(!$sortedKey){
        $sortedKey = $this->keyField;
      }
      foreach( $dataSet AS $key => $dataRow ){

        if(isset($dataRow[ClusterAggregator::AGGREGATOR_KEY]))
          unset($dataRow[ClusterAggregator::AGGREGATOR_KEY]);
        if(isset($dataRow[ $sortedKey ])){

          if(!isset($testArr[ $dataRow[ $sortedKey ] ])){
            $testArr[ $dataRow[ $sortedKey ] ] = $dataRow;
          }
          else{
            if($testArr[ $dataRow[ $sortedKey ] ] === $dataRow){
              $offset = 0;
              if(($pagination=$this->getPagination())!==false)
              {
                $offset = $pagination->getOffset();
              }
              if(isset($this->rawData[ $offset + $key ]))
                unset($this->rawData[ $offset + $key ]);
            }
          }
        }
      }
      $this->duplicatesDeleted = true;
    }

    return $this->fetchData( false );
  }

  /**
   * @param array $data
   * @return bool
   */
  protected function isAssoc( array $data )
  {
    return array_keys($data) !== range(0, count($data) - 1);
  }

  /**
   * Normalize collection - add source value
   */
  protected function addAggregatorKey(){
    $sampleRow = current( $this->rawData );
    reset( $this->rawData );
    if( !isset( $sampleRow[ ClusterAggregator::AGGREGATOR_KEY ]) ){
      foreach( $this->rawData AS $k => $row )
      {
        if(empty($row))
        {
          unset($this->rawData[$k]);
          continue;
        }

        if(array_keys($row)!==array(ClusterAggregator::AGGREGATOR_KEY))
        {
          $row[ ClusterAggregator::AGGREGATOR_KEY ] = $this->providerName;
          $this->rawData[$k] = $row;
        }
      }
    }
  }
  /**
   * Calculates the total number of data items.
   * This method simply returns the number of elements in {@link rawData}.
   * @return integer the total number of data items.
   */
  protected function calculateTotalItemCount()
  {
    $i = 0;
    foreach( $this->rawData AS $row ){
      if( is_array($row) && array_keys($row)!==array(ClusterAggregator::AGGREGATOR_KEY)){
        $i++;
      }
    }
    return $i;
  }
  /**
   * Allow delete duplicates rows
   */
  public function allowDuplicatesDelete(){
   $this->setDuplicatesDeleted(false);
  }

  /**
   * @param boolean $duplicatesDeleted
   */
  public function setDuplicatesDeleted($duplicatesDeleted)
  {
    $this->duplicatesDeleted = $duplicatesDeleted;
  }

}
