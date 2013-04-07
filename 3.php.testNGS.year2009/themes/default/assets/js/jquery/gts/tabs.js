/**
 * Набор всего-всего для работоспособности портлет-набора виджетов.
 * @author Pendalff
 * @authorurl http://pendalff.ru
 */
jQuery(document).ready(function() {
	jQuery("div.tabs").tabs({
		ajaxOptions: {
			error: function(xhr, status, index, anchor) {
			
				var mess = "<p> К сожалению не удалось загрузить требуемую вкладку.</p>";
				var title = "Ошибка!";
				message(mess,title,'ui-state-error');
			}
		},
		cache: true
		//,event: 'mouseover'
	});
});
