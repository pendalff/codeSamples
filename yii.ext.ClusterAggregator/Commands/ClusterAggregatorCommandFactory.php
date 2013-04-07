<?php
/**
 * Factory of commands
 * User: sem
 * Date: 20.06.12
 * Time: 11:48
 */
class ClusterAggregatorCommandFactory
{
  private static $_prefix = 'ClusterAggregatorCommand';

  /**
   * @static
   * @param $name
   * @return mixed
   * @throws ClusterAggregatorException
   */
  public static function factory( $name )
  {
    $className = self::$_prefix . ucfirst($name);
    if( class_exists( $className ) ){
       return new $className();
    }
    else{
       throw new ClusterAggregatorException('Command with alias '.$name.' not found, class '.$className.' not available!',1000);
    }
  }

}
