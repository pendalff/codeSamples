<?php
/**
 * build element params value view type
 * (array data to method render controller)
 * User: sem
 * Date: 27.03.12
 * Time: 9:02
 */
class XTagBuilderParamValueView extends XTagBuilderBase implements IXTagBuilder
{
  /**
   * Build element
   * @param  array $currentItem
   * @return void
   */
  public function build( array $currentItem )
  {
    $default = isset( $currentItem['default'] ) ? $currentItem['default'] : null;

    if( !isset( $currentItem['val'] ) ){
      return $default;
    }
    return $this->find( $currentItem['val'], $default );
  }

  public function find( $path, $default )
  {
    $context = $this->proc->getData();

    return Arr::path( $context, $path, $default, $this->delimiter );
  }

}