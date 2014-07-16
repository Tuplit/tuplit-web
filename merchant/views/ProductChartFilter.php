<?php 
error_reporting(E_ALL);
require_once('includes/commonincludes.php');
merchant_login_check();
$dataArray = array();
$error_div = 0;
$cur_month = date('m');
$cur_year = date('Y');
$last_date =  date('m/t/Y');
$curr_date = date('m/d/Y');
$first_date_dbformat = date('Y-m-01 H:i:s');
$last_date_dbformat = date('Y-m-d H:i:s');
$sort_type = 'asc';
$sort_field='Name';
$trans_type = 1;
if(isset($_POST['filter_dashboard_date']) && $_POST['filter_dashboard_date']!='') {
	$date_type = trim($_POST['filter_dashboard_date']);
	if($date_type == 'between'){
		$curr_date  = 	$_POST['st_date'];
		$last_date	=	$_POST['end_date'];
	}
}
if(isset($_POST['filter_dashboard']) && $_POST['filter_dashboard']!='') {
	$trans_type = trim($_POST['filter_dashboard']);
}
if(isset($_POST['sort_val']) && $_POST['sort_val']!='') {
	$sort_type = trim($_POST['sort_val']);
}
if(isset($_POST['sort_field']) && $_POST['sort_field']!='') {
	$sort_field = trim($_POST['sort_field']);
}

$url					=	WEB_SERVICE.'v1/merchants/productAnalysis/?Start=0&Limit=0&Type='.$trans_type.'&DataType='.$date_type.'&StartDate='.$curr_date.'&EndDate='.$last_date.'&Sort='.$sort_type.'&Field='.$sort_field.'';
$curlProductResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
//echo "<pre>"; print_r($curlProductResponse ); echo "</pre>";die();
if(isset($curlProductResponse) && is_array($curlProductResponse) && $curlProductResponse['meta']['code'] == 201 && is_array($curlProductResponse['ProductAnalytics']) ) {
	if(isset($curlProductResponse['ProductAnalytics'])){
		$product_array	 = $curlProductResponse['ProductAnalytics'];	
	}
} else if(isset($curlProductResponse['meta']['errorMessage']) && $curlProductResponse['meta']['errorMessage'] != '') {
		$errorMessage	=	$curlProductResponse['meta']['errorMessage'];
} else {
		$errorMessage	= 	"Bad Request";
} 

if($date_type != '') {
	if(isset($product_array) && is_array($product_array) && count($product_array)>0) {
		$orderStringArray = getStringForDayProduct($product_array);
	}
	if(isset($orderStringArray) && $orderStringArray!='') {
		list($x_labels_string,$value_order_string) = explode('###',$orderStringArray);
		$all_series['order'] = $value_order_string;
	}
}
if(!isset($all_series) || count($all_series)<=0) {
	$error_div = 1;
} 




