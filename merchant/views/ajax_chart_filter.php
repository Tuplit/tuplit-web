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
if(isset($_POST['filter_dashboard_date']) && $_POST['filter_dashboard_date']!='') {
	$date_type = trim($_POST['filter_dashboard_date']);
	if($date_type == 'between'){
		$curr_date  = 	$_POST['st_date'];
		$last_date	=	$_POST['end_date'];
	}
}
$url					=	WEB_SERVICE.'v1/merchants/transaction/?Start=0&Limit=0&DataType='.$date_type.'&StartDate='.$curr_date.'&EndDate='.$last_date.'';
$curlTransactionResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
if(isset($curlTransactionResponse) && is_array($curlTransactionResponse) && $curlTransactionResponse['meta']['code'] == 201 && is_array($curlTransactionResponse['TransactionList']) ) {
	if(isset($curlTransactionResponse['TransactionList'])){
		$order_array	 = $curlTransactionResponse['TransactionList'];	
	}
} else if(isset($curlTransactionResponse['meta']['errorMessage']) && $curlTransactionResponse['meta']['errorMessage'] != '') {
		$errorMessage	=	$curlTransactionResponse['meta']['errorMessage'];
} else {
		$errorMessage	= 	"Bad Request";
} 

if($date_type=='month') {
	if(isset($order_array) && is_array($order_array) && count($order_array)>0) {
		$orderStringArray = getStringForDay($order_array);
	}
	if(isset($orderStringArray) && $orderStringArray!='') {
		list($x_labels_string,$value_order_string) = explode('###',$orderStringArray);
		$all_series['order'] = $value_order_string;
	}
} else if($date_type=='year') {
	$x_labels_string = '';
	if(isset($order_array) && is_array($order_array) && count($order_array)>0) {
		$all_series['order'] = getStringForMonth($order_array);
	}
} else if($date_type=='day') {
	if(isset($order_array) && is_array($order_array) && count($order_array)>0) {
		$orderStringArray = getStringForHour($order_array);
	}
	if(isset($orderStringArray) && $orderStringArray!='') {
		list($x_labels_string,$value_order_string) = explode('###',$orderStringArray);
		$all_series['order'] = $value_order_string;
	}
} else if($date_type=='between') {
	if(isset($order_array) && is_array($order_array) && count($order_array)>0) {
		$orderStringArray = getStringForDay($order_array,date('Y-m-d',strtotime($_POST['st_date'])),date('Y-m-d',strtotime($_POST['end_date'])));
	}
	if(isset($orderStringArray) && $orderStringArray!='') {
		list($x_labels_string,$value_order_string) = explode('###',$orderStringArray);
		$all_series['order'] = $value_order_string;
	}
} else if($date_type=='7days') {
	if(isset($order_array) && is_array($order_array) && count($order_array)>0) {
		$orderStringArray = getStringForDay($order_array,'','',1);
	}
	if(isset($orderStringArray) && $orderStringArray!='') {
		list($x_labels_string,$value_order_string) = explode('###',$orderStringArray);
		$all_series['order'] = $value_order_string;
	}
}else if($date_type=='timeofday') {
	if(isset($order_array) && is_array($order_array) && count($order_array)>0) {
		$orderStringArray = getStringForDayTime($order_array);
	}
	if(isset($orderStringArray) && $orderStringArray!='') {
		list($x_labels_string,$value_order_string) = explode('###',$orderStringArray);
		$all_series['order'] = $value_order_string;
	}
	
}
if(!isset($all_series) || count($all_series)<=0) {
	$error_div = 1;
} 




