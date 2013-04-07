<?
if(!isset($caption)){
	$caption = "Инструменты:";
}
if(!isset($footer)){
	$footer = "&nbsp;";
}

?>
<div class="portlet_wrapper ui-widget-content ui-corner-top" <? if(isset($attr)){  echo HTML::attributes($attr); } ?>>

	<div class="portlet_helper ui-widget-header ui-corner-top">
		<b><? echo $caption;?></b>
		<div  class="icon ui-corner-all ui-state-default" title="Свернуть панель">
			<span class="ui-icon ui-icon-carat-1-n"></span>
		</div>
		<div  class="icon ui-corner-all ui-state-default" title="Cвернуть все виджеты">
			<span class="ui-icon ui-icon-minus"></span>
		</div>
		<div  class="icon ui-corner-all ui-state-default" title="Перемещение панели">
			<span class="ui-icon ui-icon-arrow-4-diag"></span>
		</div>
		<div  class="icon ui-corner-all ui-state-default" title="Вернуть на место">
			<span class="ui-icon ui-icon-home"></span>
		</div>
	</div>

	<ul class="portlet">
		<? echo $list;?>
	</ul>
	<div class="portlet_helper ui-widget-header ui-corner-bottom"><? echo $footer;?></div>
</div>
