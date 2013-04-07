<?php defined('SYSPATH') or die('No direct script access.');

?>
<dl id="system-message">
<dt class="<?php echo $type;?>">Сообщение</dt>
<dd class="<?php echo $type;?>">
	<ul>
		<?php
		foreach ($data as $message):
		?><li><?php echo $message;?></li>
		<? endforeach;?>
	</ul>
</dd>
</dl>
