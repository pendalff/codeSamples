<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */

$registry = Registry::instance();
$path     = $registry->get('path');

$del_url =URL::site('admin_orgs/remove_image/'.$id);

$url = JURI::root();
$url = rtrim($url, "/");
$image_url = $url."/".$path."/".$id."/middle/";
$image_url_large = $url."/".$path."/".$id."/large/";

$middle_path = JPATH_ROOT.DS.$path.DS.$id.DS."middle/";

$file_list = glob( $middle_path."*.jpg" );

if(count($file_list)){
	foreach ($file_list as $k => $file) {
		$file_list[$k] = array_pop(explode( DIRECTORY_SEPARATOR, $file ));
	}
}

$cooks = $_COOKIE;
foreach ($cooks as $key => $cook) {
	if(is_array( $cook)){
		unset($cooks[$key]);
	}
}
$cooks =  str_replace( "'","" , json_encode ($cooks));
?>
<script type="text/javascript">
jQuery(document).ready(function() {
jQuery('#list a').lightBox();

	function centered(){
		jQuery('ul#list li a img').each(function(){
			var elem   = jQuery(this);
			var parent = elem.parent().parent().parent();
			var top	   =  parseInt (( parent.height() - elem.height()) /2);
			elem.css('margin-top', top+'px');	
			//console.info(parent);
		});
	}
	centered();
	function appendList(file){
		jQuery('ul#list li:first').remove();
		var url = "<?=$image_url;?>";
		var large_url = "<?=$image_url_large;?>";
		var fileurl = url+file+'?'+new Math.random();
		var li = jQuery("<li><span class='delete' title='удалить этот рисунок'></span><div class='act'><a href='"+large_url+file+"'><img src='"+fileurl+"'/></a></div></li>");
		li.appendTo('ul#list');
		setTimeout(function(){  clickeble_delete(); centered(); jQuery('#list a').lightBox();}, 200);
	}

	function onComp(event, ID, fileObj, response, data){ 

		try {
			var ccc = eval("(" + response + ")");
			console.log(ccc);
			if (ccc.err.length == 0) {
				appendList(ccc.file);
				message(ccc.mess, "Успеx!");
			}
			else {
				var mess = " ";
				var errors = ccc.err;
				for (var i = 0; i < errors.length; i++) {
					mess += errors[i] + "<br/>";
				};
				message(mess, "Ошибка добавления фото!");
			}

		}catch (e){}
	}
	
function removeList(file){
	var img = jQuery("img[src='"+file+"']");
	img.parents('li:first').remove();
}	
	
function clickeble_delete(){
	var elements = jQuery('ul#list span.delete');
		elements.addClass('ui-icon ui-icon-trash');
		elements.each(function(){
			var el = jQuery(this);
			el.unbind('click');
			el.click(function(){
				if(confirm("Вы действительно хотите удалить эту картинку?")){
					var filename = el.parent().find('img').attr('src');
					file = filename.split("/").pop();
					file = file.split("?").shift();					
					var data = { filename: file };
					//(url, data, callback, type)
					jQuery.post('<?=$del_url;?>', data, function(thedata){
						//	console.info(thedata);
							var errors = thedata.err;
						//	console.info(errors);
							if(errors.length == 0){
							  removeList(filename);
							  message(thedata.mess,"Удалено!");
							}
							else{
						   		var mess = " ";
								var errors = ccc.err;
						   		for (var i = 0; i < errors.length; i++){
									mess += errors[i]+"<br/>";
								}
						   		message(mess,"Ошибка удаления фото!");								
							}
					}, 'json');	
				}
			});
		});
}
clickeble_delete();

	jQuery("#uploadify").uploadify({
		"uploader"       : "<? echo Core_actions::link_static ('js/upload/uploadify.swf') ?>",
		"script"         : "<?=URL::site('admin_orgs/upload/'.$id)?>",
		'scriptData': { 'cook': '<? echo $cooks; ?>', 'session': '<?=@session_id ();?>','kourl':'admin_orgs/upload/<?=$id;?>'},
		"cancelImg"      : "<? echo Core_actions::link_static ('img/cancel.png')?>",
		"folder"         : "upload",
		"queueID"        : "fileQueue",
		"auto"           : true,
		"buttonText"     : "ADD NEW",
		"multi"          : true,
		"onComplete"     : onComp
		/*
		,"onError": function (event,ID,fileObj,errorObj) {
     	 	console.log(errorObj);
   		 }	
   		 */
	});


});
</script>

<div class="photoset ui-corner-all ui-widget-content">
	<div id="uploader" class="ui-corner-all">
		<div id="file"><input type="file" name="uploadify" id="uploadify" /></div>
		<p><a href="javascript:jQuery('#uploadify').uploadifyClearQueue()">Отменить все загрузки</a></p>
		<div id="fileQueue"></div>
	</div>
	<div id="photolist">
		<ul id="list">
<?php
if(count($file_list) && is_array($file_list)){
	foreach ($file_list as $img) {
		//<span class='rotater' title='вращать вправо на 90 градусов'></span>
		echo "<li><span class='delete' title='удалить этот рисунок'></span><div class='act'><a href='{$image_url_large}{$img}'><img src='".$image_url.$img."'/></a></div></li>";
	}
}
?>			
		</ul>
	</div>
</div>