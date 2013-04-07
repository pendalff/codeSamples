<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
?>
<div id="topics">
<div class="caption">
	<div>
	<h2><?php echo $forum->name; ?>	</h2>
	<div class="desc"><?php echo ($forum->description); ?></div>
	<div class="count">Тем в форуме: <b><?php echo ($forum->count); ?></b></div>
	</div>
</div>
<?php
	if(auth::has_perm('forum', 'addpost', null)):
?>
	<div class="addpost">
		<?php echo HTML::anchor(URL::site('forum/'.$forum->forum_id.'/addpost', true), 'Добавить тему'); ?>
	</div>
<?php
	endif;
?>
<div class="pages">
	<?php echo $forum->paging;?>
</div>
<div class="list">

<?php
$user_model = $forum->users;
if($forum->count > 0 ):
?>
<ul>
<?php
foreach($forum->topics AS $topic):
	
$url 	=	URL::site('forum/topic/'.$topic->post_id, true);
$url_user = URL::site('users/show/'.$topic->user_id, true);
$user = $user_model->get_user($topic->user_id);

?>
<li class="elem topic" onclick="window.location.assign('<?php echo $url;?>');">
	<div>
	<?php echo HTML::anchor($url, $topic->post, array('class'=>'title')); ?>	
	<p class="desc"><?php echo mb_substr($topic->content, 0, 100);?></p>
	<p class="author">Написал: <?php echo HTML::anchor($url_user, $user->username, array('class'=>'nick')) ;?></p>
<?php
	if(auth::has_perm('forum', 'editpost', $topic->user_id)):
?>
	<div class="edit">
		<?php echo HTML::anchor(URL::site('forum/editpost/'. $topic->post_id, true), 'Редактировать'); ?>
	</div>
<?php
	endif;
?>
<?php
	if(auth::has_perm('forum', 'delpost', $topic->user_id)):
?>
	<div class="del">
		<?php echo HTML::anchor(URL::site('forum/delpost/'. $topic->post_id, true), 'Удалить'); ?>
	</div>
<?php
	endif;
?>
	</div>
</li>
<?php
endforeach;
?>
</ul>
<?php
else:
?>
Еще нет тем.
<?php
endif;
?>
</div>
<div class="pages">
	<?php echo $forum->paging;?>
</div>
</div>
<script language="JavaScript" type="text/javascript">
jQuery(document).ready(function () {
	$('div.list ul li:even').addClass('even');
});
</script>