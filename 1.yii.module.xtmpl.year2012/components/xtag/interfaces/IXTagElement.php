<?php
/**
 * Interface objects for single template-active element.
 * User: sem
 * Date: 27.03.12
 * Time: 10:09
 */
interface IXTagElement
{
  /**
   * Constructor.
   * @param DOMNode $node
   * @param array $properties
   */
  public function __construct( DOMNode & $node, array $properties );

  /**
   * @return null|string
   */
  public function getClass();

  /**
   * @return string|null  current html content
   */
  public function getHtml();

  /**
   * @return \DOMNode element node in DOMDocument
   */
  public function getNode();

  /**
   * @return null|array element params
   */
  public function getParams();

  /**
   * Get render for here
   * @abstract
   * @return IXTagRender|XTagRenderBase
   */
  public function getRender();
  /**
   * set render for here
   * @abstract
   * @param IXTagRender $render
   */
  public function setRender( IXTagRender & $render );

}
