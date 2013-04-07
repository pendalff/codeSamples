<div class="tabs" <? if(isset($attr)){  echo HTML::attributes($attr); } ?>>
	<ul>
<?php
	$li ='';
	$body = '';
	$tab_attr ='';
	foreach ($elements as $key => $elem) {
		
		if(isset($elem['attr'])){  
			$tab_attr =  HTML::attributes($elem['attr']); 
		}
	
		$li .= "<li>";

		if( isset($elem['ajax']) && $elem['ajax'] == 1){
			$li.="<a href='{$elem['body']}'> {$elem['title']} </a>";
		}
		else{
			$li.="<a href='#{$elem['id']}'> {$elem['title']} </a>";
			
			$body.="<div id='{$elem['id']}' {$tab_attr}>";
			$body.=$elem['body'];
			$body.="</div>";	
		}
		//.$elem['title'].
		$li.="</li>";

	}
	
	echo $li;
?>
	</ul>
<?php
	echo $body;
?>
</div>

