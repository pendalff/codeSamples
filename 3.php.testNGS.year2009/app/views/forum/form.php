<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
$is_edit = isset($input['post_id']) && $input['post_id']>0;
$title =  $is_edit ?   'Редактирование' : 'Добавление';
$title .= isset($input['is_topic']) && $input['is_topic']>0 ? ' темы' : ' ответа';

?>
<div id="form">
<h2>
<?php echo $title.':<br />'; ?>
</h2>
<?php
echo View::factory('errors/forms')->set('errors',$errors);

if(!$is_edit) $url = $input['forum_id'].'/addpost/'.$input['parent_id'];
else $url = 'editpost/'.$input['post_id'];

echo Form::open(url::site('forum/'.$url, true));
echo '<div> '.Form::label('post', 'Заголовок', array('class'=>'label')).Form::input('post',    $input['post']).'</div>';
echo '<div> '.Form::label('content', 'Сообщение', array('class'=>'label')).Form::textarea('content',$input['content']).'</div>';
echo Form::submit('submit','Отправить');
echo Form::hidden('forum_id',$input['forum_id']);
echo Form::hidden('is_topic',$input['is_topic']);
echo Form::hidden('post_id' ,$input['post_id']);
echo Form::hidden('user_id' ,$input['user_id']);
if(!$is_edit) echo Form::hidden('parent_id' , $input['parent_id']);
echo Form::hidden('hidden','form_sent');
echo Form::close();
?>
</div>