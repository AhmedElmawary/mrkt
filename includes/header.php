<?php

require_once('includes/config.php');

 $url_en ="http://". $_SERVER['HTTP_HOST'] .'/mrkt/';

?>
<link rel="icon" type="image/ico" href="//assets/img/favicon.ico" />

<meta name="application-name" content="<?php echo APP_NAME; ?>" />
<meta http-equiv="content-type" content="text/html" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta charset="utf-8" />

<meta name="twitter:card" content="summary" />
<meta property="og:type" content="website" />
<meta property="og:title" content="<?php echo APP_NAME; ?>" />
<meta property="og:url" content="<?php echo $url_en; ?>" />
<meta property="og:image" content="<?php echo CMS_BASE; ?>assets/img/logo.png" />

<base href="<?php echo CMS_BASE; ?>" />
<link rel="canonical" href="<?php echo $url_en; ?>" />
<link rel="alternate" hreflang="en" href="<?php echo $url_en; ?>" />

<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400italic,600,700%7COpen+Sans:300,400,400italic,600,700">
<link rel="stylesheet" type="text/css" href="assets/OneUI/src/assets/css/bootstrap.min.css" />
<link rel="stylesheet" type="text/css" href="assets/OneUI/src/assets/css/oneui.css" />

<script defer src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
<script defer src="assets/OneUI/src/assets/js/core/bootstrap.min.js"></script>
<script defer src="assets/OneUI/src/assets/js/core/jquery.slimscroll.min.js"></script>
<script defer src="assets/OneUI/src/assets/js/core/jquery.scrollLock.min.js"></script>
<script defer src="assets/OneUI/src/assets/js/core/jquery.appear.min.js"></script>
<script defer src="assets/OneUI/src/assets/js/core/jquery.countTo.min.js"></script>
<script defer src="assets/OneUI/src/assets/js/core/jquery.placeholder.min.js"></script>
<script defer src="assets/OneUI/src/assets/js/core/js.cookie.min.js"></script>
<script defer src="assets/OneUI/src/assets/js/app.js"></script>