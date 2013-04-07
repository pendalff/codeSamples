<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
?>
<div id="topics">
<?php
	if(auth::has_perm('forum', 'addforum', auth::userid())):
?>
	<div class="add">
		<?php echo HTML::anchor(URL::site('forum/addforum', true), 'Добавить форум'); ?>
	</div>

<?php
	endif;
?>
<ul>
<?php
foreach($forums AS $forum):
$url = URL::site('forum/show/'.$forum->forum_id, true);
?>
<li class="elem" onclick="window.location.assign('<?php echo $url;?>');">
	<div>
	<?php echo HTML::anchor($url, $forum->name, array('class'=>'title')); ?>	
	<div class="caption"><?php echo ($forum->description); ?></div>
	<div class="count">Тем в форуме: <b><?php echo ($post_obj->count_posts($forum->forum_id,' AND `is_topic`=1')); ?></b></div>
	<div class="count"><?php echo HTML::anchor(URL::site('forum/'.$forum->forum_id.'/addpost', true), 'Добавить тему'); ?></div>
	<div class="count"><?php echo HTML::anchor(URL::site('forum/tree/'.$forum->forum_id, true), 'В виде дерева'); ?></div>

<?php
	if(auth::has_perm('forum', 'editforum',  auth::userid())):
?>
	<div class="edit">
		<?php echo HTML::anchor(URL::site('forum/editforum/'. $forum->forum_id, true), 'Редактировать'); ?>
	</div>
<?php
	endif;
?>
<?php
	if(auth::has_perm('forum', 'delforum',  auth::userid())):
?>
	<div class="del">
		<?php echo HTML::anchor(URL::site('forum/delforum/'. $forum->forum_id, true), 'Удалить'); ?>
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
</div>
<script language="JavaScript" type="text/javascript">
jQuery(document).ready(function () {
	$('div#list ul li:even').addClass('even');
});
</script>