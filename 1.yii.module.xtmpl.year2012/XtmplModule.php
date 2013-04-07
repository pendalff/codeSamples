<?php
/**
 * XSLT-based template engine
 * @version 1.5
 * User: sem
 * Date: 28.03.12
 * Time: 9:02
 */
class XtmplModule extends CWebModule
{
  public function preinit()
  {
    // import the module-level components
    $this->setImport(array(
      'xtmpl.interfaces.*',
      'xtmpl.behaviors.*',
      'xtmpl.components.xtag.interfaces.*',
      'xtmpl.components.xtag.behaviors.*',
      'xtmpl.components.xtag.exceptions.*',
      'xtmpl.components.xtag.helpers.*',
      'xtmpl.components.xtag.*',
      'xtmpl.components.yii-debug-toolbar.*',
      'xtmpl.components.*',
      'xtmpl.controllers.*',
      'xtmpl.models.*',
    ));
    parent::preinit();
  }

  public function init()
  {
    $default = array(
      'components' => array(
        'xtagProcessor' => array(
          'class' => 'xtmpl.components.xtag.XTagProcessor',
          'namespace' => 'x',
          'urlDTD' => null,
          //Available elements: tagname=>type
          'elements' => array(
            'tmpl' => array(
              'builder' => 'default', //class of builder
              'parser' => 'default',
              'parserContext' => array(
                'type' => '@type', //element[@type] or concrete type
                'namespace' => null, //url of namespace
                'tagName' => 'tmpl', //main tag
                'paramTagName' => 'param', //parameter tag
                'recursiveParamTagName' => 'option', //tree struct in parametrs, see dtd
              )
            ),
          )
        )
      )
    );
    //dynamic replace of configuration if it need, default used xtmpl config
    if (file_exists($this->basePath . '/config/main.php')) {
      $currentConfig = $this->basePath . '/config/main.php';
    } else {
      $currentConfig = Yii::getPathOfAlias('xtmpl') . '/config/main.php';
    }

    $config = new CConfiguration($currentConfig);
    Yii::app()->configure(CMap::mergeArray($default, $config->toArray()));
  }

  public function beforeControllerAction($controller, $action)
  {
    if (parent::beforeControllerAction($controller, $action)) {
      // this method is called before any module controller action is performed
      // you may place customized code here
      return true;
    } else
      return false;
  }
}
