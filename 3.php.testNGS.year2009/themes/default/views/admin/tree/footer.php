<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
$colspan = 6;
if(Kohana::$environment != Kohana::PRODUCTION){
	$colspan = 8;
}
?>
<tfoot>
<tr  class="ui-corner-bottom">
<td class="tree_footer" colspan="<?=$colspan;?>">
	<? echo $body;?>
</td>
</tr>
</tfoot>