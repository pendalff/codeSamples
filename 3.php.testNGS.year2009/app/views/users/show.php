<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */

$user->roles_names = array();

if(count($user->roles)==0){
	$user->roles[]=9999;
}
foreach($user->roles AS $role){
	$role_name=$user->all_roles[$role];
	$user->roles_names[$role] = $role_name->description;
}


?>
<div id="user">
<div class="left">Имя:</div>
<div class="right"><?php echo $user->username;?></div>

<div class="left">Email:</div>
<div class="right"><?php echo HTML::mailto($user->email);?></div>

<div class="left">Права:</div>
<div class="right"><?php echo implode(", ", $user->roles_names);?></div>

<div class="left">Сообщений в форуме:</div>
<div class="right"><?php echo $user->count_posts." ( ".HTML::anchor( URL::site('users/posts/'.$user->id, true), "Читать")." )";?></div>
</div>
