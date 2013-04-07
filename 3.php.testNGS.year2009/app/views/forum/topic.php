<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */

$topic_first = $topic[$topic['root']];
$user_model = new Model_User;

$user = $user_model->get_user($topic_first['user_id']);
$url_user = URL::site('users/show/'.$topic_first['user_id'], true);
?>
<div id="topics">
<div class="caption">
	<div>
	<h2><?php echo $topic_first['post']; ?>	</h2>
	<div class="desc"><?php echo $topic_first['content']; ?></div>
	<div class="author"><?php echo HTML::anchor($url_user, $user->username); ?></div>
	<div class="count">
		Ответов: <b><?php echo ($topic['count']-1); ?></b>
	</div>
	<div class="answer">
		<?php echo HTML::anchor(URL::site('forum/'.$topic_first['forum_id'].'/addpost/'.$topic_first['post_id'], true), 'Ответить'); ?>
	</div>
<?php
if( auth::has_perm( 'forum', 'editpost',$topic_first['user_id'] )){
?>

	<div class="edit">
		<?php echo HTML::anchor(URL::site('forum/editpost/'.$topic_first['post_id'], true), 'Редактировать'); ?>
	</div>
<?php	
}

if( auth::has_perm( 'forum', 'delpost', $topic_first['user_id'] )){
?>
	<div class="del">
		<?php echo HTML::anchor(URL::site('forum/delpost/'.$topic_first['post_id'], true), 'Удалить'); ?>
	</div>
<?php	
}
?>
	</div>
</div>
<div class="pages">
	<?php echo $topic['paging'];?>
</div>
<div class="list">

<?php

echo View::factory('forum/subtree')->set('nodes', $topic);
/*
foreach($topic_first['childs'] AS $top):

$user = $user_model->get_user($top['user_id']);	
$url_user = URL::site('users/show/'.$top['user_id'], true);

?>
<li class="elem topic ">
	<div>
	<?php echo  $top['post']; ?>	
	<p class="desc"><?php echo ($top['content']);?></p>
	<p class="author">Написал: <?php echo HTML::anchor($url_user, $user->username, array('class'=>'nick')) ;?></p>
	<div class="answer">
		<?php echo HTML::anchor(URL::site('forum/'.$top['forum_id'].'/addpost/'.$top['post_id'], true), 'Ответить'); ?>
	</div>
<?php
if( auth::has_perm( 'forum', 'editpost',$top['user_id'] )){
?>
	<div class="edit">
		<?php echo HTML::anchor(URL::site('forum/editpost/'.$top['post_id'], true), 'Редактировать'); ?>
	</div>
<?php	
}
?>
	</div>
</li>
<?php
endforeach;
*/
?>

</div>
<div class="pages">
	<?php echo $topic['paging'];?>
</div>
</div>