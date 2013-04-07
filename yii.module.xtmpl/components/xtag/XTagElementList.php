<?php
/**
 * Object for list template-active element.
 * User: sem
 * Date: 27.03.12
 * Time: 10:09
 */
class XTagElementList extends CComponent implements IXTagElementList
{

  /**
   * @var CMap $items of IXTagElement
   */
  protected $items = null;
  private $nodes = array();

  /**
   * Constructor.
   * @param array $properties
   */
  public function __construct()
  {
    $this->items = new CMap();
  }

  /**
   * @abstract
   * @return IXTagElement
   */
  public function createItem(DOMNode & $node, array $properties)
  {
    return new XTagElement($node, $properties);
  }

  public function addItem(IXTagElement $item)
  {
    if (array_search($item->getNode(), $this->nodes, true) === false) {
      $this->nodes[] = $item->getNode();
      $this->items->add(null, $item);
    }
  }

  /**
   * @return CMap
   */
  public function getItems()
  {
    return $this->items;
  }


  public function renderItems()
  {
    foreach ($this->items AS $item) {
      /** @var XTagElement $item */
      $item->getRender()->run($item);
    }

  }
}
