<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
$is_edit = isset($input['id']) && $input['id']>0;
$title =  $is_edit ?   'Редактирование' : 'Регистрация';
$title .= ' пользователя'

?>
<div id="form">
<h2>
<?php echo $title.':<br />'; ?>
</h2>
<?php
echo View::factory('errors/forms')->set('errors',$errors);

if(!$is_edit) $url = Route::get('default')->uri( array('controller'=>'users','action'=>'register'));
else 		  $url =  Route::get('default')->uri( array('controller'=>'users','action'=>'edit','id'=>$input['id']));

echo Form::open(url::site($url, true));
echo '<div>'. Form::label('username', 'Логин', array('class'=>'label')).' '.Form::input('username',    $input['username']).'</div>';
echo '<div> '. Form::label('email', 'Email', array('class'=>'label')).' '.Form::input('email',       $input['email']).'</div>';
if(!$is_edit){
echo '<div>  '. Form::label('password', 'Пароль', array('class'=>'label')).' '.Form::password('password',   $input['password']).'</div>';
}
else{
echo '<div> Изменить пароль:</div>';
echo '<div> '. Form::label('password_new', 'Новый пароль', array('class'=>'label')).' '.Form::password('password_new',   $input['password_new']).'</div>';
	if($self_user){
		echo '<div> '. Form::label('password_old', 'Старый пароль', array('class'=>'label')).' '.Form::password('password_old',   $input['password_old']).'</div>';
	}
}
if(isset($roles) && is_array($roles)){

echo '<div> Роли пользователя:</div>';
	foreach($roles as $role){
		echo '<div>';
		echo Form::checkbox('roles[]', $role->id, in_array($role->id, $input['roles']));
		ECHO Form::label('roles[]', $role->description).'</div>';
	}
}
echo Form::submit('submit','Отправить');
echo Form::hidden('id' ,(int) $input['id']);
echo Form::hidden('hidden','form_sent');
echo Form::close();
?>
</div>