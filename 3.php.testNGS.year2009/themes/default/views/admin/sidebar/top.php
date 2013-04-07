<h2 class="header"><? echo $caption;?></h2>
<?
echo Request::factory('widget/SSW')->execute();
?>
<div class="top_icon" style="background: url(<? echo $caption_icon;?>) no-repeat 50% 50% transparent"></div>
<div>
<?php
if(isset($buttons) && is_array($buttons)){
?>
	<ul class="topToolbar">
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