<?php
if(isset($button['active']) && $button['active'] == 1){
	$active = 'active';
}
else{
	$active = '';
}

?>
<a href="<? echo $button['link']?>" class="button ui-corner-all <?=$active;?>" title="<? echo $button['title'];?>" id="<? echo $id;?>">
<img src="<? echo $button['icon']?>" alt="<? echo $button['title'];?>">
<span><? echo $button['label']?></span>
</a>
