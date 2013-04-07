<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */

$title =  'Редактирование параметров';
?>
<div id="form">
<h2>
<?php echo $title.':<br />'; ?>
</h2>
<?php
echo View::factory('errors/forms')->set('errors',$errors);
$configs = array(
			'host'        => array('Хост БД', 'input'),
			'dbname'       =>  array('Имя БД', 'input'),
			'username'   =>  array('Пользователь БД', 'input'),
			'password'   =>  array('Пароль пользователя БД', 'input'),
			'topic_per_page'=>  array('Число тем на страницу', 'input'),
			'post_per_page'=>  array('Число ответов на страницу', 'input'),
			'title' => 			array('Заголовок (title)', 'input'),
			'meta_keywords' =>  array('Поле meta keywords', 'textarea'),
			'meta_description' =>  array('Поле meta description', 'textarea'),

); 
echo Form::open(url::site('config/index', true));
foreach($configs AS $name=>$v){
	$method = new ReflectionMethod('Form', $v[1]);
	$f = $method->invokeArgs(NULL,  array('name'=>$name, 'value'=>  arr::get($input, $name) ) );
	echo '<div> '.Form::label($name, $v[0], array('class'=>'label')).$f.'</div>';	
}
echo Form::hidden('hidden','form_sent');
echo Form::submit('submit','Отправить');
echo Form::close();
?>
</div>