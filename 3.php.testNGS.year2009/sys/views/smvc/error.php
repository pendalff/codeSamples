<?php

// Unique error identifier
$error_id = uniqid('error');

?>
<style type="text/css">
#error { 
    border: 2px solid gray;
text-align: left;  
}
#error h1,
#error h2 { 
margin: 0; 
padding: 1em; 
font-size: 1em; font-weight: bold; background: gray; color: #fff; }
	#error h1 a,
	#error h2 a { color: #fff; }
#error h2 { background: #222; }
#error h3 { margin: 0; padding: 0.4em 0 0; font-size: 1em; font-weight: normal; }
#error p { margin: 0; padding: 0.2em 0; }
#error a { color: #1b323b; }
#error pre { overflow: auto; white-space: pre-wrap; }
#error table { width: 100%; display: block; margin: 0 0 0.4em; padding: 0; border-collapse: collapse; background: #fff; }
	#error table td { border: solid 1px #ddd; text-align: left; vertical-align: top; padding: 0.4em; }
#error div.content { padding: 0.4em 1em 1em; overflow: hidden; }
#error pre.source { margin: 0 0 1em; padding: 0.4em; background: #fff; border: dotted 1px #b7c680; line-height: 1.2em; }
	#error pre.source span.line { display: block; }
	#error pre.source span.highlight { background: #f0eb96; }
		#error pre.source span.line span.number { color: #666; }
#error ol.trace { display: block; margin: 0 0 0 2em; padding: 0; list-style: decimal; }
	#error ol.trace li { margin: 0; padding: 0; }
</style>
<div id="error">
	<h1><span class="type"><?php echo $type ?> [ <?php echo $code ?> ]:</span> <span class="message"><?php echo $message ?></span></h1>
	<div id="<?php echo $error_id ?>" class="content">
		<p><span class="file"><?php echo SMVC::debug_path($file) ?> [ <?php echo $line ?> ]</span></p>
		<?php echo SMVC::debug_source($file, $line) ?>
		<p> Trace: </p>
		<ol class="trace">
		<?php foreach (SMVC::trace($trace) as $i => $step): ?>
			<li>
				<p>
					<span class="file">
						<?php if ($step['file']): $source_id = $error_id.'source'.$i; ?>
							<?php echo SMVC::debug_path($step['file']) ?> [ <?php echo $step['line'] ?> ]
						<?php else: ?>
							{<?php echo __('PHP internal call') ?>}
						<?php endif ?>
					</span>
					&raquo;
					<?php echo $step['function'] ?>(<?php if ($step['args']): $args_id = $error_id.'args'.$i; ?><?php echo __('arguments') ?><?php endif ?>)
				</p>
				<?php if (isset($args_id)): ?>
				<div id="<?php echo $args_id ?>" class="collapsed">
					<table cellspacing="0">
					<?php foreach ($step['args'] as $name => $arg): ?>
						<tr>
							<td><code><?php echo $name ?></code></td>
							<td><pre><?php echo SMVC::dump($arg) ?></pre></td>
						</tr>
					<?php endforeach ?>
					</table>
				</div>
				<?php endif ?>
				<?php if (isset($source_id)): ?>
					<pre id="<?php echo $source_id ?>" class="source collapsed"><code><?php echo $step['source'] ?></code></pre>
				<?php endif ?>
			</li>
			<?php unset($args_id, $source_id); ?>
		<?php endforeach ?>
		</ol>
	</div>
<?php if(0): ?>
	<h2>
	<a href="#<?php echo $env_id = $error_id.'environment' ?>" onclick="return koggle('<?php echo $env_id ?>')"><?php echo __('Environment') ?></a></h2>
	<div id="<?php echo $env_id ?>" class="content collapsed">
		<?php $included = get_included_files() ?>
		<h3><a href="#<?php echo $env_id = $error_id.'environment_included' ?>" onclick="return koggle('<?php echo $env_id ?>')"><?php echo __('Included files') ?></a> (<?php echo count($included) ?>)</h3>
		<div id="<?php echo $env_id ?>" class="collapsed">
			<table cellspacing="0">
				<?php foreach ($included as $file): ?>
				<tr>
					<td><code><?php echo SMVC::debug_path($file) ?></code></td>
				</tr>
				<?php endforeach ?>
			</table>
		</div>
		<?php $included = get_loaded_extensions() ?>
		<h3><a href="#<?php echo $env_id = $error_id.'environment_loaded' ?>" onclick="return koggle('<?php echo $env_id ?>')"><?php echo __('Loaded extensions') ?></a> (<?php echo count($included) ?>)</h3>
		<div id="<?php echo $env_id ?>" class="collapsed">
			<table cellspacing="0">
				<?php foreach ($included as $file): ?>
				<tr>
					<td><code><?php echo SMVC::debug_path($file) ?></code></td>
				</tr>
				<?php endforeach ?>
			</table>
		</div>
<?php endif;?>

		<?php foreach (array('_SESSION', '_GET', '_POST', '_FILES', '_COOKIE') as $var): ?>
		<?php if (empty($GLOBALS[$var]) OR ! is_array($GLOBALS[$var])) continue ?>
		<h3><a href="#<?php echo $env_id = $error_id.'environment'.strtolower($var) ?>" onclick="return koggle('<?php echo $env_id ?>')">$<?php echo $var ?></a></h3>
		<div id="<?php echo $env_id ?>" class="collapsed">
			<table cellspacing="0">
				<?php foreach ($GLOBALS[$var] as $key => $value): ?>
				<tr>
					<td><code><?php echo $key ?></code></td>
					<td><pre><?php echo SMVC::dump($value) ?></pre></td>
				</tr>
				<?php endforeach ?>
			</table>
		</div>
		<?php endforeach ?>
	</div>
</div>
