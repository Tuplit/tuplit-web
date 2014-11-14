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
define('SITE_TITLE', 'Tuplit&nbsp;Merchant&nbsp;Panel');
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
	define('SITE_PATH_UPLOAD','http://dru4vhpkosqqc.cloudfront.net/');
	define('ABS_PATH_UPLOAD','http://dru4vhpkosqqc.cloudfront.net/');
}

define('MERCHANT_ICONS_IMAGE_PATH', SITE_PATH_UPLOAD.'merchants/icons/');	
define('MERCHANT_ICONS_IMAGE_PATH_REL', ABS_PATH_UPLOAD.'merchants/icons/');

define('MERCHANT_COVER_IMAGE_PATH', SITE_PATH_UPLOAD.'merchants/');	
define('MERCHANT_COVER_IMAGE_PATH_REL', ABS_PATH_UPLOAD.'merchants/');

define('MERCHANT_BACKGROUND_IMAGE_PATH', SITE_PATH_UPLOAD.'merchants/backgrounds/');	
define('MERCHANT_BACKGROUND_IMAGE_PATH_REL', ABS_PATH_UPLOAD.'merchants/backgrounds/');

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

global $Salesperson;
$Salesperson = array('Dashboard','Orders','OrderHistory','TransactionList','ProductList');

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
$HowyouHeared = array('1'=>'Press','2'=>'Social Media','3'=>'Advertising','4'=>'Search engine','5'=>'Friend','6'=>'Other');

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
$currencyArray = array('USD','EUR','GBP','PLN','CHF');

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
								
require(ABS_PATH.'/includes/CommonFunctions.php');
if(!isset($layout)){
	$layout = detectLayout();
}	
define('LAYOUT',$layout);

global $backgroundSliderArray;
$backgroundSliderArray = array( '1'=>SITE_PATH.'/webresources/images/HomePage_Bg1.jpg',
								'2'=>SITE_PATH.'/webresources/images/HomePage_Bg2.jpg',
								'3'=>SITE_PATH.'/webresources/images/HomePage_Bg3.jpg',
								'4'=>SITE_PATH.'/webresources/images/HomePage_Bg4.jpg',
								'5'=>SITE_PATH.'/webresources/images/HomePage_Bg5.jpg');
								
global $AutoLock;
$AutoLock = array('1'=>'3','2'=>'5','3'=>'10');

global $ProductVAT;
$ProductVAT = array('0'=>'0','5'=>'5','20'=>'20');
?>
