<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */

?>
<div id="form">
<h2>Авторизация:</h2>
<?php 
if(isset($error)) echo View::factory('errors/forms')->set('errors',($error));
echo Form::open(url::site('users/login',true));
echo '<div> '. Form::label('l', 'Логин', array('class'=>'label')).Form::input('l',    $i['l']).'</div>';
echo '<div> '. Form::label('p', 'Пароль', array('class'=>'label')).Form::password('p',$i['p']).'</div>';
echo Form::submit('f','Отправить');
echo Form::close();
?>
</div>
