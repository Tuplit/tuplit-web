<?php
/**
 * configuration variables
 *
 * This file has constants and global variable used throughout the application.
 *
 */
define("TITLE","tuplit");
if (isset($_SERVER['HTTPS']) && ($_SERVER["HTTPS"] == 'on' ) )
	$site = 'https://';
else
	$site = 'http://';

if($_SERVER['SERVER_ADDR']=='172.21.4.104')
{
	define('BASE_URL',$site.$_SERVER['HTTP_HOST']);
	define('ADMIN_SITE_PATH',  $site.$_SERVER['HTTP_HOST'].'/tuplit/admin');
	define('ADMIN_ABS_PATH',  'C:/wamp/www/tuplit/admin');
	define('SITE_PATH',  $site.$_SERVER['HTTP_HOST'].'/tuplit');
	define('ABS_PATH',  'C:/wamp/www/tuplit');
	define('SERVER',  0);
	define('WEB_SERVICE',   $site.$_SERVER['HTTP_HOST'].'/tuplit/');
}
else
{
	define('BASE_URL',$site.$_SERVER['HTTP_HOST']);
	define('ADMIN_SITE_PATH',  $site.$_SERVER['HTTP_HOST'].'/admin');
	define('ADMIN_ABS_PATH',  '/var/www/html/admin');
	define('SITE_PATH',  $site.$_SERVER['HTTP_HOST']);
	define('ABS_PATH',  '/var/www/html');
	define('SERVER',  1);
	define('WEB_SERVICE',  $site.$_SERVER['HTTP_HOST'].'/');
}
if ($_SERVER['HTTP_HOST'] == '172.21.4.104'){
	define('BUCKET_NAME','localtuplit');
}
else{
	define('BUCKET_NAME','tuplit');
}
//dl2k2hrieev.cloudfront.net
//script ans style path
define('SITE_TITLE', 'tuplit');
define('ADMIN_SCRIPT_PATH', ADMIN_SITE_PATH.'/webresources/js/');
define('ADMIN_STYLE_PATH', ADMIN_SITE_PATH.'/webresources/css/');
define('ADMIN_IMAGE_PATH', ADMIN_SITE_PATH.'/webresources/images/');
define('ADMIN_UPLOAD_PATH', ADMIN_SITE_PATH.'/webresources/uploads/');

//Images related constants
define('UPLOAD_USER_PATH_REL', ABS_PATH.'/admin/webresources/uploads/users/');
define('UPLOAD_USER_THUMB_PATH_REL', ABS_PATH.'/admin/webresources/uploads/users/thumbnail/');

define('TEMP_USER_IMAGE_PATH', SITE_PATH.'/admin/webresources/uploads/temp/');	
define('TEMP_USER_IMAGE_PATH_REL', ABS_PATH.'/admin/webresources/uploads/temp/');	

define('UPLOAD_CATEGORY_PATH_REL', ABS_PATH.'/admin/webresources/uploads/category/');
define('UPLOAD_CATEGORY_THUMB_PATH_REL', ABS_PATH.'/admin/webresources/uploads/category/thumbnail/');

define('UPLOAD_SLIDER_PATH_REL', ABS_PATH.'/admin/webresources/uploads/sliderImages/');
define('UPLOAD_SLIDER_THUMB_PATH_REL', ABS_PATH.'/admin/webresources/uploads/sliderImages/thumbnail/');

define('UPLOAD_MERCHANT_IMAGE_PATH_REL', ABS_PATH.'/merchant/webresources/uploads/merchants/');
define('UPLOAD_MERCHANT_ICONS_IMAGE_PATH_REL', ABS_PATH.'/merchant/webresources/uploads/merchants/icons/');

define('UPLOAD_PRODUCT_IMAGE_PATH_REL', ABS_PATH.'/merchant/webresources/uploads/products/');


define('REGION','us-west-2');

