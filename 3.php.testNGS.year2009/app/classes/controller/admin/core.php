<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Контроллер действий админки. 
 * Декоратор
 */
class Controller_Admin_Core extends Controller_Admin_Base {
	
/**
 * @var $template
 */
	public 		$template = 'admin';
	
	protected 	$sidebars  = array();
/**
 * Содержит объект реестра.
 * @var object registry
 */
	protected   $registry;
	
/**
 * @var mixed $body - содержит контент шаблона
 */	
	
	protected	$body;
	
	public    $simple   = false;
/**
 * Главная страница магазина
 * 
 */
	public function action_index() {
	 //Виджеты справа
		$portlet_arr = array(array('title'=>'Особый заголовок'),NULL,NULL);
		
		$portlet = Widget::portlet($portlet_arr, array('id'=>'right_panel'),'Правая панель');
		
		$params = array( 'title' => "Виджет справа",
						 'body' => "asdf sdfg sdf sdfsd fds fsdfку sdf");
						 
		$widget = Widget::simple($params);
		
		$right_panel = Widget::group(array($widget,$portlet));
	//Виджеты слева	

		$params = array( 'title' => "Виджет слева" );
		
		$portlet_arr = array(NULL,NULL);
		
		$portlet = Widget::portlet($portlet_arr, array('id'=>'left_panel'),'Левая панель');
		
		$l = Widget::simple($params);
		$l.=$portlet->render();
		
		$sidebars=array(
			'bottom' => 'Низ',
			'right' => $right_panel,
			'left' => $l,
		);
		
		$this->sidebars = $sidebars;

	//	$core = $this->registry->get('core.images');
	//	var_dump($core);
		/*
		$form = new KForm('simple');

		$elem1 = array( 
						'ajax' => 0,
						'id'   => 'tab2',
						'title' => 'Tab',
						'body'  => $form,
					 	'attr' => array('class'=>'a')
					 );	
		*/
		$elem2 = array( 
						'ajax' => 1,
						'id'   => 'tab1',
						'title' => 'ajax Tab',
						'body'  => 'index.php?option=com_gts&act=settings&ajax=1',
					 );		
		$elem3 = array( 
						'ajax' => 0,
						'id'   => 'tab3',
						'title' => 'Tab22',
						'body'  => 'ajax/sdfsdfsdfdcontent sdfs dfsd fsd fsdf sdf ',
					 	'attr' => array('class'=>'a')
					 );	

		
		$tabs = Widget::tabs(array($elem2,$elem3));
		//var_dump($tabs);
		$this->body = $tabs;
		
	//	$this->body = $body;

}

/**
 * 
 */
	public function action_orgs() {
		$inform = Request::factory('Admin_Elements_Widget/ajaxinfo')->execute();;		
		$news_widgets =  SMVC_Config::instance()->load('widgets/products');
		$portlet_arr = array( 
							 $inform, 
							 $news_widgets->viewer
							 );		
	//	$portlet_arr = array(array('title'=>'Подсказки по работе с товарами:'));
		
		$portlet = Widget::portlet($portlet_arr, array('id'=>'left_panel'),'Панель инструментов:');

		//$inform = Request::factory('widget/ajaxinfo')->execute();;

		$panel = Widget::group(array($portlet));
			$sidebars=array(
			'left' => $panel
		);
		
		$this->sidebars = $sidebars;		
		
	    $action = Request::instance()->param('subtask','index');
		if($this->_is_ajax != TRUE){
		    Request::factory('Admin_Elements_sidebar/subtop')->execute();	
		}
		Request::factory('Admin_Orgs/'.$action)->execute();	
	}

/**
 * Товары магазина
 */
	public function action_refs() {
		$inform = Request::factory('Admin_Elements_Widget/ajaxinfo')->execute();;		
		$news_widgets =  SMVC_Config::instance()->load('widgets/products');
		$portlet_arr = array( 
							 $inform
							 );		
	//	$portlet_arr = array(array('title'=>'Подсказки по работе с товарами:'));
		
		$portlet = Widget::portlet($portlet_arr, array('id'=>'left_panel'),'Панель инструментов:');

		//$inform = Request::factory('widget/ajaxinfo')->execute();;

		$panel = Widget::group(array($portlet));
			$sidebars=array(
			'left' => $panel
		);
		
		$this->sidebars = $sidebars;		
		
	    $action = Request::instance()->param('subtask','index');
		if($this->_is_ajax != TRUE){
		    Request::factory('Admin_Elements_sidebar/subtop')->execute();	
		}
		Request::factory('Admin_Refs/'.$action)->execute();	
	}

/**
 * Категории магазина. Все действия делегированы отдельному контроллеру.
 * @return 
 */
	public function action_cats() {
	
	    //Виджеты слева
		$news_widgets =  SMVC_Config::instance()->load('widgets/cats');
		$portlet_arr = array( 
							 $news_widgets->work_with_drop,
							// $news_widgets->default,
							// $news_widgets->default
							);
		
		$portlet = Widget::portlet( $portlet_arr, array('id'=>'left_panel'),'Справочная информация:');		
		//array('title','body','attr');
		$group = array($portlet);
		//$group = array($portlet);
		$panel = Widget::group( $group );

		$sidebars=array(
			'left' => $panel
		);
		
		$this->sidebars = $sidebars;	


	    $action = Request::instance()->param('subtask','index');
	    Request::factory('Admin_Elements_sidebar/subtop')->execute();
		Request::factory('admin_cats/'.$action)->execute();
	}
	
