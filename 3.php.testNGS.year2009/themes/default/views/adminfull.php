<?php
/**
 * @var $sidebars - массив с содержимым сайдбаров
 * @var $body	  - основное тело  шаблона
 */

//Если сайдбары не назначены - нужно назначить
if( !isset($sidebars) || !is_array($sidebars) ){
	$sidebars=array();
}
//Нет тела шаблона
if( !isset($body)){
	$body = "Содержимое шаблона не назначено!";
}
/*
 * В шаблоне есть четыре позиции
 */
$sidebar_positions = array( 'top','bottom','left','right' );
	
foreach ($sidebar_positions as $sidebar) {
	if(array_key_exists($sidebar, $sidebars)){
		$$sidebar = $sidebars[$sidebar];
	}
	else{
		$$sidebar = 0;	
	}
}

$twoclass = '';
if(!$left){
	$twoclass .= ' noleft';	
}
if(!$right){
	$twoclass .= ' noright';
}
?>
<div id="pagewidth" class="">
<? if($top) {?>
	<div id="header"> 
		<div class="sbhead ui-widget-header  ui-corner-all"> <? echo $top;?>	</div> 
	</div>
<? } ?>
<!-- Основная часть -->
	<div id="wrapper" class="clearfix  ui-widget-content  ui-corner-all" > 
			<div id="twocols" class="clearfix <? echo $twoclass;?>"> 
				<div id="maincol" > 
					<div class="tmpl_body"> <? if(isset($messages)) echo $messages;?> <? echo $body;?>  </div>
				</div>
			<? if($right) {?>
				<div id="rightcol">  
					<div class="sbright ui-widget">  <? echo $right;?>  </div>
				</div>
			<? 
			} ?>
			</div> 
<? if($left) {?>
	<div id="leftcol">
		<div class="sbleft">  <? echo $left;?>  </div>
	</div>
<? } ?>
<!-- Основная часть КОНЕЦ -->
	</div>
<? if($bottom) {?>
	<div id="footer">  
		<div class="sbfoot ui-widget-header  ui-corner-all"><? echo $bottom;?>  </div>
	</div>
<? } ?>
</div>
<?php
if(Kohana::$environment != Kohana::PRODUCTION)
echo DebugToolbar::render();
?>