if($_SERVER['HTTP_HOST']=='172.21.4.104')
{
	//Images related constants
	// common path 
	//admin
	define('SITE_PATH_UPLOAD',SITE_PATH.'/admin/webresources/uploads/');
	define('ABS_PATH_UPLOAD',ABS_PATH.'/admin/webresources/uploads/');
	//merchant
	define('MERCHANT_SITE_PATH_UPLOAD',SITE_PATH.'/merchant/webresources/uploads/');
	define('MERCHANT_ABS_PATH_UPLOAD',ABS_PATH.'/merchant/webresources/uploads/');
}
else{
	// common path
	//admin
	define('SITE_PATH_UPLOAD','http://'.BUCKET_NAME.'.s3.amazonaws.com/');
	define('ABS_PATH_UPLOAD','http://'.BUCKET_NAME.'.s3.amazonaws.com/');
	//merchant
	define('MERCHANT_SITE_PATH_UPLOAD','http://'.BUCKET_NAME.'.s3.amazonaws.com/');
	define('MERCHANT_ABS_PATH_UPLOAD','http://'.BUCKET_NAME.'.s3.amazonaws.com/');
}
define('MERCHANT_SITE_IMAGE_PATH',SITE_PATH.'/merchant/webresources/images/');	
define('MERCHANT_SITE_IMAGE_PATH_REL', SITE_PATH.'/merchant/webresources/images/');

define('USER_IMAGE_PATH',SITE_PATH_UPLOAD.'users/');	
define('USER_IMAGE_PATH_REL', ABS_PATH_UPLOAD.'users/');

define('USER_THUMB_IMAGE_PATH', SITE_PATH_UPLOAD.'users/thumbnail/');	
define('USER_THUMB_IMAGE_PATH_REL', ABS_PATH_UPLOAD.'users/thumbnail/');

define('CATEGORY_IMAGE_PATH',SITE_PATH_UPLOAD.'category/');	
define('CATEGORY_IMAGE_PATH_REL', ABS_PATH_UPLOAD.'category/');

define('CATEGORY_THUMB_IMAGE_PATH', SITE_PATH_UPLOAD.'category/thumbnail/');	
define('CATEGORY_THUMB_IMAGE_PATH_REL', ABS_PATH_UPLOAD.'category/thumbnail/');

define('SLIDER_IMAGE_PATH',SITE_PATH_UPLOAD.'sliderImages/');	
define('SLIDER_IMAGE_PATH_REL', ABS_PATH_UPLOAD.'sliderImages/');

define('SLIDER_THUMB_IMAGE_PATH', SITE_PATH_UPLOAD.'sliderImages/thumbnail/');	
define('SLIDER_THUMB_IMAGE_PATH_REL', ABS_PATH_UPLOAD.'sliderImages/thumbnail/');

define('MERCHANT_IMAGE_PATH',MERCHANT_SITE_PATH_UPLOAD.'merchants/');	
define('MERCHANT_IMAGE_PATH_REL', MERCHANT_ABS_PATH_UPLOAD.'merchants/');

define('MERCHANT_ICONS_IMAGE_PATH', MERCHANT_SITE_PATH_UPLOAD.'merchants/icons/');	
define('MERCHANT_ICONS_IMAGE_PATH_REL', MERCHANT_ABS_PATH_UPLOAD.'merchants/icons/');

define('PRODUCT_IMAGE_PATH',MERCHANT_SITE_PATH_UPLOAD.'products/');	
define('PRODUCT_IMAGE_PATH_REL', MERCHANT_ABS_PATH_UPLOAD.'products/');

define('TEMP_PRODUCT_IMAGE_PATH_UPLOAD',SITE_PATH.'/merchant/webresources/uploads/temp/');
define('LIMIT',100);
define('PERPAGE',25);

define('ADMIN_PER_PAGE_LIMIT', 10);


//Encrypt word
define('ENCRYPTSALT',      'saltisgood');
global $admin_per_page_array;
$admin_per_page_array = array(10,50,100,200,250);

global $admin_days_array;
$admin_days_array = array('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');

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

global $item_type_array;
$item_type_array = array('1'=>'Regular','2'=>'Deal','3'=>'Special');


define('ADMIN_PER_PAGE_ARRAY', 'return ' . var_export($admin_per_page_array, 1) . ';');//define constant array
global $methodArray;
$methodArray = array('POST','DELETE','GET','PUT');
global $platformArray;
$platformArray = array('0'=>'Web','1'=>'ios','2'=>'Android');

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

global $discountTierArray;
$discountTierArray = array('1'=>'10','2'=>'20','3'=>'30','4'=>'40','5'=>'50');

global $order_status_array;
$order_status_array = array('0'=>'New','1'=>'Accepted','2'=>'Rejected');

global $month_name;
$month_name 		= array("1"		=>	"January",
							"2"		=>	"February",
							"3"		=>	"March",
							"4"		=>	"April",
							"5"		=>	"May",
							"6"		=>	"June",
							"7"		=>	"July",
							"8"		=>	"August",
							"9"		=>	"September",
							"10"	=>	"October",
							"11"	=>	"November",
							"12"	=>	"December",
						);

?>
