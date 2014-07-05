<?php
/**
 * configuration variables
 *
 * This file has constants and global variable used throughout the application.
 *
 */
 
define("TITLE","Tuplit");
if (isset($_SERVER['HTTPS']) && ($_SERVER["HTTPS"] == 'on' ) )
	$site = 'https://';
else
	$site = 'http://';

if($_SERVER['SERVER_ADDR']=='172.21.4.104')
{
	define('BASE_URL',$site.$_SERVER['HTTP_HOST']);
	define('MERCHANT_SITE_PATH',  $site.$_SERVER['HTTP_HOST'].'/tuplit/merchant');
	define('MERCHANT_ABS_PATH',  'C:/wamp/www/tuplit/merchant');
	define('SITE_PATH',  $site.$_SERVER['HTTP_HOST'].'/tuplit/merchant');
	define('ABS_PATH',  'C:/wamp/www/tuplit/merchant');
	define('SERVER',  0);
	define('WEB_SERVICE',   $site.$_SERVER['HTTP_HOST'].'/tuplit/');
}
else
{
	define('BASE_URL',$site.$_SERVER['HTTP_HOST']);
	define('MERCHANT_SITE_PATH',  $site.$_SERVER['HTTP_HOST'].'/merchant');
	define('MERCHANT_ABS_PATH',  '/var/www/html/merchant');
	define('SITE_PATH',  $site.$_SERVER['HTTP_HOST'].'/merchant');
	define('ABS_PATH',  '/var/www/html/merchant');
	define('SERVER',  1);
	define('WEB_SERVICE',  $site.$_SERVER['HTTP_HOST'].'/');
}

define('CLIENT_ID','00d3f72dc78e12e212a326699555252ca868d2ed');
define('CLIENT_SECRET','c72720dc12f4350f4b93fb6f3288b7eb129b6fbc');
define('ENCODE_KEY', 'tuplit');

if ($_SERVER['HTTP_HOST'] == '172.21.4.104'){
	define('BUCKET_NAME','localtuplit');
}
else{
	define('BUCKET_NAME','tuplit');
}

//script ans style path
define('SITE_TITLE', 'Tuplit&nbsp;Merchant&nbsp;Portal');
define('MERCHANT_SCRIPT_PATH', MERCHANT_SITE_PATH.'/webresources/js/');
define('MERCHANT_STYLE_PATH', MERCHANT_SITE_PATH.'/webresources/css/');
define('MERCHANT_IMAGE_PATH', MERCHANT_SITE_PATH.'/webresources/images/');
define('MERCHANT_UPLOAD_PATH', MERCHANT_SITE_PATH.'/webresources/uploads/');


define('TEMP_IMAGE_PATH', MERCHANT_SITE_PATH.'/webresources/uploads/temp/');	
define('TEMP_IMAGE_PATH_REL', MERCHANT_ABS_PATH.'/webresources/uploads/temp/');	

define('REGION','us-west-2');

if($_SERVER['HTTP_HOST']=='172.21.4.104')
{
	define('SITE_PATH_UPLOAD',MERCHANT_SITE_PATH.'/webresources/uploads/');
	define('ABS_PATH_UPLOAD',MERCHANT_ABS_PATH.'/webresources/uploads/');
}
else{
	define('SITE_PATH_UPLOAD','http://'.BUCKET_NAME.'.s3.amazonaws.com/');
	define('ABS_PATH_UPLOAD','http://'.BUCKET_NAME.'.s3.amazonaws.com/');
}

define('MERCHANT_ICONS_IMAGE_PATH', SITE_PATH_UPLOAD.'merchants/icons/');	
define('MERCHANT_ICONS_IMAGE_PATH_REL', ABS_PATH_UPLOAD.'merchants/icons/');

define('MERCHANT_COVER_IMAGE_PATH', SITE_PATH_UPLOAD.'merchants/');	
define('MERCHANT_COVER_IMAGE_PATH_REL', ABS_PATH_UPLOAD.'merchants/');

