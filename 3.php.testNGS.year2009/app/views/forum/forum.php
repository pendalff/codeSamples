<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
$is_edit = isset($input['forum_id']) && $input['forum_id']>0;
$title =  $is_edit ?   'Редактирование' : 'Добавление';
$title .= ' форума';

?>
<div id="form">
<h2>
<?php echo $title.':<br />'; ?>
</h2>
<?php
echo View::factory('errors/forms')->set('errors',$errors);

if(!$is_edit) $url = 'addforum';
else $url = 'editforum/'.$input['forum_id'];

echo Form::open(url::site('forum/'.$url, true));
echo '<div> '.Form::label('name', 'Название', array('class'=>'label')).Form::input('name',    $input['name']).'</div>';
echo '<div> '.Form::label('description', 'Описание', array('class'=>'label')).Form::textarea('description',$input['description']).'</div>';
echo Form::submit('submit','Отправить');
echo Form::hidden('forum_id',$input['forum_id']);

echo Form::hidden('hidden','form_sent');
echo Form::close();
?>
</div>