<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
?>
<div>
<div><h1>Пример форума</h1></div>
<div><ul>
	<li><?php echo HTML::anchor( URL::site('forum/list',true), 	    'Главная');?></li>
	<li><?php echo HTML::anchor( URL::site('users/register',true),  'Регистрация');?></li>
<?php if(!auth::logined()): ?>
	<li><?php echo HTML::anchor( URL::site('users/login',true),     'Вход');?></li>
<?php else: ?>
	<li><?php echo HTML::anchor( URL::site('users/logout',true),    'Выход');?></li>
	<li><?php echo HTML::anchor( URL::site('users/edit',true),      'Профиль');?></li>
	<?php if(auth::admin_role(false)): ?>
		<li><?php echo HTML::anchor( URL::site('config',true),     'Настройки');?></li>
	<?php endif; ?>
<?php endif;?>	
</ul></div>
<div style="clear:both;">
<?php
if(Auth::instance()->user!=null){
	echo "<p>Вы вошли как ".Auth::instance()->user->username."</p.";
}
?></div>
</div>