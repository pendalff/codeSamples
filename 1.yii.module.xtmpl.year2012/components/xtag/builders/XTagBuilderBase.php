<?php
/**
 * base class for builders concrete xtag elements
 * User: sem
 * Date: 27.03.12
 * Time: 9:02
 */
class XTagBuilderBase extends CComponent implements IXTagBuilder
{
  /**
   * @var IXTagProcessor $proc
   */
  protected $proc = null;

  /**
   * @var IXTagContext $mapper
   */
  protected $mapper = null;

  protected $delimiter = null;

  public $defaultDelimiter = '.';

  /**
   * @param IXTagProcessor $proc
   */
  public function __construct(IXTagProcessor & $proc)
  {
    $this->proc = $proc;
    $this->delimiter = $this->defaultDelimiter;
  }

  public function onCurrent(CEvent $event)
  {
    if ($event->params instanceof IXTagContext) {
      $this->run($event->params);
    }
  }

  /**
   * Build one IXTagElement element with concrete render type
   * from parser result
   * @param  IXTagContext|null $mapper
   * @return mixed
   */
  public function run(IXTagContext $mapper = null)
  {

    $this->mapper = $mapper;

    $context = $this->mapper->getBuilderContext();
    $this->delimiter = ($context->itemAt('delimiter')) ? ($context->itemAt('delimiter')) : $this->defaultDelimiter;

    $currentItem = $this->mapper->getCurrent();

    return $this->build($currentItem);
  }

  /**
   * Single object process
   * @param array $item
   * @return mixed
   */
  protected function build(array $item)
  {
    $this->beforeBuild($item);
    $this->afterBuild($item);
  }

  public function beforeBuild($param)
  {
    if ($this->hasEventHandler('onBeforeBuild')) {
      $event = new CEvent($this, $param);
      $this->onBeforeBuild($event);
      return $event->params;
    } else
      return true;
  }

  public function onBeforeBuild($event)
  {
    $this->raiseEvent('onBeforeBuild', $event);
  }

  public function afterBuild($param)
  {
    if ($this->hasEventHandler('onAfterBuild')) {
      $event = new CEvent($this, $param);
      $this->onAfterBuild($event);
      return $event->params;
    }
    return $param;
  }

  public function onAfterBuild($event)
  {
    $this->raiseEvent('onAfterBuild', $event);
  }


}