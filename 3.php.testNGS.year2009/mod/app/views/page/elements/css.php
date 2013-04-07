<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
$attrs = "";
if( isset($src) && !empty($src)  ){
	$attrs = 'href="'.$src.'"';
}
if( isset($code) && !empty($code) ){
	
echo "<style type='text/css'>";
echo $code;
echo "</style>";

}
elseif(!empty($attrs)){
?>
<link type="text/css" <?php echo $attrs;?> rel="stylesheet"></link>
<?php
}
