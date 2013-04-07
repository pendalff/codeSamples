<?php
/**
 * Interface for value modificator plugins
 * User: sem
 * Date: 27.03.12
 * Time: 9:02
 */
interface IXTagValueModificator
{
  public function __construct( XTagBuilderBehavior $modiferApplyObject );
}