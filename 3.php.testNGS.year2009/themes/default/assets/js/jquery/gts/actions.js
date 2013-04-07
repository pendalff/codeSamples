/**
 * @author sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
window.addEvent = window.addEvent || function(){};


jQuery(document).ready(function(){
	var $ = jQuery;	
	jQuery('ul.subheadToolbar a').bind('click', function(){
		 var action = jQuery(this).attr('href');
		 var form = jQuery('#adminform');
		 if (action && form.length > 0) {
			// 	console.info(jQuery(this) === jQuery('a#add'));
			if (jQuery(this).hasClass('delete') || jQuery(this).attr('id') == 'del') {
			
			    if(confirm('Вы уверены?')){
						form.attr('action', action).submit();
						return false;
				}
				return false;
			}
			else {
				form.attr('action', action).submit();
				return false;
			}
		 }
		 return true;
	});
		
   jQuery('button.cancel').bind('click', function(){
		window.location.href = (jQuery('ul.subheadToolbar li:first-child a').attr('href'));
		return false;
	});
	
	function send_data(elem){
		 var action = elem.attr('href');
		 if (action) {
		 	jQuery('#adminform').attr('action', action).submit();
		 	return false;
		 }
		 return true;
	} 
	
	jQuery('.delete a').bind('click', function(){
		return confirm('Вы уверены?');
	});
	
	jQuery('.move a').bind('click', function(){
		return send_data(jQuery(this));
	});
	
	jQuery('#check_all').bind('click', function(){
		var elem = jQuery(this);
		var boxs = jQuery('input[name="ids[]"]');
		if(elem.attr('checked')){
			boxs.attr('checked','checked');
		}
		else{
			boxs.attr('checked', false );			
		}
		boxs.trigger('change');
	});
});
