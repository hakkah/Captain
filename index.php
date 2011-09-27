<?php
if(file_exists('config.php'))
	$config = require('config.php');
else {
	$errors[] = 'Not configured - config.php is missing';
	$config = array();
}

$targets = isset($config['targets']) ? $config['targets'] : array();

if( ! isset($_POST['payload'])): ?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

		<title>Captain</title>
		<style type="text/css" media="screen">
			body {
				font-family: Georgia, Times, serif;
				line-height: 20px;
				font-size: 16px;
				margin: 100px;
			}
			
			h1 {
				font-weight: normal;
				font-size: 3em;
			}
			
			h2 {
				margin-top: 0;
				font-weight: normal;
			}
			
			p {
				margin-bottom: 0;
			}
			
			form {
				margin-top: 18px;
			}
			
			input[type=text] {
				padding: 3px;
				display: block;
				width: 600px;
				font-size: 16px;
				border: 1px solid #ccc;
			}
			
			input[type=text].action {
				width: 582px;
			}
			
			label {
				color: #666;
				margin-top: 18px;
				display: block;
			}
			
			.action, input[type=text].action {
				margin-left: 18px;
				font-size: 12px;
				margin-top: 2px;
			}
			
			input[type=text].action {
				margin-bottom: -1px;
				margin-top: 0;
			}
			
			.attention {
				background-color: #FFFF88;
				padding: 18px;
				position: relative;
				left: -18px;
				margin-top: 18px;
			}
		</style>
	</head>

	<body>
		<h1>Captain</h1>
		<p>Taking care of GitHub web hooks, so you don't have to.</p>
		<?php if( ! $config): ?>
		<div class="attention">
			<h2>Captain is not properly configured</h2>
			<p>It seems like Captain aint set up right, you might wanna look into that.</p>
		</div>
		<?php endif; ?>
		<?php if($targets): ?>
			<form method="post">
				<?php foreach($targets as $target => $actions): ?>
					<label>When username/repository/ref matches...</label>
					<input class="target" type="text" name="targets[<?php echo $target; ?>]" value="<?php echo $target; ?>"/>
					<label class="action">...then this happens:</label>
					<?php foreach($actions as $action): ?>
						<input class="action" type="text" name="targets[<?php echo $target; ?>][]" value="<?php echo $action; ?>"/>
					<?php endforeach; ?>
						<input class="action" type="text" name="targets[<?php echo $target; ?>][]" value=""/>
				<?php endforeach; ?>
					<label>Add a new target matching:</label>
					<input class="target" type="text" name="new_target" value=""/>
			<input type="submit" value="Save" />
			</form>
		<?php endif; ?>
		<p>By <a href="http://github.com/intedinmamma">intedinmamma</a>.</p>
	</body>
	</html>
<?php
endif;

function save_config(Array $config, $filename = 'config.php') {
	$config = var_export($config, TRUE);
	file_put_contents($filename, "<?php\n\$config = {$config};\nreturn \$config;");
}

if(isset($_POST['targets'])) {
	$new_targets = array();
	foreach($_POST['targets'] as $target => $actions)
		if($target)
			$new_targets[$target] = array_filter($_POST['targets'][$target]);
	
	if($_POST['new_target'])
		$new_targets[$_POST['new_target']] = array();
	
	$config['targets'] = $new_targets;
	save_config($config);
	echo "Saved targets!";
}
die();
$payload = json_decode($_POST['payload']);
$commit_path = $payload->repository->owner->name.'/'.$payload->repository->name.'/'.$payload->ref;

$errors = array();

if(isset($targets[$commit_path])) {
	foreach($targets[$commit_path] as $action) {
		$output = array();
		exec($action, $output, $return_code);
		if($return_code > 0)
			$errors[] = implode("\n", $output);
	}
} else {
	$errors[] = "No target matching {$commit_path}";
}

if( ! isset($config['admin_emails'])) {
	$errors[] = '$config[\'admin_emails\'] has not been set.';
	$config['admin_emails'] = 'root@localhost';
}

if($errors && $config['admin_emails'])
	mail($config['admin_emails'], "Captain error: {$commit_path}", implode("\n", $errors));