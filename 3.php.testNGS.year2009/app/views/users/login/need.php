<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */

?>
<div id="user">
<p>Для выбранного действия необходимо войти в систему.</p>
<?php if(isset($b)) echo "<p>Вы были забаннены.</p>";?>
<div><ul>
	<li><?php echo HTML::anchor( URL::site('users/register',true),  'Регистрация');?></li>
	<li><?php echo HTML::anchor( URL::site('users/login',true),     'Вход');?></li>
</ul></div>
</div>