define('MERCHANT_SITE_IMAGE_PATH',MERCHANT_SITE_PATH.'/webresources/images/');	
define('MERCHANT_SITE_IMAGE_PATH_REL', MERCHANT_SITE_PATH.'/webresources/images/');

define('USER_IMAGE_PATH',SITE_PATH_UPLOAD.'users/');	
define('USER_IMAGE_PATH_REL', ABS_PATH_UPLOAD.'users/');

define('USER_THUMB_IMAGE_PATH', SITE_PATH_UPLOAD.'users/thumbnail/');	
define('USER_THUMB_IMAGE_PATH_REL', ABS_PATH_UPLOAD.'users/thumbnail/');

define('PRODUCT_IMAGE_PATH',SITE_PATH_UPLOAD.'products/');	
define('PRODUCT_IMAGE_PATH_REL', ABS_PATH_UPLOAD.'products/');

define('LIMIT',100);
define('PERPAGE',25);

define('MERCHANT_PER_PAGE_LIMIT', 10);


//Encrypt word
define('ENCRYPTSALT',      'saltisgood');
global $MERCHANT_per_page_array;
$MERCHANT_per_page_array = array(10,50,100,200,250);
define('MERCHANT_PER_PAGE_ARRAY', 'return ' . var_export($MERCHANT_per_page_array, 1) . ';');//define constant array
global $methodArray;
$methodArray = array('POST','DELETE','GET','PUT');

global $platformArray;
$platformArray = array('0'=>'Web','1'=>'ios','2'=>'Android');

global $discountTierArray;
$discountTierArray = array('1'=>'10','2'=>'20','3'=>'30','4'=>'40','5'=>'50');

global $itemTypeArray;
$itemTypeArray = array('1'=>'Regular','2'=>'Deal','3'=>'Special');

global $orderStatusArray;
$orderStatusArray = array('1'=>'New','2'=>'Accepted','3'=>'Rejected');

global $days_array;
$days_array = array('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');

global $BusinessType;
$BusinessType = array('1'=>'Restaurant','2'=>'Foods','3'=>'Drinks');

global $HowyouHeared;
$HowyouHeared = array('1'=>'Friends','2'=>'Websites','3'=>'News','4'=>'Advertisement');

global $userLoadMore;
$userLoadMore	= 12;

global $admin_hours_array;
for($min=1;$min<=12;$min++) {
	$hourtemp	=	(string)$min;
	if(strlen($hourtemp) == 2)
		$admin_hours_array[$hourtemp]	=	$hourtemp;
	else {
		$hourtemp = '0'.$hourtemp;
		$admin_hours_array[$hourtemp]	=	$hourtemp;
	}		
}

global $admin_minute_array;
for($min=0;$min<60;$min++) {
	$temp	=	(string)$min;
	if(strlen($temp) == 2)
		$admin_minute_array[$temp]	=	$temp;
	else {
		$temp = '0'.$temp;
		$admin_minute_array[$temp]	=	$temp;
	}		
}

global $admin_ampm_array;
$admin_ampm_array = array(
						'AM'=>'AM',
						'PM'=>'PM'
						);

global $applicationARN;
if($_SERVER['HTTP_HOST'] == '172.21.4.104') {
	$applicationARN	=	array(
							'ios' 		=> 	'arn:aws:sns:us-west-2:365095979756:app/APNS_SANDBOX/tuplit_Sandbox',
							'android'	=>	'arn:aws:sns:us-west-2:365095979756:app/GCM/tuplit-Android'
						);
}
else{
	$applicationARN	=	array(
							'ios' 		=> 	'arn:aws:sns:us-west-2:365095979756:app/APNS/tuplit',
							'android'	=>	'arn:aws:sns:us-west-2:365095979756:app/GCM/tuplit-Android'
						);
}
global $order_status_array;
$order_status_array = array('0'=>'New','1'=>'Accepted','2'=>'Rejected');

global $countryArray;
$countryArray = array('US','UK','AU','GE','CA','FR');

global $currencyArray;
$currencyArray = array('USD','AUD','EUR','CAD');
?>
