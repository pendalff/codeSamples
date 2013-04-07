/**
 * @author sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
var $j = jQuery.noConflict();
jQuery(document).ready(function(jQuery){
	var $ = jQuery;
	var menu = $('#menu');
	menu.removeClass('disabled');
if( $('#cat_filter').length > 0){
	$('#cat_filter').change(function(){
		var cat_id = $(this).val();
		var href = window.location.href;
		if (href.match(/(&cat_id=(\d+))/i)) {
			href = href.replace(/(&cat_id=)(\d+)/i,"$1"+cat_id);
		}
		else{
			href=href+"&cat_id="+cat_id;
		}
		window.location.assign(href);
	});
}
$('#menu li').mouseover(function(){
				$(this).addClass('hover');
			}).mouseout(function(){
      			$(this).removeClass('hover');
    		});
		var elements = $('#menu ul li');
		var nested = null;
		var offsetWidth = 0;
		elements.each( function(i,el){
		$(el).each( function(z,e){
				offsetWidth = (offsetWidth >= e.offsetWidth) ? offsetWidth : e.offsetWidth;
		});
		$(el).each( function(k,e){
				$(e).css('width', offsetWidth + 'px');
		});
			
		}
		);

});
