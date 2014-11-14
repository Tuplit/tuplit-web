<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
require_once('controllers/OrderController.php');
$orderObj   =   new OrderController();
$dataArray 	= array();
$error_div 	= 0;
$condition 	= $field = '';
$cur_month 	= date('m');
$cur_year 	= date('Y');
$last_date 	= date('m/t/Y');
$curr_date 	= date('m/d/Y');
$first_date_dbformat = date('Y-m-01 H:i:s');
$last_date_dbformat = date('Y-m-d H:i:s');
if(isset($_POST['filter_dashboard_date']) && $_POST['filter_dashboard_date']!='') {
	$date_type = trim($_POST['filter_dashboard_date']);
	if($date_type == 'between'){
		$curr_date  = 	$_POST['st_date'];
		$last_date	=	$_POST['end_date'];
	}
}

if(!isset($_SESSION['tuplit_ses_from_timeZone']) || $_SESSION['tuplit_ses_from_timeZone'] == ''){
	$time_zone 	= 	getTimeZone();
	$_SESSION['tuplit_ses_from_timeZone'] = strval($time_zone);
} else {
	$time_zone 	= 	$_SESSION['tuplit_ses_from_timeZone'];
}
if($date_type=='year') {
	$field 		= 	" , MONTH(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE)) AS month";
	$condition 	.=	 "  and DATE_FORMAT(OrderDate,'%Y') = ".$cur_year." group by month";
} else if($date_type=='month') {
	$field 		= 	" , DATE_FORMAT(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE), '%m/%d/%Y') AS day";
	$condition .= 	"and DATE_FORMAT(OrderDate,'%m') = ".$cur_month." and DATE_FORMAT(OrderDate,'%Y') = ".$cur_year." group by day";
} else if($date_type=='day') {
	$field 		= 	" , HOUR(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE)) AS hour";
	$condition .= 	" and date(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE))='".date('Y-m-d',strtotime($curr_date))."' group by hour";
} else if($date_type=='between') {
	$field 		= 	" , DATE_FORMAT(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE), '%m/%d/%Y') AS day";
	if(isset($startDate) && $startDate!='' && isset($endDate) && $endDate !='')
	{
		$condition 	.= 	" and DATE(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE)) between '".date('Y-m-d',strtotime($startDate))."' and '".date('Y-m-d',strtotime($endDate))."'";
	} else if(isset($startDate) && $startDate!='') {
		$condition 	.= 	" and DATE(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE)) >= '".date('Y-m-d',strtotime($startDate))."'";
	} else if(isset($endDate) && $endDate!='') {
		$condition 	.= 	" and DATE(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE)) <= '".date('Y-m-d',strtotime($endDate))."'";
	} 
	$condition 		.= 	' group by day';
} else if($date_type=='7days') {
	$field 			 = 	" , DATE_FORMAT(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE), '%m/%d/%Y') AS day";
	$condition 		.= 	"and (DATE_FORMAT(OrderDate,'%Y-%m-%d') <= '".date('Y-m-d',strtotime($curr_date))."' and DATE_FORMAT(OrderDate,'%Y-%m-%d') > '".date('Y-m-d',strtotime("-7 days"))."')  group by day";
}
else if($date_type=='timeofday') {
	$field 			= 	" , HOUR(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE)) AS hour";
	$condition 		.= 	" and date(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE))='".date('Y-m-d',strtotime($curr_date))."' group by hour";
}
$order_array  = $orderObj->getTransactionList($field,$condition);

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
?>



