<?php 
ob_start();
require_once('../includes/AjaxCommonIncludes.php');

// get currency from country name
if( isset( $_GET['action'] )  && $_GET['action'] == 'GET_CURRENCY_FROM_COUNTRY') {
	if(isset($_GET['country_name']) && $_GET['country_name']!=''){
		$country_name	= strtolower($_GET['country_name']);
		$currency_array = array_change_key_case($country_currency_array);
		if(isset($currency_array[$country_name]))
			echo $currency_array[$country_name];
		else
			echo 0;
	}
}

?>