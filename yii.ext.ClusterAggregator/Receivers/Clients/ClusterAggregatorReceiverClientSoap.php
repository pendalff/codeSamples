<?php
/**
 *
 * @author: sem
 * Date: 05.06.12
 * Time: 1:03
 */
class ClusterAggregatorReceiverClientSoap extends SoapClient
{
    public function __construct( $dsn, $params = null ){

        parent::SoapClient( $dsn, $params );
    }
}
