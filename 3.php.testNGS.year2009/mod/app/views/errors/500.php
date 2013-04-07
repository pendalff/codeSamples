<?php defined('SYSPATH') or die('No direct script access.');?>
<h2>Извините, произошла внутренняя ошибка сервера.</h2>
<h3><?php echo $e->getMessage();?></h3>
<a href="#" onclick="javascript:window.history.go(-1)">
	Вернуться назад
</a>