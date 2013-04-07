<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */

$class = "";
	if($node->has_children() ||$node->level==1){
		$class.=" folder";
	}else{
		$class.=" file";
	}
	if($node->level>1 && $node->level<3){
		$class.=" accept";
	}
?>
<td class="td_box">
<? echo Core_Actions::link_box($node->id);?>
<input type="hidden" name="newparent[<?=$node->id;?>]" id="newparent-<?=$node->id;?>">
</td>
<td class="td_id">
	<?=$node->id;?>
</td>
<td class="td_name">
  <span class="<?php echo $class; ?>">
    <?php echo $node->name;?>
  </span>
</td>
<?php
if(Kohana::$environment != Kohana::PRODUCTION && 0){
?>
<td class="td_lft">
    <?php echo $node->lft;?>
</td>
<td class="td_rgt">
    <?php echo $node->rgt;?>
</td>
<td class="td_level">
    <?php echo $node->lvl;?>
</td>
<?php
}
?>
<td class="td_parent">
    <?php echo $node->parent_id;?>
</td>

<td class="td_actions">
    <?php echo View::factory('admin/tree/actions')->set('id', $node->id)->set('level', $node->level)->bind('controller', $controller)->bind('node',$node);?>
</td>