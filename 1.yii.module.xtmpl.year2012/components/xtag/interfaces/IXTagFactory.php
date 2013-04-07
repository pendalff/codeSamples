<?php
/**
 * Interface for Factories objects
 * User: sem
 * Date: 27.03.12
 * Time: 9:02
 */
interface IXTagFactory extends IXTagProcessorAccessibleSingletone
{
  /**
   *
   * @abstract
   * @param null|string|IXTagBuilder $builderName
   * @return IXTagBuilder
   */
  public function builder($builderName = null);

  /**
   *
   * @abstract
   * @param null|string|IXTagParser $parserName
   * @return IXTagParser
   */
  public function parser($parserName = null);

  /**
   *
   * @abstract
   * @param null|string|IXTagRender $renderName
   * @return IXTagRender
   */
  public function render($renderName = null);

}