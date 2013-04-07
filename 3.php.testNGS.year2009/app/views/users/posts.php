<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
?>
<div id="user">
<div class="caption">
	<div class="left">Имя:</div>
	<div class="right"><?php echo $user->username;?></div>
	<div class="left">Сообщений в форуме:</div>
	<div class="right"><?php echo $user->count_posts;?></div>
	<div><?php echo HTML::anchor( URL::site('users/posts/'.$user->id, true), "Перейти к профилю")." ";?></div>
</div>
<br/><br/>
<div class="pages">
	<?php echo $user->paging;?>
</div>
<div class="list">
<ul>
<?php
foreach($user->posts AS $post):

$url 	=	URL::site('forum/topic/'.$post->post_id, true);
?>
<li class="elem topic" onclick="window.location.assign('<?php echo $url;?>');">
	<div>
	<?php echo HTML::anchor($url, $post->post, array('class'=>'title')); ?>	
	<p class="desc"><?php echo mb_substr($post->content, 0, 100);?></p>
	</div>
</li>
<?php
endforeach;
?>
</ul>
</div>
<div class="pages">
	<?php echo $user->paging;?>
</div>
</div>
<script language="JavaScript" type="text/javascript">
jQuery(document).ready(function () {
	$('div.list ul li:even').addClass('even');
});
</script>
