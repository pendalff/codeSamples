<?php defined('SYSPATH') or die('No direct script access.');?>
<h1>Запрашиваемая страница не найденна.</h1>
<h3>
<?php 
echo 
$e instanceof ReflectionException && $e->getLine() == 457 ? 
'Страница не существует' : $e->getMessage();?></h3>
<a href="#" onclick="javascript:window.history.go(-1)">
	Вернуться назад
</a>