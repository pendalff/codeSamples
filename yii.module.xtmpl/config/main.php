<?php
/**
 * Config for module xtmpl
 * @author: sem
 * Date: 27.03.12
 * Time: 10:54
 */
return array(
  'components' => array( /*
    'log' => array( // configuration for the toolbar
      'routes'=>array(
        array( // configuration for the toolbar
          'class'=>'xtmpl.components.yii-debug-toolbar.YiiDebugToolbarRoute',
          'levels'=>'error, warning, trace, profile, info',
          'ipFilters'=>array('127.0.0.1'),
        ),
      ),
    ),*/
    'xtagProcessor' => array(
      'class' => 'xtmpl.components.xtag.XTagProcessor',
      'namespace' => '',
      'urlDTD' => null,
      //Available elements: tagname=>type
      'elements' => array(
        'tmpl' => array(
          //class of builder
          'builder' => 'default',
          //class of parser
          'parser' => 'default',
          'parserContext' => array(
            //map element type to element[@type]
            //(with DOT delimiter is - type.classname)
            // way 2 - use concrete element type and use map classnam
            'type' => '@type',
            //map classname to anoter attr
            'classname' => null,
            //url of namespace. empty string used xbase/dtd action for xmlns
            'namespace' => null,
            //main tag
            'tagName' => 'tmpl',
            //parameter tag
            'paramTagName' => 'param',
            //tree struct tag in parametrs, see dtd
            'recursiveParamTagName' => 'option',
          )
        ),
        'widget' => array(
          'builder' => 'default', //class of builder
          'parser' => 'default',
          'parserContext' => array(
            //element[@type] or concrete type
            'type' => 'widget',
            //classname attr
            'classname' => '@run',
            //url of namespace
            'namespace' => null,
            //main tag
            'tagName' => 'widget',
            //parameter tag
            'paramTagName' => 'param',
            //tree struct in parametrs, see dtd
            'recursiveParamTagName' => 'option',
          ),
        ),
        'widgetBegin' => array(
          'builder' => 'default', //class of builder
          'parser' => 'default',
          'parserContext' => array(
            //element[@type] or concrete type
            'type' => 'widgetBegin',
            //classname attr
            'classname' => '@run',
            //url of namespace
            'namespace' => null,
            //main tag
            'tagName' => 'widgetBegin',
            //parameter tag
            'paramTagName' => 'param',
            //tree struct in parametrs, see dtd
            'recursiveParamTagName' => 'option',
          ),
        ),
        'widgetCall' => array(
          'builder' => 'default', //class of builder
          'parser' => 'default',
          'parserContext' => array(
            //element[@type] or concrete type
            'type' => 'widgetCall',
            //classname attr
            'classname' => '@run',
            //url of namespace
            'namespace' => null,
            //main tag
            'tagName' => 'widgetCall',
            //parameter tag
            'paramTagName' => 'param',
            //tree struct in parametrs, see dtd
            'recursiveParamTagName' => 'option',
          ),
        ),
        'widgetEnd' => array(
          'builder' => 'default', //class of builder
          'parser' => 'default',
          'parserContext' => array(
            //element[@type] or concrete type
            'type' => 'widgetEnd',
            //classname attr
            'classname' => null, //'@run',
            //url of namespace
            'namespace' => null,
            //main tag
            'tagName' => 'widgetEnd',
            //parameter tag
            'paramTagName' => 'param',
            //tree struct in parametrs, see dtd
            'recursiveParamTagName' => 'option',
          ),
        ),
        'cache' => array(
          'builder' => 'default', //class of builder
          'parser' => 'default',
          'parserContext' => array(
            //element[@type] or concrete type
            'type' => 'cache',
            //classname attr
            //'classname'               => '@run',
            //url of namespace
            'namespace' => null,
            //main tag
            'tagName' => 'cache',
            //parameter tag
            'paramTagName' => 'param',
            //tree struct in parametrs, see dtd
            'recursiveParamTagName' => 'option',
          ),
        ),
      )
    ),
    'viewRenderer' => array(
      'class' => 'xtmpl.components.XsltViewRenderer',
      'debug' => false
    ),
  )
);