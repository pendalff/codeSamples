/**
 * @author f-sem
 * Функции для использования плагина Dialog для фреймворка jQuery из библиотеки JqIU 
 * В данном случае - служат для сообщений пользователю.
 */

function prepare_dialog(title,message){
	if (jQuery('#dialog').length == 0) {
		jQuery('body').append('<div id="dialog" style="display:none;" title="' + title + '">' + message + '</div>');
	}
	else{
		jQuery("#dialog").dialog( 'destroy' );
		jQuery('#dialog').attr('title',title).html(message);
	}
	jQuery("#dialog").dialog({
			modal: true,
			minHeight: 200,
			minWidth: 350,
			buttons: {
				Ok: function() {
					$(this).dialog('close');
				}
			}
		});
	jQuery("#dialog").dialog('open');
}

function mess_err(s, delay){
	s=s.replace(/\n/gi, '<br/>');
	prepare_dialog('Ошибка!', s);
		if (delay != false) {
			window.setTimeout(function(){
				jQuery("#dialog").dialog('close')
			}, delay);
		}
}

function mess_done(s,delay){
	s=s.replace(/\n/gi, '<br/>');
	prepare_dialog('Успешно выполнено!', s);
	if (delay != false) {
		window.setTimeout(function(){
			jQuery("#dialog").dialog('close')
		}, delay);
	}
}

function confirm_dialog(str){
var title='Вы уверены?';
if (jQuery('#dialog').length == 0) {
	jQuery('body').append('<div id="dialog" style="display:none;" title="' + title + '">' + str + '</div>');
}
else {
	jQuery('#dialog').attr('title', title).html(str);
	jQuery('span#ui-dialog-title-dialog').text(title);
}
}
