<?php
if(!isset($title)){
	$title = 'Новый виджет';
}

if(!isset($body)){
	$body = 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit';
}
?>
<div class="widget" <? if(isset($attr)){  echo HTML::attributes($attr); } ?>>
		<div class="widget-header">
			<? echo $title;?>
		</div>
		<div class="widget-content" style="display: block;">
			<? echo $body;?>
		</div>
</div>