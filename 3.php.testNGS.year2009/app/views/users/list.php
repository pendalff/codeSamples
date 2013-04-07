<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
?>
<div id="list">
<ul>
<?php
foreach($forums AS $forum):
$url = URL::site('forum/show/'.$forum->forum_id, true);
?>
<li class="elem" onclick="window.location.assign('<?php echo $url;?>');">
	<div>
	<?php echo HTML::anchor($url, $forum->name, array('class'=>'title')); ?>	
	<div class="caption"><?php echo ($forum->description); ?></div>
	<div class="count">Тем в форуме: <b><?php echo ($post_obj->count_posts($forum->forum_id)); ?></b></div>
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