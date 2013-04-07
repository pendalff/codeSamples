<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
/*<div class="subhead_icon" style="background: url(<? echo $caption_icon;?>) no-repeat 50% 50% transparent"></div>
*/
?>
<div class="button_panel ui-widget-header  ui-corner-all">

<?php
if(isset($buttons) && is_array($buttons)){
?>
	<ul class="subheadToolbar">
	<?php
		foreach($buttons as $button){
			echo "<li class='ui-corner-all ul-wiget ui-state-default'>".$button."</li>";
		}
	?>
	</ul>

<?php
}
?>
</div>