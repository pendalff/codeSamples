<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
?>
<thead>
<tr class="ui-corner-top">
<th class="th_box"><input type="checkbox"  id="check_all" /></th>
<th class="th_id">ID</th>
<th class="th_name">
  Название
</th>
<?php
if(Kohana::$environment != Kohana::PRODUCTION && 0){
?>
<th class="th_lft">
   Левый ключ
</th>
<th class="th_rgt">
   Правый ключ
</th>
<th class="th_level">
   Уровень
</th>
<?php 
} 
?>
<th class="th_level">
   Родитель
</th>

<th class="th_actions">
   Действия
</th>
</tr>
</thead>