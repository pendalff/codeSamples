<?php
/**
 * Interface for objects with access to processor
 * User: sem
 * Date: 27.03.12
 * Time: 9:02
 */
interface IXTagProcessorAccessible
{
  public function __construct( IXTagProcessor & $proc );
}