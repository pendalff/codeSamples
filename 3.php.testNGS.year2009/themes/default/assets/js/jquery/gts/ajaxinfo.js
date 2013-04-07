/**
 * @author sem
 */
 
jQuery(document).ready(function(){
	var $ = jQuery;
	var element = jQuery('#ajaxinfo');
	var noaction = jQuery('#noaction');

	
	jQuery("#ajax_indicator").ajaxStart(function(){
	   $(this).show(100);
	});
	
	jQuery("#ajax_indicator").ajaxStop(function(){
	   $(this).hide(500);
	});
	
	jQuery('body').ajaxStart(function(){
		noaction.hide();
		element.find('#sendaction').show();
	});
	
	jQuery('body').ajaxError(function(){
		element.children('div').hide().end();
		element.find('#erroraction').show();
	});
	
	jQuery('body').ajaxSuccess(function(evt, request, settings){
		element.children('div').hide().end();
		element.find('#okaction').show();
	});	
	
	jQuery('body').ajaxStop(function(){
		setTimeout(function(){
			element.children('div').slideUp(200).end();
			noaction.slideDown(500);
		},2000);
	});
});
