<?php

require_once('includes/config.php');

if(!MAINTENANCE_MODE)
{
	header('Location: index');
	exit;
}

?>
<!DOCTYPE html>
<html class="no-focus" lang="en">
	<head>
		<title><?php echo APP_NAME; ?> | Maintenance</title>
		<?php require_once('includes/header.php'); ?>
	</head>
	<body>
		<!-- Error Content -->
		<div class="content bg-white text-center pulldown overflow-hidden">
			<div class="row">
				<div class="col-sm-6 col-sm-offset-3">
					<!-- Error Titles -->
					<h1 class="font-s128 font-w300 text-smooth animated rollIn">503</h1>
					<h2 class="h3 font-w300 push-50 animated fadeInUp"><?php echo MAINTENANCE_TEXT; ?></h2>
					<!-- END Error Titles -->
				</div>
			</div>
		</div>
		<!-- END Error Content -->

		<!-- Error Footer -->
		<div class="content pulldown text-muted text-center">
			<a class="link-effect" href="index">Try again</a>
		</div>
		<!-- END Error Footer -->
	</body>
</html>