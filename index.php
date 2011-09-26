<?php
$mail_errors_to = FALSE;

$targets = array();

/*
// Sample configuration
$targets['username/repository/refs/head/master'] = array(
		'command 1',
		'command 2'
	)
);
*/

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
		<?php if( ! $targets): ?>
		<div class="attention">
			<h2>Captain is not properly configured</h2>
			<p>It seems like Captain aint set up right, you might wanna look into that.</p>
		</div>
		<?php endif; ?>

		<p><a href="http://github.com/hakkah/Captain">Fork me</a>.</p>
	</body>
	</html>
<?php
endif;

$payload = json_decode($_POST['payload']);
$commit_path = $payload->repository->owner->name.'/'.$payload->repository->name.'/'.$payload->ref;

$errors = array();

if(isset($targets[$commit_path]))
	foreach($targets[$commit_path] as $action) {
		$output = array();
		exec($action, $output, $return_code);
		if($return_code > 0)
			$errors[] = implode("\n", $output);
	}
else
	$errors[] = "No target matching {$commit_path}";

if($errors && $mail_errors_to)
	mail($mail_errors_to, "Captain error: {$commit_path}", implode("\n", $errors));