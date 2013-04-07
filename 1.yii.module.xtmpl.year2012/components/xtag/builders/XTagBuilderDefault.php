<?php
/**
 * Interface for builders concrete xtag elements
 * User: sem
 * Date: 27.03.12
 * Time: 9:02
 */
class XTagBuilderDefault extends XTagBuilderBase
{

  protected $paramBuilderName = 'param';

  /**
   * single object process
   * @param array $item
   * @return mixed
   */
  public function build(array $item)
  {
    $type = $item['type'];
    $classname = $item['class'];


    if ($type == $classname && strpos($type, $this->delimiter) !== false) {
      $chunks = explode($this->delimiter, $type);
      $type = array_shift($chunks);
      $classname = implode($this->delimiter, $chunks);
    }

    try {
      $node = $item['node'];
      $content = $item['html'];

      $render = $this->proc->getFactory()->render($type);
      $params = $item['params'];

      $props = array(
        'builder' => $this,
        'render' => $render,
        'params' => $params,
        'class' => $classname,
        'html' => $content
      );

      $list = $this->mapper->getList();
      $list->addItem($list->createItem($node, $props));
    } catch (XTagFactoryException $e) {
      return;
    }
  }

  /**
   * build object params process
   */
  public function buildParams($currentItem)
  {
    $this->mapper->setCurrent($currentItem);
    $paramBuilder = $this->proc->getFactory()->builder($this->paramBuilderName);
    return $paramBuilder->run($this->mapper);
  }

}