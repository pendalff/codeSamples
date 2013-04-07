/**
 * @author sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
window.addEvent = window.addEvent || function(){};
jQuery(document).ready(function(){

	function toggleBox(elem){
		var box  = elem.find("input:checkbox");
			if(box.attr('checked') == false){
			box.attr('checked', 'checked');
		}
		else{
			box.attr('checked', false);
		}
	}
	jQuery('#main_list thead tr, #main_list tfoot td').addClass('ui-widget-header').css('border','0');
	jQuery('#main_list tfoot td').addClass('ui-corner-bottom');
});
