<?php
/**
 * Interface for renders
 * User: sem
 * Date: 27.03.12
 * Time: 9:02
 */
interface IXTagRender extends IXTagProcessorAccessible
{
  /**
   * Render element
   * @abstract
   * @param IXTagElement|XTagElement $target
   */
  public function run(IXTagElement $target);
}