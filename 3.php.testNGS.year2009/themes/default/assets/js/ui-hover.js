jQuery(document).ready(function(){
	jQuery('.ui-state-default').hover(
	   function() { $(this).addClass('ui-state-hover'); },
	   function() { $(this).removeClass('ui-state-hover'); }
	);
});
