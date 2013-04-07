<?php defined('SYSPATH') or die('No direct script access.');
$icon = 'ui-icon ';
$class = '';
switch ($type) {
	case 'success':
		$icon .= 'ui-icon-check ';
		$class .= 'ui-state-active ';
		$title = 'Успешно!';
	break;
	case 'error':
		$icon .= 'ui-icon-alert ';	
		$class .= 'ui-state-error ';
		$title = 'Ошибка!';
	break;
	case 'validation':
		$icon .= 'ui-icon-alert ';	
		$class .= 'ui-state-error ';
		$title = 'Ошибка валидации!';
	break;
	case 'notice':
		$icon .= 'ui-icon-info ';
		$class .= 'ui-state-highlight ';	
	break;	
	default:
		$icon .= 'ui-icon-info ';
		$class .= '';	
		$title = 'Сообщение';	
	break;
}
$icon = "<span class='{$icon}' style='float:left; margin:0 7px 0 0;'></span>"
?>
<div id="dialog-message"  class="<?=$class;?>" title="Сообщение">
	<ul>
		<? foreach ($data as $message):	?>
			<li><?=$icon;?><?php echo $message;?></li>
		<? endforeach;?>
	</ul>
</div>
<script type="text/javascript">
jQuery(function() {
	jQuery("#dialog-message").dialog({
		modal: true,
		width: 350,
		buttons: {
			Ok: function() {
				jQuery(this).dialog('close');
			}
		}
	});
	
	setTimeout(function(){	jQuery("#dialog-message").dialog("close"); }, 6000);
});
</script>