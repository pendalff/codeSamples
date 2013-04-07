<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
if (isset($errors) AND ! empty($errors))
{
?>
<div id="errors">
<?php
	if(is_array($errors) ){
	    foreach ($errors as $field=>$val)
	    {
		$val[0] = $val[0] == 'email' ? 'email_err' : $val[0];
	?>
	        <div class="error" style="float:left; width: 300px;">
				Ошибка заполнения поля <b><?php echo __($field);?></b>
			</div>
			<div> <?php echo __($val[0]);?></div>        
	<?php
	    }    
	}
	elseif(is_string($errors) ){
	?>
	        <div class="error"> <?php echo $errors;?></div>        
	<?php		
	}
?>
</div>
<?php 
}
?>
