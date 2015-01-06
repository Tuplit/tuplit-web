<?php
if (isset($_SERVER['HTTPS']) && ($_SERVER["HTTPS"] == 'on' ) )
	$site = 'https://';
elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on')
   $site = 'https://';
else
	$site = 'http://';

if($_SERVER ['HTTP_HOST'] == '172.21.4.104'){
	define('SITE_PATH',  $site.$_SERVER['HTTP_HOST'].'/tuplit/');
	define('API_PATH',  SITE_PATH);
}
else{
	define('SITE_PATH',  $site.$_SERVER['HTTP_HOST'].'/');
	define('API_PATH',  'http://www.tuplit.com');
}
if(strstr(SITE_PATH,'elasticbeanstalk')){
	header("Location: ".API_PATH);
	die();
}
header("Location: ".API_PATH);
die();
?>