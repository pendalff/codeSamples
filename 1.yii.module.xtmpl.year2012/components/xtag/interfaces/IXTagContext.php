<?php
/**
 * Interface context (transfer object) for parsers and builders
 * User: sem
 * Date: 27.03.12
 * Time: 10:09
 */
interface IXTagContext
{
  /**
   * @abstract
   * @return IXTagElementList
   */
  public function getList();

  /**
   * @abstract
   * @return array current processed item
   */
  public function getCurrent();
  /**
   * @abstract
   * @param array $item
   */
  public function setCurrent( array $item );
  /**
   * @abstract
   * @param $builder
   */
  public function setBuilder($builder);
  /**
   * @abstract
   *
   */
  public function getBuilder();
  /**
   * @abstract
   * @param $parser
   */
  public function setParser($parser);
  /**
   * @abstract
   * @return
   */
  public function getParser();
  /**
   * @abstract
   * @param CMap $builderContext
   */
  public function setBuilderContext( CMap $builderContext );
  /**
   * @abstract
   * @return CMap
   */
  public function getBuilderContext();
  /**
   * @abstract
   * @param CMap $parserContext
   */
  public function setParserContext( CMap $parserContext );
  /**
   * @abstract
   * @return CMap
   */
  public function getParserContext();

}
