<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
?>
<table id="main_list" class="treeTable">
<?
if(isset($header)){
	echo $header;
}
	echo $body;
	
if(isset($footer)){
	echo $footer;
}
?>
</table>
<script language="JavaScript" type="text/javascript">
jQuery(document).ready(function () {
	
//Первый, корневой элемент, менять нельзя. Дополнительная защита от дурака.
 jQuery('#ids1').bind('change', function(){
 	jQuery(this).attr( 'checked', false );
 });
 
 jQuery("#main_list").treeTable({
    treeColumn: 2,
    initialState: 'collapsed'
   //Если нужно изначально выводить свернутыми - сменить.
	// initialState: 'expanded'
  });

  //draggable nodes
  jQuery("#main_list .file, #main_list .folder").draggable({
    helper: "clone",
    opacity: .75,
    refreshPositions: true, // Performance?
    revert: "invalid",
    revertDuration: 300,
    scroll: true
  });
  //droppable rows
  jQuery("#main_list .file, #main_list .folder").each(function() {
  	
	jQuery(this).parents("tr").bind('change', function(){
		jQuery(this).find('td.node_name span').removeClass('folder').addClass('file');
		if(jQuery(this).next().hasClass('child-of-' + this.id)){
			jQuery(this).find('td.node_name span').removeClass('file').addClass('folder');
		}
	});

    jQuery(this).parents("tr").droppable({
      accept: ".file, .folder",
      drop: function(e, ui) { 

		//selftTr - текущая, перетаскиваемая, строка.
	    var selfTr = jQuery( jQuery(ui.draggable).parents("tr") );

        selfTr.appendBranchTo(this); //перемещаем
        
		//selfId - id строки, которую перетаскиваем.		
        var selfId = selfTr.attr("id").substr(5);
		//id нового родителя строки	
		var parentId = this.id.substr(5);	

		var parent_row = jQuery(this);		
				
		parent_row.trigger('change');

		var has_childrens_from_self = jQuery('#main_list tr.child-of-node-' + selfId);
		
		
		if(has_childrens_from_self.length){
			selfTr.find('td.node_name span').removeClass('file').addClass('folder');
		}
		else{
			selfTr.find('td.node_name span').removeClass('folder').addClass('file');
		}
		
        jQuery("#newparent-" + selfId).val(parentId);
		zebra();
      },
      hoverClass: "accept",
      over: function(e, ui) {
        //Что, строка еще свернута? Развернем!
        if(this.id != ui.draggable.parents("tr")[0].id && !jQuery(this).is(".expanded")) {
          jQuery(this).expand();
        }
      }
    });
  });

  //подсветим строки
  jQuery("table#main_list tbody tr").mousedown(function() {
    jQuery("tr.selected").removeClass("selected"); // Deselect currently selected rows
    jQuery(this).addClass("selected");
  });

  //клик по заголовку - это тоже подсветка строки
  jQuery("table#main_list tbody tr span").mousedown(function() {
    jQuery(jQuery(this).parents("tr")[0]).trigger("mousedown");
  });
  
  //раскраска строк
  function zebra(){
	 jQuery('table#main_list tbody tr:odd').css('background', 'transparent');
	  jQuery('table#main_list tbody tr:even').css('background', '#e7e7e7');
  }
  zebra();
  //подсветка онных же. раскомментить, если надо.
  /*
  jQuery('table#main_list tbody tr').hover(
  function(){
	jQuery(this).addClass('ui-state-highlight');
  },
  function(){
	jQuery(this).removeClass('ui-state-highlight');
  });
	*/
});
</script>