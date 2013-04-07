<?php
/**
 * Interface for parsers
 * User: sem
 * Date: 27.03.12
 * Time: 9:02
 */
interface IXTagParser extends IXTagProcessorAccessible
{

  /**
   * Parse document
   * @abstract
   * @param  IXTagContext $mapper
   * @return IXTagContext $mapper
   */
  public function run(IXTagContext $mapper);

  /**
   * @abstract
   * @param CEvent $event
   */
  public function onProcessParse(CEvent $event);

}