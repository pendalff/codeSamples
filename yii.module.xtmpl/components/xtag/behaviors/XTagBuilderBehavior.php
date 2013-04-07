<?php
/**
 * Base behavior for builders
 * @author: sem
 * Date: 28.03.12
 * Time: 22:33
 */
class XTagBuilderBehavior extends CBehavior
{
    public function events()
    {
      return array(
        'onBeforeBuild' => 'beforeBuild',
        'onAfterBuild'  => 'afterBuild'
      );
    }

    public function beforeBuild( CEvent $event ){
      return $event;
    }

    public function afterBuild( CEvent $event ){
      return $event;
    }

}
