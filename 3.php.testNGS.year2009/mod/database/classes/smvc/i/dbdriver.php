<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
interface SMVC_I_dbdriver{
    /**
     * connect
     */
	public function connect();

    /**
     * hasConnection
     * @return boolean
     */
    public function has_connection();

   /**
     * getResource
     * @return resource
     */
    public function get_stmt();
 
    /**
     * close
     */   	
	public function disconnect();


    /**
     * hasError
     * @return integer
     */
    public function has_error();

    /**
     * get_error
     * @return int
     */
    public function get_error();

    /**
     * query
     * @param string $sql
     */
	public function query( $sql );

    /**
     * getNumRows
     * @return int
     */
	public function getNumRows();

    /**
     * get one row. 
     * @param boolean $as_object
     * @return stdClass | array (optional)
     */
	public function getRow();
    

    /**
     * Return data set as array|object (optional, default - as array)
     * getRows
     * @param boolean $as_object
     * @return array
     */
	public function getRows();
}
?>