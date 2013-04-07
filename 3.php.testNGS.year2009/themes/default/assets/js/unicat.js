/**
 * @author sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
var $j = jQuery.noConflict();
jQuery(document).ready(function(){
	/*
	if(jQuery('.images a').length ==0){
		jQuery('.image a').lightBox();
	}
	if(jQuery('.images a').length > 0){
		jQuery('.images a').lightBox();
	}
	*/
	if (jQuery('#field_tabs').length > 0) {
		jQuery('#field_tabs ul').idTabs(); 		
	}	

});
