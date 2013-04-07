<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */

?>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo SMVC::$charset;?>" />
	<title> <?php echo $page->title;?> </title>
	<meta name="keywords" content=" <?php echo $page->meta_keywords;?> " /> 
	<meta name="description" content=" <?php echo $page->meta_description;?> " /> 	

	<?php 
	echo $page->render_css();
	echo $page->render_js();
	?>
</head>
<body>
<div id="wrapper">
	<div id="content">
		
	<?php echo $page->content;?>
	</div><!-- #content-->

</div><!-- #wrapper -->


</body>