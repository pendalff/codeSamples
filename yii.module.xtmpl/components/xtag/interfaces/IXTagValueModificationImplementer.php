<?php
/**
 * Interface for value modification class
 * User: sem
 * Date: 27.03.12
 * Time: 9:02
 */
interface IXTagValueModificationImplementer extends IBehavior
{
  public function addModifersToList(array $modifers);
}