<?php
/**
 * Interface list of element objects
 * User: sem
 * Date: 27.03.12
 * Time: 10:09
 */
interface IXTagElementList
{
  /**
   * @abstract
   * @return \IXTagElement|\XTagElementList
   */
   public function createItem( DOMNode & $node, array $properties );

  /**
   * @abstract
   * @param \IXTagElement|\XTagElementList $item
   */
   public function addItem( IXTagElement $item );

  /**
   * @abstract
   * @return \CMap
   */
   public function getItems();
}
