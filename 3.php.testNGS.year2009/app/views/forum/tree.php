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

<?php
foreach($forum->topics AS $topic):
?>
	<?php
	echo View::factory('forum/subtree')->bind('nodes', $topic)->set('istopic', true)->render();;
	?>
<?php
endforeach;
?>

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
//	$('div.list ul li:even').addClass('even');
});
</script>