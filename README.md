codeSamples
===========
1. 2012 год. Модуль yii, реализующий функционал xslt рендера, включает в себя ряд инструментов для вызова виджетов/методов посредством простого, конфигурируемого xml-диалекта.
Примеры использования:
```html
    <xsl:include href="protected/xsl/layout.xsl"/>
    <xsl:template match="body">
    <h1>
      YII XSLT view Render examples
    </h1>
    <widget run="CTabView">
      <param name="tabs">
        <option name="id1">
          <option name="title">'test'</option>
          <option name="content">test content</option>
        </option>
      </param>
    </widget>
    
    <!-- Example params - single value, array value, assoc array value,
      <param name="options">
        <option>1</option>
        <option>2</option>
        <option name="level2">
          <option name="leve2.1">Some value. level 3</option>
          <option name="level2.2">
            <option>Some value 1. level 3</option>
            <option>Some value 2. level 3</option>
            <option name="test">Some value with name TEST. level 3</option>
          </option>
        </option>
      </param>
    Is sample get ==>
      array('options' => array(
            1,
            2,
            level2 => array(
                level2.1=> ' ... '
                level2.2=> array( '...', '...', test => '...')
            )
          )
    -->
    
    <!-- Start form example -->
    <tmpl type="form.beginForm"><!--  mapping values to context controller -->
      <param name="action" type="context">test.action</param>
      <param name="method">post</param>
      <param name="htmlOptions">
        <option name="id">form</option>
        <option name="class">yii-xslt</option>
      </param>
    </tmpl>
    <tmpl type="form.label"><!--  mapping values to context controller -->
      <param name="label" type="context">test.form.labels.mask|uppercase</param>
      <param name="for">mask</param>
    </tmpl>
    
    <!-- Start widget example -->
    <tmpl type="widget.CMaskedTextField">
      <param name="name">test</param>
      <param name="mask">99/99/9999</param>
    </tmpl>
    
    <!--  Clip - ->
    the Clip type  == widget.system.web.widgets.CClipWidget
    Clip example:
    
    <?php $this->beginWidget('system.web.widgets.CClipWidget', array('id'=>'My tab 1')); ?>
        My tab 1 ...
    <?php $this->endWidget(); ?>
    
    Another varian implement with xtags:
    
    <?php $this->beginClip('My tab 2'); ?>
    My tab 2 ...
    <?php $this->endClip(); ?>
    <!- -  Clip -->
    
    <!-- Start widget clip example. -->
    <tmpl type="clip">
      <param name="id">my1</param>
      <span>My tab 1 ...</span>
    </tmpl>
    
    <tmpl type="clip">
      <param name="id">my2</param>
      <param name="renderClip">0</param>
      <span>My tab 2 with addins param...</span>
    </tmpl>
    <!-- END widget clip example -->
    
    <!-- start tabs with clip content -->
    <tmpl type="widget.system.web.widgets.CTabView">
      <param name="tabs" type="context">clips|createArray count='tab' key='title' value='content'</param>
    </tmpl>
    
    <tmpl type="form.endForm"/>
    <!-- End form example -->
    
    <cache id="testCaching">
      <p>caching test</p>
    </cache>
    </xsl:template>
```
2. 2012 год. Компонет для аггрегации данных из различных источков в различных форматах, реально используется в коммерческом проекте для организации кластера мастер-слейв. Весь код, который может представлять коммерческую тайну вырезан, оставлены только самые общие вещи.
3. 2009 год. Тестовое задание для НГС - реализовать дерево сообщений неограниченной вложенности. Использован форк фреймворка kohana 3 - SMVC, авторство ядра половины классов остается за kohana team. Для реализации дерева применяется closure table - по тестам выяснилось, что оно вполне имеет право на жизнь при не слишном большой глубине веток (10 уровней и менее) и кол-вом элементов до 1млн.