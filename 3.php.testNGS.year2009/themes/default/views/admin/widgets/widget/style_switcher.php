<div class="switcher_wrapper">
	<span>Оформление:</span>
	<?php
		echo Form::select('style_switcher', $themes, $theme, array('id' =>'style_switcher', 'class' => 'ui-widget ui-state-default'));
	?>
</div>   
<script type="text/javascript">
    jQuery(document).ready(function(){
        var theme = '<?php echo $theme;?>';
        $("#style_switcher").val(theme).change(function(){
            var theme = $(this).val();
            if(theme) {
                jQuery.cookie('theme',theme,{
					expires: 355,
					path: '/administrator'
				});
                window.location.reload();
            }
        });
    });
</script>