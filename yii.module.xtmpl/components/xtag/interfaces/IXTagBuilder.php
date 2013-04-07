<?php
/**
 * Interface for builders concrete xtag elements
 * User: sem
 * Date: 27.03.12
 * Time: 9:02
 */
interface IXTagBuilder
{
  /**
   * @param IXTagProcessor $proc
   */
  public function __construct(IXTagProcessor & $proc);

  /**
   * Start build one IXTagElement element with concrete render type
   * from parser result
   * @abstract
   * @param  IXTagContext|null $mapper
   * @return mixed
   */
  public function run(IXTagContext $mapper = null);

  /**
   * @abstract
   * @param CEvent $event
   */
  public function onCurrent(CEvent $event);

  public function onBeforeBuild($event);

  public function beforeBuild($param);

  public function onAfterBuild($event);

  public function afterBuild($param);

}