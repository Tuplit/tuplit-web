<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
require_once('controllers/OrderController.php');
$orderObj   =   new OrderController();
$dataArray = array();
$error_div = 0;
$condition 	= $field = $pie_condition = $sort_condition = '';
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
if(!isset($_SESSION['tuplit_ses_from_timeZone']) || $_SESSION['tuplit_ses_from_timeZone'] == ''){
	$time_zone 	= 	getTimeZone();
	$_SESSION['tuplit_ses_from_timeZone'] = strval($time_zone);
} else {
	$time_zone 	= 	$_SESSION['tuplit_ses_from_timeZone'];
}
if(isset($_POST['sort_field']) && $_POST['sort_field']!='') {
	$sort_field = trim($_POST['sort_field']);
}
if($sort_type != '' && $sort_field != '' ){
	$sort_condition = ' order by '.$sort_field.' '.$sort_type.'';
}
else{
	$sort_condition = ' order by Name asc';
}
if($trans_type == 2)
	 	 $group_condition	= 'group by p.fkCategoryId,p.ItemType ';
else if($trans_type == 1)
		$group_condition	= ' group by p.id';
	if($date_type=='year') {
		$field 				 = 	 " , MONTH(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE)) AS month";
		$condition 			.=	 "  and DATE_FORMAT(OrderDate,'%Y') = ".$cur_year."   ".$group_condition."";
		$pie_condition 		.=	 "  and DATE_FORMAT(OrderDate,'%Y') = ".$cur_year."";
	} else if($date_type=='month') {
		$field 				 = 	" , DATE_FORMAT(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE), '%m/%d/%Y') AS day";
		$condition 			.= 	"and DATE_FORMAT(OrderDate,'%m') = ".$cur_month." and DATE_FORMAT(OrderDate,'%Y') = ".$cur_year." ".$group_condition."";
		$pie_condition 		.=	 "and DATE_FORMAT(OrderDate,'%m') = ".$cur_month." and DATE_FORMAT(OrderDate,'%Y') = ".$cur_year."";
	} else if($date_type=='day') {
		$field 				 = 	" , HOUR(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE)) AS hour";
		$condition 			.= 	" and date(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE))='".date('Y-m-d',strtotime($curr_date))."'  ".$group_condition."";
		$pie_condition 		.=	 "and date(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE))='".date('Y-m-d',strtotime($curr_date))."'";
	} else if($date_type=='between') {
		$field 		= 	" , DATE_FORMAT(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE), '%m/%d/%Y') AS day";
		if(isset($startDate) && $startDate!='' && isset($endDate) && $endDate !='')
		{
			$condition 		.= 	" and DATE(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE)) between '".date('Y-m-d',strtotime($startDate))."' and '".date('Y-m-d',strtotime($endDate))."'";
			$pie_condition 	.= 	" and DATE(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE)) between '".date('Y-m-d',strtotime($startDate))."' and '".date('Y-m-d',strtotime($endDate))."'";
		} else if(isset($startDate) && $startDate!='') {
			$condition 		.= 	" and DATE(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE)) >= '".date('Y-m-d',strtotime($startDate))."'";
			$pie_condition 	.= 	" and DATE(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE)) >= '".date('Y-m-d',strtotime($startDate))."'";
		} else if(isset($endDate) && $endDate!='') {
			$condition 		.= 	" and DATE(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE)) <= '".date('Y-m-d',strtotime($endDate))."'";
			$pie_condition 	.= 	" and DATE(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE)) <= '".date('Y-m-d',strtotime($endDate))."'";
		} 
		$condition 		.= 	'  '.$group_condition.'';
	} else if($date_type=='7days') {
		$field 			 = 	" , DATE_FORMAT(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE), '%m/%d/%Y') AS day";
		$condition 		.= 	"and (DATE_FORMAT(OrderDate,'%Y-%m-%d') <= '".date('Y-m-d',strtotime($curr_date))."' and DATE_FORMAT(OrderDate,'%Y-%m-%d') > '".date('Y-m-d',strtotime("-7 days"))."')  ".$group_condition."";
		$pie_condition	.= 	"and (DATE_FORMAT(OrderDate,'%Y-%m-%d') <= '".date('Y-m-d',strtotime($curr_date))."' and DATE_FORMAT(OrderDate,'%Y-%m-%d') > '".date('Y-m-d',strtotime("-7 days"))."') ";
	}

if($trans_type == 1)
	$product_array  = $orderObj->getProductTransactions($field,$condition,$sort_condition);
else if($trans_type == 2){
	$product_array  = $orderObj->getCategoryTransactions($field,$condition,$sort_condition);

	if(isset($product_array) && count($product_array) > 0 && is_array($product_array)){
		foreach($product_array as $key=>$value){
			if($value->ItemType == 2 && $value->CategoryId == 0)
				$value->Name	=	'Deals';
			else if($value->ItemType == 3 && $value->CategoryId == 0)
				$value->Name	=	'Specials';
		}
	}
}
$pie_chart_array	= $orderObj->getPieChart($field,$pie_condition);
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




