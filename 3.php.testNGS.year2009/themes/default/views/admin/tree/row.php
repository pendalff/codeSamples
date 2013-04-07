<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
$class="";
//echo $node->parent_id."<br/>";

$node->parent_id = !empty($node->parent_id ) ? $node->parent_id : false;
if( $node->level >= 1 ){
	$class.=" child-of-node-".$node->parent_id;
}
$view = View::factory('admin/tree/node')->set('node', $node)->bind('controller',$controller);
?>
<tr id="node-<?=$node->id;?>" class="<?=$class;?>">
    <?=$view->render();?>
 </tr>