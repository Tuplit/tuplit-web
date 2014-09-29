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

define('TEMP_PRODUCT_IMAGE_PATH_UPLOAD',ABS_PATH.'/merchant/webresources/uploads/temp/');
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

global $userLoadMore;
$userLoadMore	=	12;

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
							'ios' 	=> 	'arn:aws:sns:us-west-2:105887880235:app/APNS_SANDBOX/Tuplit_Development',
						);
}
else{
	$applicationARN	=	array(
							'ios' 	=> 	'arn:aws:sns:us-west-2:105887880235:app/APNS/Tuplit_Production',
						);
}

global $discountTierArray;
$discountTierArray = array('1'=>'10','2'=>'20','3'=>'30','4'=>'40','5'=>'50');

global $order_status_array;
$order_status_array = array('0'=>'New','1'=>'Accepted','2'=>'Rejected');

global $month_name;
$month_name 		= 	array("1"		=>	"January",
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
global $mangoPayError;
$mangoPayError   	= 	array('02625'=>'Invalid card number',
						 '02626'=>'Invalid date. Use mmdd format',
						 '02627'=>'Invalid CVV number',
						 '02624'=>'Invalid expiration date',
						 '02628'=>'Transaction refused',
						 '02101'=>'Internal Error'
						 );
						 
global $cardTypeArray;
$cardTypeArray		=	array('CB'=>ADMIN_SITE_PATH.'/webresources/cards/cb.png',
						  'VISA'=>ADMIN_SITE_PATH.'/webresources/cards/visa.png',
						  'MASTERCARD'=>ADMIN_SITE_PATH.'/webresources/cards/mastercard.png',
						  'AMEX'=>ADMIN_SITE_PATH.'/webresources/cards/amex.png',
						  'CB_VISA_MASTERCARD'=>ADMIN_SITE_PATH.'/webresources/cards/mastercard.png');
						  
global $BusinessTypeArray;
$BusinessTypeArray = array('1'=>'Restaurant','2'=>'Foods','3'=>'Drinks');

global $HowyouHeared;
$HowyouHeared = array('1'=>'Press','2'=>'Social Media','3'=>'Advertising','4'=>'Search engine','5'=>'Friend','6'=>'Other');

global $country_currency_array;
$country_currency_array = array(
								'Afghanistan' => 'AFN',
								'Albania' => 'ALL',
								'Algeria' => 'DZD',
								'American Samoa' => 'USD',
								'Andorra' => 'EUR',
								'Angola' => 'AOA',
								'Anguilla' => 'XCD',
								'Antigua and Barbuda' => 'XCD',
								'Argentina' => 'ARS',
								'Armenia' => 'AMD',
								'Aruba' => 'AWG',
								'Australia' => 'AUD',
								'Austria' => 'EUR',
								'Azerbaijan' => 'AZN',
								'Bahamas' => 'BSD',
								'Bahrain' => 'BHD',
								'Bangladesh' => 'BDT',
								'Barbados' => 'BBD',
								'Belarus' => 'BYR',
								'Belgium' => 'EUR',
								'Belize' => 'BZD',
								'Benin' => 'XOF',
								'Bermuda' => 'BMD',
								'Bhutan' => 'BTN',
								'Bolivia' => 'BOB',
								'Bosnia and Herzegovina' => 'BAM',
								'Botswana' => 'BWP',
								'Bouvet Island' => 'NOK',
								'Brazil' => 'BRL',
								'British Indian Ocean Territory' => 'USD',
								'British Virgin Islands' => 'USD',
								'Brunei' => 'BND',
								'Bulgaria' => 'BGN',
								'Burkina Faso' => 'XOF',
								'Burundi' => 'BIF',
								'Cambodia' => 'KHR',
								'Cameroon' => 'XAF',
								'Canada' => 'CAD',
								'Cape Verde' => 'CVE',
								'Cayman Islands' => 'KYD',
								'Central African Republic' => 'XAF',
								'Chad' => 'XAF',
								'Chile' => 'CLP',
								'China' => 'CNY',
								'Christmas Island' => 'AUD',
								'Cocos Islands' => 'AUD',
								'Colombia' => 'COP',
								'Comoros' => 'KMF',
								'Cook Islands' => 'NZD',
								'Costa Rica' => 'CRC',
								'Croatia' => 'HRK',
								'Cuba' => 'CUP',
								'Cyprus' => 'CYP',
								'Czech Republic' => 'CZK',
								'Democratic Republic of the Congo' => 'CDF',
								'Denmark' => 'DKK',
								'Djibouti' => 'DJF',
								'Dominica' => 'XCD',
								'Dominican Republic' => 'DOP',
								'East Timor' => 'USD',
								'Ecuador' => 'USD',
								'Egypt' => 'EGP',
								'El Salvador' => 'SVC',
								'Equatorial Guinea' => 'XAF',
								'Eritrea' => 'ERN',
								'Estonia' => 'EEK',
								'Ethiopia' => 'ETB',
								'Falkland Islands' => 'FKP',
								'Faroe Islands' => 'DKK',
								'Fiji' => 'FJD',
								'Finland' => 'EUR',
								'France' => 'EUR',
								'French Guiana' => 'EUR',
								'French Polynesia' => 'XPF',
								'French Southern Territories' => 'EUR',
								'Gabon' => 'XAF',
								'Gambia' => 'GMD',
								'Georgia' => 'GEL',
								'Germany' => 'EUR',
								'Ghana' => 'GHC',
								'Gibraltar' => 'GIP',
								'Greece' => 'EUR',
								'Greenland' => 'DKK',
								'Grenada' => 'XCD',
								'Guadeloupe' => 'EUR',
								'Guam' => 'USD',
								'Guatemala' => 'GTQ',
								'Guinea' => 'GNF',
								'Guinea-Bissau' => 'XOF',
								'Guyana' => 'GYD',
								'Haiti' => 'HTG',
								'Heard Island and McDonald Islands' => 'AUD',
								'Honduras' => 'HNL',
								'Hong Kong' => 'HKD',
								'Hungary' => 'HUF',
								'Iceland' => 'ISK',
								'India' => 'INR',
								'Indonesia' => 'IDR',
								'Iran' => 'IRR',
								'Iraq' => 'IQD',
								'Ireland' => 'EUR',
								'Israel' => 'ILS',
								'Italy' => 'EUR',
								'Ivory Coast' => 'XOF',
								'Jamaica' => 'JMD',
								'Japan' => 'JPY',
								'Jordan' => 'JOD',
								'Kazakhstan' => 'KZT',
								'Kenya' => 'KES',
								'Kiribati' => 'AUD',
								'Kuwait' => 'KWD',
								'Kyrgyzstan' => 'KGS',
								'Laos' => 'LAK',
								'Latvia' => 'LVL',
								'Lebanon' => 'LBP',
								'Lesotho' => 'LSL',
								'Liberia' => 'LRD',
								'Libya' => 'LYD',
								'Liechtenstein' => 'CHF',
								'Lithuania' => 'LTL',
								'Luxembourg' => 'EUR',
								'Macao' => 'MOP',
								'Macedonia' => 'MKD',
								'Madagascar' => 'MGA',
								'Malawi' => 'MWK',
								'Malaysia' => 'MYR',
								'Maldives' => 'MVR',
								'Mali' => 'XOF',
								'Malta' => 'MTL',
								'Marshall Islands' => 'USD',
								'Martinique' => 'EUR',
								'Mauritania' => 'MRO',
								'Mauritius' => 'MUR',
								'Mayotte' => 'EUR',
								'Mexico' => 'MXN',
								'Micronesia' => 'USD',
								'Moldova' => 'MDL',
								'Monaco' => 'EUR',
								'Mongolia' => 'MNT',
								'Montserrat' => 'XCD',
								'Morocco' => 'MAD',
								'Mozambique' => 'MZN',
								'Myanmar' => 'MMK',
								'Namibia' => 'NAD',
								'Nauru' => 'AUD',
								'Nepal' => 'NPR',
								'Netherlands' => 'EUR',
								'Netherlands Antilles' => 'ANG',
								'New Caledonia' => 'XPF',
								'New Zealand' => 'NZD',
								'Nicaragua' => 'NIO',
								'Niger' => 'XOF',
								'Nigeria' => 'NGN',
								'Niue' => 'NZD',
								'Norfolk Island' => 'AUD',
								'North Korea' => 'KPW',
								'Northern Mariana Islands' => 'USD',
								'Norway' => 'NOK',
								'Oman' => 'OMR',
								'Pakistan' => 'PKR',
								'Palau' => 'USD',
								'Palestinian Territory' => 'ILS',
								'Panama' => 'PAB',
								'Papua New Guinea' => 'PGK',
								'Paraguay' => 'PYG',
								'Peru' => 'PEN',
								'Philippines' => 'PHP',
								'Pitcairn' => 'NZD',
								'Poland' => 'PLN',
								'Portugal' => 'EUR',
								'Puerto Rico' => 'USD',
								'Qatar' => 'QAR',
								'Republic of the Congo' => 'XAF',
								'Reunion' => 'EUR',
								'Romania' => 'RON',
								'Russia' => 'RUB',
								'Rwanda' => 'RWF',
								'Saint Helena' => 'SHP',
								'Saint Kitts and Nevis' => 'XCD',
								'Saint Lucia' => 'XCD',
								'Saint Pierre and Miquelon' => 'EUR',
								'Saint Vincent and the Grenadines' => 'XCD',
								'Samoa' => 'WST',
								'San Marino' => 'EUR',
								'Sao Tome and Principe' => 'STD',
								'Saudi Arabia' => 'SAR',
								'Senegal' => 'XOF',
								'Serbia and Montenegro' => 'RSD',
								'Seychelles' => 'SCR',
								'Sierra Leone' => 'SLL',
								'Singapore' => 'SGD',
								'Slovakia' => 'SKK',
								'Slovenia' => 'EUR',
								'Solomon Islands' => 'SBD',
								'Somalia' => 'SOS',
								'South Africa' => 'ZAR',
								'South Georgia and the South Sandwich Islands' => 'GBP',
								'South Korea' => 'KRW',
								'Spain' => 'EUR',
								'Sri Lanka' => 'LKR',
								'Sudan' => 'SDD',
								'Suriname' => 'SRD',
								'Svalbard and Jan Mayen' => 'NOK',
								'Swaziland' => 'SZL',
								'Sweden' => 'SEK',
								'Switzerland' => 'CHF',
								'Syria' => 'SYP',
								'Taiwan' => 'TWD',
								'Tajikistan' => 'TJS',
								'Tanzania' => 'TZS',
								'Thailand' => 'THB',
								'Togo' => 'XOF',
								'Tokelau' => 'NZD',
								'Tonga' => 'TOP',
								'Trinidad and Tobago' => 'TTD',
								'Tunisia' => 'TND',
								'Turkey' => 'TRY',
								'Turkmenistan' => 'TMM',
								'Turks and Caicos Islands' => 'USD',
								'Tuvalu' => 'AUD',
								'U.S. Virgin Islands' => 'USD',
								'Uganda' => 'UGX',
								'Ukraine' => 'UAH',
								'United Arab Emirates' => 'AED',
								'United Kingdom' => 'GBP',
								'United States' => 'USD',
								'United States Minor Outlying Islands' => 'USD',
								'Uruguay' => 'UYU',
								'Uzbekistan' => 'UZS',
								'Vanuatu' => 'VUV',
								'Vatican' => 'EUR',
								'Venezuela' => 'VEF',
								'Vietnam' => 'VND',
								'Wallis and Futuna' => 'XPF',
								'Western Sahara' => 'MAD',
								'Yemen' => 'YER',
								'Zambia' => 'ZMK',
								'Zimbabwe' => 'ZWD');
?>
