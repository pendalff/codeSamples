<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
$attrs = "";
if( isset($src) && !empty($src)  ){
	$attrs = 'src="'.$src.'"';
}
$content = "";
if( isset($code) && !empty($code) ){
	$content = $code;
}
if(!empty($content) || !empty($attrs)){
?>
<script type="text/javascript" <?php echo $attrs;?>><?php echo $content;?></script>
<?php
}
