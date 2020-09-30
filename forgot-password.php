<?php

require_once('includes/config.php');

if(isset($_SESSION['admin_id']))
{
	header('Location: admin');
	exit;
}
else if(MAINTENANCE_MODE)
{
	header('Location: maintenance');
	exit;
}

?>
<!DOCTYPE html>
<html class="no-focus" lang="en">
	<head>
		<title><?php echo APP_NAME; ?> | Forgot password</title>
		<?php require_once('includes/header.php'); ?>
	</head>
	<body>
		<div class="bg-white pulldown">
			<div class="content content-boxed overflow-hidden">
				<div class="row">
					<div class="col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 col-lg-4 col-lg-offset-4">
						<div class="push-30-t push-50 animated fadeIn">
							<!-- Login Title -->
							<div class="text-center">
								<?php
					
								$logo = 'assets/img/logo.png';
								
								if(is_file($logo))
								{
									?>
									<img src="<?php echo $logo; ?>" height="100" />
									<br /><br />
									<?php
								}
								else
								{
									?>
									<i class="fa fa-2x fa-shopping-basket text-primary"></i>
									<p class="text-muted push-15-t">
										<b><big><?php echo APP_NAME; ?></big></b>
									</p>
									<?php
								}
								
								?>
							</div>
							<!-- END Login Title -->

							<!-- Login Form -->
							<!-- jQuery Validation (.js-validation-login class is initialized in js/pages/base_pages_login.js) -->
							<!-- For more examples you can check out https://github.com/jzaefferer/jquery-validation -->
							<form class="form-horizontal push-30-t" action="manage" method="post">
								<div class="form-group">
									<div class="col-xs-12">
										<div class="form-material form-material-primary floating">
											<input class="form-control" type="email" autofocus required maxlength="50" name="email" id="email" />
											<label for="email">E-mail</label>
										</div>
									</div>
								</div>
								<div class="form-group push-30-t">
									<div class="col-sm-6">
										<a href="index" class="link-effect">< Back to login</a>
									</div>
									<div class="col-sm-6">
										<button class="btn btn-sm btn-block btn-primary" type="submit" name="forgot_password_form"><i class="si si-login"></i> Reset & send</button>
									</div>
								</div>
							</form>
							<!-- END Login Form -->
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>