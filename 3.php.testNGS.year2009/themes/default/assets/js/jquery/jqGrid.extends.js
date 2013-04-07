/**
 * @author sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
jQuery.extend(jQuery.fn.fmatter , {
    boolimage : function(cellvalue, options, rowObject){
   	if(cellvalue==1){
   		return '<img border=\"0\" width=\"16\" height=\"16\" alt=\"Да\" src=\"images/publish_g.png\">';
   	}
	else{
		return '<img border=\"0\" width=\"16\" height=\"16\" alt=\"Нет\" src=\"images/publish_x.png\">';
	}
}
})(jQuery);
jQuery.extend(jQuery.fn.fmatter.boolimage , {
    unformat : function(cellvalue, options, rowObject) {
	var one = rowObject.find('img').attr('src')=="images/publish_g.png";
	if(one) return "1";
	return "0";
}
})(jQuery);