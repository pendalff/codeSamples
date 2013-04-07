/**
 * Диалоги.
 * @author Pendalff
 * @authorurl http://pendalff.ru
 */

	function message (message,title) {
	var dialog = jQuery("#dialog-message");
	if(dialog.length == 0){
		var dialog = jQuery('<div id="dialog-message"></div>');
		
		jQuery('body').append(dialog);
	}
	else{
		jQuery("#dialog-message").dialog('destroy');
	}

	dialog.attr('title',title);
	dialog.html(message);

		jQuery("#dialog-message").dialog({
				modal: true,
				width: 400,
				buttons: {
					Ok: function() {
						$(this).dialog('close');
					}
				}
		});
	}

function mess_err(s, delay){
	s=s.replace(/\n/gi, '<br/>');
	message(s,'Ошибка!');
		if (delay != false) {
			window.setTimeout(function(){
				jQuery("#dialog").dialog('close')
			}, delay);
		}
}

function mess_done(s,delay){
	s=s.replace(/\n/gi, '<br/>');
	message(s, 'Успешно выполнено!');
	if (delay != false) {
		window.setTimeout(function(){
			jQuery("#dialog").dialog('close')
		}, delay);
	}
}