	public function action_import() {
	    $action = Request::instance()->param('subtask','index');
	    
		Request::factory('admin_import/'.$action)->execute();
	}
/**
 * Заказы магазина
 * @return 
 */
	public function action_order() {
		$portlet_arr = array(
		array('title'=>'Подсказки по работе с заказами:','body'=>'Используйте знак "+" чтобы просмотреть подробную информацию о составе заказа.'),
		array('title'=>'Подсказки по статусам:','body'=>'
		<p><b>%%NAME%%</b> - шаблон имени (ФИО) заказчика</p>
		<p><b>%%ORDER%%</b> - шаблон содержимого корзины заказа</p>
		<p><b>%%№№%%</b> - шаблон номера заказа</p>
				
		'),
		);
		
		$portlet = Widget::portlet($portlet_arr, array('id'=>'left_panel'),'Панель инструментов:');

		$inform = Request::factory('widget/ajaxinfo')->execute();;

		$panel = Widget::group(array($inform,$portlet));
			$sidebars=array(
			'left' => $panel
		);
		
		$this->sidebars = $sidebars;	/*		
		if($this->_is_ajax != TRUE){
		self::$scripts[] = 'jquery.gts.actions';

		    $body  .= Request::factory('sidebar/subtop')->execute();	

		}
		*/	
	    $body  = Request::factory('sidebar/subtop')->execute();
		$body .=  Request::factory('Admin_Orders/index')->execute();
	
		$this->page->content  = $body;	
	}

/**
 * Поля
 * @return 
 */
	public function action_fields() {

	}

/**
 * Действие для реализайии синхронизации - импорта и экспорта
 * @return 
 */
	public function action_sync() {
		$body .=  Request::factory('Admin_Sync/index')->execute();
	
		$this->page->content  = $body;			
	}


/**
 * Метод - Страница настроек
 * @return 
 */
	public function action_settings() {

	 //Виджеты справа
		$portlet_arr = array(array('title'=>'Справочная информация', 'body'=>'В этом разделе хранятся различные настройки электронного магазина.'));
		
		$portlet = Widget::portlet($portlet_arr, array('id'=>'right_panel'),'Панель инструментов:');

		$inform = Request::factory('widget/ajaxinfo')->execute();;

		$panel = Widget::group(array($inform,$portlet));

		$sidebars=array(
			'left' => $panel
		);
		
		$this->sidebars = $sidebars;	
		
		$form = Request::factory('admin_settings/index')->execute();;
		
		$elem1 = array( 
						'ajax' => 0,
						'id'   => 'tab1',
						'title' => 'Картинки товаров',
						'body'  => $form,
					 	'attr' => array('class'=>'a')
					 );	

		//var_dump($images);
		self::$styles[]='forms/validate';		
		self::$styles[]='forms/settings';	
		
		$form = Request::factory('admin_settings/path')->execute();;
		/*
		if (isset($_POST['path']) && $form->populate($_POST)) {
            $data = $form->result();
			$registry->set('core.path',$data['path']);
           // var_dump($data);
        } else if (Request::$method == 'GET') {
				$form->pre_populate($images);
		}
		*/
		$elem2 = array( 
						'ajax' => 0,
						'id'   => 'tab2',
						'title' => 'Пути к картинкам',
						'body'  => $form,
					 	'attr' => array('class'=>'a')
					 );	
		$elem3 = array( 
						'ajax' => 0,
						'id'   => 'tab3',
						'title' => 'Картинки категорий',
						'body'  => Request::factory('admin_settings/images_cats')->execute(),
					 );		
		$elem4 = array( 
						'ajax' => 1,
						'id'   => 'tab4',
						'title' => 'Broken Tab',
						'body'  => 'LUindex.php?option=com_gts&act=settings&ajax=1',
					 );	
		$tabs = Widget::tabs(array($elem1,$elem3,$elem2));
		//var_dump($tabs);
		
		$this->page->content = $tabs;
		//@$this->template->body = "Настройки программы";
	}
/* ****************************************************************************
	Не действия магазина идут ниже этого комментария
***************************************************************************** */

/**
 * Выполняется до запуска действия
 * @return 
 */	
	public function before() {

		parent::before();	
		
	//	$this->registry = Registry::instance();
		
		if($this->_is_ajax != TRUE ) {

		if(isset ($_COOKIE["theme"]))
		{
		    $theme = $_COOKIE["theme"];
		} else {
			$theme = 'south-street';
		}
		$this->page->add_CSS('admin.css');
		$this->page->add_CSS('widget.css');	
		$this->page->add_CSS('themes/'.$theme.'/jquery-ui-1.7.3.custom.css');		

		$this->page->add_JS('jquery/cookie/jquery.cookie.js');		
		$this->page->add_JS('jquery/ui/min/jquery.ui.min.js');		
		$this->page->add_JS('ui-hover.js');		
		$this->page->add_JS('jquery/gts/dialog.js');		
		$this->page->add_JS('jquery/gts/portlet.js');		
		$this->page->add_JS('jquery/gts/tabs.js');		
	
		
			$this->template->bind('sidebars', $this->sidebars);
		
			$this->template->bind('content', $this->page->content);

			$this->template->bind('controller', $this);
		}
		else{
			echo $this->page->content;
		}
	}	
/**
 * После запуска действия
 * @return 
 */	
	public function after() {
		parent::after();
		if($this->_is_ajax!==TRUE){
			$sidebar_positions = array( 'top','bottom','left','right' );
 //NOTE!
			if(!array_key_exists('top',$this->sidebars) && $this->simple){
				$this->sidebars['top'] = Request::factory('admin_elements_sidebar/top')->execute();
			}
			
			if(!array_key_exists('bottom',$this->sidebars) && $this->simple){
				$this->template->sidebars['bottom'] = Request::factory('admin_elements_sidebar/bottom')->execute();
			}
		}
	}

}