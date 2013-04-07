<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
if(isset($istopic)){
	$childs = array();
	$childs[] = $nodes[ $nodes['root'] ];	
}
else{
	$childs = DB_ClosureTable_Render::getChilds($nodes);	
}

$level = "level";
$t=array('depth'=>0);

if( isset($childs[0]) ){ 
	$t = $childs[0];
}

$level .= $t['depth'];

?>
<ul class="<?php echo $level;?>">
<?php
foreach($childs AS $node):
$user = Model::factory('user')->get_user($node['user_id']);
$url_user = URL::site('users/show/'.$node['user_id'], true);
?>
<li class="elem topic">
	<div><h3><?php echo  $node['post']; ?></h3></div>
	<div class="desc">
		<?php echo ($node['content']);?>
	</div>
	<div class="author">Написал: 
		<?php echo HTML::anchor($url_user, $user->username, array('class'=>'nick')) ;?>
	</div>
	<div class="answer">
		<?php echo HTML::anchor(URL::site('forum/'.$node['forum_id'].'/addpost/'.$node['post_id'], true), 'Ответить'); ?>
	</div>
<?php
if( auth::has_perm( 'forum', 'editpost', $node['user_id'] )){
?>
	<div class="edit">
		<?php echo HTML::anchor(URL::site('forum/editpost/'.$node['post_id'], true), 'Редактировать'); ?>
	</div>
<?php	
}

if( auth::has_perm( 'forum', 'delpost', $node['user_id'] )){
?>
	<div class="del">
		<?php echo HTML::anchor(URL::site('forum/delpost/'.$node['post_id'], true), 'Удалить'); ?>
	</div>
<?php	
}
	if( isset($node['childs']) && count($node['childs']) > 0 ){
		echo View::factory('forum/subtree')->bind('nodes', $node)->render();
	}
?>
</li>
<?php
endforeach;
?>
</ul>
