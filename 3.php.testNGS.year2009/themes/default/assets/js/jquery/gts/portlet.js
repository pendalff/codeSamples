/**
 * Набор всего-всего для работоспособности портлет-набора виджетов.
 * @author Pendalff
 * @authorurl http://pendalff.ru
 */
var act,selfurl;
jQuery(document).ready(function() {

//Сортировка виджетов
	//jQuery("ul.portlet")
	jQuery('div.portlet_wrapper').each(function(){
	
		var id = jQuery(this).attr('id')+'_'+act;
		
		var list = jQuery(this).find("ul.portlet");
		
		list.sortable({
			connectWith: 'ul.portlet',
			update : function () {

		   	var order = jQuery(this).sortable('serialize');
			jQuery.cookie(id, order,{
					expires: 355,
					path: selfurl
				});
	        }
		});
		
		var c = jQuery.cookie(id);
		if (c) {
		    jQuery.each(c.split('&'), function () {
		        var id_child = this.replace('[]=', '_');
		        jQuery('#' + id_child).appendTo(list);
		    });
		}

	});
//Декорация
	jQuery(".widget").addClass("ui-widget ui-widget-content ui-helper-clearfix ui-corner-all")
		.find(".widget-header")
			.addClass("ui-widget-header ui-corner-all")
			.prepend('<span class="ui-icon ui-icon-plusthick"></span>')
			.end()
		.find(".widget-content");
//Свистелка - сворачивалка
	jQuery(".widget-header .ui-icon").click(function(){
		jQuery(this).toggleClass("ui-icon-minusthick");
		jQuery(this).parents(".widget").children(".widget-content").toggle();
	});
//Запрет выбора
	jQuery("ul.portlet").disableSelection();
//Перемещение панелей
	function drag(id){
		jQuery('.portlet_wrapper').draggable({
			containment: '#pagewidth',
		});
	}
//неперемещение панелей	
	function undrag(id){
		jQuery('.portlet_wrapper').draggable( "destroy" );
	}

//Свернуть панель
	jQuery('div.portlet_helper span.ui-icon-carat-1-n').click(function(){
		jQuery(this).toggleClass('ui-icon-carat-1-n').toggleClass("ui-icon-carat-1-s");
		var portlet = jQuery(this).parents('div.portlet_wrapper');
		var elem = portlet.find('ul.portlet');
		var hidden =  portlet.find('ul.portlet:hidden').length;
		if (hidden == 0) {
			elem.hide(300).end().find('.portlet_helper').css('margin-bottom','0px');
			jQuery(this).parent().attr('title', 'Развернуть панель');
		}
		else{
			elem.show(300).end().find('.portlet_helper').css('margin-bottom','8px');;
			jQuery(this).parent().attr('title', 'Свернуть панель');			
		}
	});
//Свернуть все виджеты
	jQuery('div.portlet_helper span.ui-icon-minus').click(function(){
		jQuery(this).toggleClass('ui-icon-minus').toggleClass("ui-icon-newwin");
		var list = jQuery(this).parents('div.portlet_wrapper').find('li');

		var all = list.find('.widget-content').length;
		var hiddens = list.find('.widget-content:hidden').length;

		if (hiddens == 0) {
			list.find('.widget-content').hide(300).end().find('.widget-header span').addClass("ui-icon-minusthick");
			jQuery(this).parent().attr('title', 'Развернуть все виджеты');
		}
		else{
			list.find('.widget-content').show(300).end().find('.widget-header span').removeClass("ui-icon-minusthick");
			jQuery(this).parent().attr('title', 'Свернуть все виджеты');
			if(hiddens < all){
				list.end().find('.widget-header span').addClass("ui-icon-minusthick");
				jQuery(this).trigger('click');
			}			
		}
	});
	
//Переместить панель/зафиксировать. 
//Слегка глючит - одной переменной отслеживается состояние всех панелей.	
	var toggler2 = true;
	jQuery('div.portlet_helper span.ui-icon-arrow-4-diag').click(function(){
		jQuery(this).toggleClass("ui-icon-arrow-4-diag").toggleClass('ui-icon-pin-w');
		if(!toggler2){
			undrag();
			jQuery(this).parent().attr('title','Перемещение панели');
			toggler2 = true;
		}
		else{
			drag();
			jQuery(this).parent().attr('title','Зафиксировать панель');
			toggler2 = false;		
		}
	});
	
	jQuery('div.portlet_helper span.ui-icon-home').click(function(){
		jQuery(this).parents('div.portlet_wrapper').css({ left:"0", top:"0" });
	});
/**
 * @todo
 * Сохранять состояние не только положения виджетов, но и панели. 
 * Все состояния
 */	

//Конец скрипта
});
