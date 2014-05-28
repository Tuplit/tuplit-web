<?php
	require_once('includes/CommonIncludes.php');
	ob_start();
	session_start();
	session_destroy();
	//destroyCookies();
	$expire = strtotime("+30 days");
	setcookie('tuplit_merchant_logout', 'logout', $expire);
	header("location:Login.php");	
    die();
?>