<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();
$FromDate = $ToDate = '';
$stype	=	1;
if(isset($_SESSION['TuplitAnalyticsView']) && !empty($_SESSION['TuplitAnalyticsView']))
	$date_type							=	$_SESSION['TuplitAnalyticsView'];
else {
	$date_type							=	'month';
	$_SESSION['TuplitAnalyticsView']	=	'month';
}
if(isset($_POST['Search'])){
	/*from filter*/
	if(isset($_POST['from_month']) && $_POST['from_month']!= ''){
		$from_month 	 = $_POST['from_month'];
	}else{
		$from_month 	 = date('m');
	}
	if(isset($_POST['from_date']) && $_POST['from_date']!= ''){
		$from_date 	 =  $_POST['from_date'];
	}else{
		$from_date 	 = date('d');
	}
	if(isset($_POST['from_year']) && $_POST['from_year']!= ''){
		$from_year 	 = $_POST['from_year'];
	}else{
		$from_year 	 = date('Y');
	}
	if(isset($_POST['from_hour']) && $_POST['from_hour']!= ''){
		$from_hour 	= $_POST['from_hour'];
	}else{
		$from_hour 	 = 0;
	}
	if(isset($_POST['from_min']) && $_POST['from_min']!= ''){
		$from_min = $_POST['from_min'];
	}else{
		$from_min 	 = 0;
	}
	
	/*to filter*/
	if(isset($_POST['to_month']) && $_POST['to_month']!= ''){
		$to_month 	= $_POST['to_month'];
	}else{
		$to_month 	= date('m');
	}
	if(isset($_POST['to_date']) && $_POST['to_date']!= ''){
		$to_date 	 	=  $_POST['to_date'];
	}else{
		$to_date 	 	= date('d');
	}
	if(isset($_POST['to_year']) && $_POST['to_year']!= ''){
		$to_year 	 	= $_POST['to_year'];
	}else{
		$to_year 	 = date('Y');
	}
	if(isset($_POST['to_hour']) && $_POST['to_hour']!= ''){
		$to_hour 	= $_POST['to_hour'];
	}else{
		$to_hour 	 = 23;
	}
	if(isset($_POST['to_min']) && $_POST['to_min']!= ''){
		$to_min = $_POST['to_min'];
	}else{
		$to_min 	 = 59;
	}	
}

if(isset($from_year) && isset($to_year) && $from_year > $to_year) {
	$from_year = $to_year;
}

if(isset($from_year) && isset($to_year)){
	$FromDate	=	$from_year.'-'.$from_month.'-'.$from_date.' '.$from_hour.':'.$from_min;
	$ToDate		=	$to_year.'-'.$to_month.'-'.$to_date.' '.$to_hour.':'.$to_min;
	$FromDate 	= 	strtotime(convertIntocheckinGmtSiteMinus($FromDate,$_SESSION['tuplit_ses_from_timeZone']));
	$ToDate 	= 	strtotime(convertIntocheckinGmtSiteMinus($ToDate,$_SESSION['tuplit_ses_from_timeZone']));
	$date_type 	= 	'';
}

//getting merchant details
if(isset($_SESSION['merchantDetailsInfo']) && is_array($_SESSION['merchantDetailsInfo'])){
	$merchantInfo  			=	$_SESSION['merchantDetailsInfo'];	
}
else{
	$merchantId				= 	$_SESSION['merchantInfo']['MerchantId'];
	$url					=	WEB_SERVICE.'v1/merchants/'.$merchantId;
	$curlMerchantResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);	
	if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201 && $curlMerchantResponse['merchant']['MerchantId'] != '' ) 
	{
		$merchantInfo		=	$_SESSION['merchantDetailsInfo']   =	$curlMerchantResponse['merchant'];
	}
}
if(!isset($_SESSION['tuplit_ses_from_timeZone']) || $_SESSION['tuplit_ses_from_timeZone'] == ''){
	 $time_zone = getTimeZone();
	 $_SESSION['tuplit_ses_from_timeZone'] = strval($time_zone);	 
}

//export csv
if(isset($_POST['export-excel']) && $_POST['export-excel'] == 1)
{
	$OrderStatus1	= $DataType1 = $FromDate1 = $ToDate1 = '';
	if(isset($_POST['DataTypeHide']) && $_POST['DataTypeHide'] != '') 				$DataType1		=	$_POST['DataTypeHide'];
	if(isset($_POST['OrderStatusHide']) && $_POST['OrderStatusHide'] != '')			$OrderStatus1	=	$_POST['OrderStatusHide'];
	if(isset($_POST['FromDateHide']) && $_POST['FromDateHide'] != '')				$FromDate1		=	$_POST['FromDateHide'];
	if(isset($_POST['ToDateHide']) && $_POST['ToDateHide'] != '')					$ToDate1		=	$_POST['ToDateHide'];
	
	
	//getting comment list of users
	$url					=	WEB_SERVICE."v1/orders/transactions/?FromDate=".$FromDate1."&ToDate=".$ToDate1."&DataType=".$DataType1."&OrderStatus=".$OrderStatus1."&LimitType=1";
	$curlOrderResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlOrderResponse) && is_array($curlOrderResponse) && $curlOrderResponse['meta']['code'] == 201 && is_array($curlOrderResponse['OrderList']) ) {
		if(isset($curlOrderResponse['OrderList']))
			$orderList 	  	= 	$curlOrderResponse['OrderList'];	
	}	
	if(is_array($orderList) && count($orderList) > 0){
		$TransactionsExport		=	array();
		foreach($orderList as $key=>$value){	
			if(!empty($value['TransactionId'])){
				$TransactionsExport[$key]['Transaction ID']		=	$value['TransactionId'];
			}else{
				$TransactionsExport[$key]['Transaction ID']		=	"Test Id";
			}
			$TransactionsExport[$key]['Date']				=	date("m/d/Y H:i:s", strtotime($value['OrderDate']));
			
			if(!empty($value['FirstName']) && !empty($value['LastName'])) 
				$TransactionsExport[$key]['Customer Name']	=	$value['FirstName'].' '.$value['LastName']; 
			else if(!empty($value['FirstName']))
				$TransactionsExport[$key]['Customer Name']	=	$value['FirstName']; 
			else	
				$TransactionsExport[$key]['Customer Name']	=	"Test Transaction";
			
			$TransactionsExport[$key]['Customer Spent - Debited']				=	price_fomat_export($value['TotalPrice']);
			$TransactionsExport[$key]['App Commission']			=	price_fomat_export($value['Commision']);
			$TransactionsExport[$key]['Total - Credited']				=	price_fomat_export($value['TotalPrice'] - $value['Commision']);
			if(isset($value['RefundStatus']) && $value['RefundStatus'] != 2){
				if($value['OrderStatus'] == 0){
					$TransactionsExport[$key]['Status'] 	=	"New";
				}else if($value['OrderStatus'] == 1){
					$TransactionsExport[$key]['Status'] 	=	"Completed";
				}else if($value['OrderStatus'] == 2){
					$TransactionsExport[$key]['Status'] 	=	"Rejected";
				}
			}else{	
				$TransactionsExport[$key]['Status']			= 	"Refunded";
			}
			//$TransactionsExport[$key]['Status']			=	$value['OrderStatus'];
			/*if(($value['OrderStatus'] == 0 && isset($value['OrderDoneBy']) && $value['OrderDoneBy'] == 1) ||  $value['OrderStatus'] == 1){
				$TransactionsExport[$key]['Refund']			=	"Refund"; 
			}else{
				$TransactionsExport[$key]['Refund']			=	""; 
			}*/
		}
		$TransactionsExport = subval_sort($TransactionsExport,'Transaction ID',1);
		csvDownload($TransactionsExport,'TuplitTransactionList.csv');
	}
}

commonHead();
?>
<body class="skin-blue fixed body_height">
	<?php  top_header(); ?>
	<section class="content no-top-padding clear">
		<div class="col-lg-12 box-center">	
			<section class=" content-header">
				<div class="col-sm-8">
					<h1 class="">
					<?php if(isset($_GET['analytics']) && $_GET['analytics'] == 'customer'){
							echo "Customer Analytics"; 
						}else if(isset($_GET['analytics']) && $_GET['analytics'] == 'product') { 
							echo "Product Analytics";
						}else echo "Transactions";
					?>
					</h1>
				</div>
			</section>

			<section class="content no-padding gray_bg top-sale  clear fleft">
				<div class="col-sm-12 no-padding ">
					<? if(isset($_GET['analytics']) && $_GET['analytics'] == 'customer'){
							CustomerAnalyticsTab(); 
						}else if(isset($_GET['analytics']) && $_GET['analytics'] == 'product') { 
							ProductAnalyticsTab();
						}
					?>
					<div class="today_btn col-xs-12 col-sm-2 col-md-2 col-lg-2 no-padding text-right LH56 " style="float:right;">
						<div class="btn-group">
						  <button class="btn btn-default btn-sm dropdown-toggle" value="" type="button" data-toggle="dropdown">
							<span id="dateTypes"><?php if(!empty($date_type)) echo $AnalyticsView[$date_type]; else echo "Select"; ?></span><span class="caret"></span>
						  </button>
							<ul role="menu" class="dropdown-menu">
								  <li><a id="day" onclick="getTransactions(this.id,'0',2,'','<?php echo $stype; ?>');" href="#">Today</a></li>
								  <li><a id="7days" onclick="getTransactions(this.id,'0',2,'','<?php echo $stype; ?>');" href="#">7 days</a></li>
								  <li><a id="month" onclick="getTransactions(this.id,'0',2,'','<?php echo $stype; ?>');" href="#">Month</a></li>
								  <li><a id="year" onclick="getTransactions(this.id,'0',2,'','<?php echo $stype; ?>');" href="#">Year</a></li>
						   	</ul>
						</div>
					</div>
				</div>
			</section>
			<div class="clear">
				<div class="product_list">
					<form name="CustomerTransaction" id="search_transaction" action="CustomerTransaction?analytics=<?php if(isset($_GET['analytics']) && $_GET['analytics'] != '')echo $_GET['analytics'];?>" method="post">
					<div class="box box-primary" style="min-height:initial;">
						<div class="box-body" >				
							<div class="col-xs-11 col-sm-4 col-md-2 col-lg-1 form-group">
								<label>From</label>
								<select id="from_month" class="form-control" name="from_month">
									<option value="">Month</option>
									<?php if(isset($monthArray) && !empty($monthArray)) {
										foreach($monthArray as $m_key=>$m_val) {								
									?>
									<option value="<?php echo $m_key;?>" <?php if(isset($from_month) && $from_month == $m_key){echo "selected";}else{echo '';} ?>><?php echo ucfirst(substr($m_val,0,3));?></option>
									<?php } } ?>	
								</select>
							</div>
							<div class="col-xs-11 col-sm-4 col-md-2 col-lg-1 form-group">
								<label>&nbsp;</label>
								<select id="from_date" class="form-control" name="from_date">
									<option value="">Date</option>
									<?php if(isset($dateArray) && !empty($dateArray)) {
										foreach($dateArray as $d_key=>$d_val) {								
									?>
									<option value="<?php echo $d_val;?>" <?php if(isset($from_date) && $from_date == $d_val){echo "selected";}else{echo '';} ?>><?php echo ucfirst($d_val);?></option>
									<?php } } ?>	
								</select>
							</div>
							<div class="col-xs-11 col-sm-4 col-md-2 col-lg-1 form-group">
								<label>&nbsp;</label>
								<select id="from_year" class="form-control" name="from_year">
									<option value="">Year</option>
									<?php if(isset($yearArray) && !empty($yearArray)) {
										foreach($yearArray as $y_key=>$y_val) {								
									?>
									<option value="<?php echo $y_val;?>" <?php if(isset($from_year) && $from_year == $y_val){echo "selected";}else{echo '';} ?>><?php echo ucfirst($y_val);?></option>
									<?php } } ?>
								</select>
							</div> 
							<div class="col-xs-11 col-sm-4 col-md-2 col-lg-1 form-group">
								<label>&nbsp;</label>
								<select id="from_hour" class="form-control" name="from_hour">
									<option value="">Hour</option>
									<?php if(isset($hourArray) && !empty($hourArray)) {
										foreach($hourArray as $h_key=>$h_val) {								
									?>
									<option value="<?php echo $h_key;?>" <?php if(isset($from_hour) && $from_hour == $h_key){echo "selected";}else{echo '';} ?>><?php echo ucfirst($h_val);?></option>
									<?php } } ?>
								</select>
							</div>
							<div class="col-xs-11 col-sm-4 col-md-2 col-lg-1 form-group">
								<label>&nbsp;</label>
								<select id="from_min" class="form-control" name="from_min">
									<option value="">Min</option>
									<?php if(isset($minArray) && !empty($minArray)) {
										foreach($minArray as $h_key=>$h_val) {								
									?>
									<option value="<?php echo $h_key;?>" <?php if(isset($from_min) && $from_min == $h_key){echo "selected";}else{echo '';} ?>><?php echo ucfirst($h_val);?></option>
									<?php } } ?>
								</select>
							</div>
							<div class="col-xs-11 col-sm-4 col-md-2 col-lg-1 form-group clear-left">
								<label>To</label>
								<select id="to_month" class="form-control" name="to_month">
									<option value="">Month</option>
									<?php if(isset($monthArray) && !empty($monthArray)) {
										foreach($monthArray as $m_key=>$m_val) {								
									?>
									<option value="<?php echo $m_key;?>" <?php if(isset($to_month) && $to_month == $m_key){echo "selected";}else{echo '';} ?>><?php echo ucfirst(substr($m_val,0,3));?></option>
									<?php } } ?>
								</select>
							</div>
							<div class="col-xs-11 col-sm-4 col-md-2 col-lg-1 form-group">
								<label>&nbsp;</label>
								<select id="to_date" class="form-control" name="to_date">
									<option value="">Date</option>
									<?php if(isset($dateArray) && !empty($dateArray)) {
										foreach($dateArray as $d_key=>$d_val) {								
									?>
									<option value="<?php echo $d_val;?>" <?php if(isset($to_date) && $to_date == $d_val){echo "selected";}else{echo '';} ?>><?php echo ucfirst($d_val);?></option>
									<?php } } ?>	
								</select>
							</div>
							<div class="col-xs-11 col-sm-4 col-md-2 col-lg-1 form-group">
								<label>&nbsp;</label>
								<select id="to_year" class="form-control" name="to_year">
									<option value="">Year</option>
									<?php if(isset($yearArray) && !empty($yearArray)) {
										foreach($yearArray as $y_key=>$y_val) {								
									?>
									<option value="<?php echo $y_val;?>" <?php if(isset($to_year) && $to_year == $y_val){echo "selected";}else{echo '';} ?>><?php echo ucfirst($y_val);?></option>
									<?php } } ?>
								</select>
							</div>
							<div class="col-xs-11 col-sm-4 col-md-2 col-lg-1 form-group">
								<label>&nbsp;</label>
								<select id="to_hour" class="form-control" name="to_hour">
									<option value="">Hour</option>
									<?php if(isset($hourArray) && !empty($hourArray)) {
										foreach($hourArray as $h_key=>$h_val) {								
									?>
									<option value="<?php echo $h_key;?>" <?php if(isset($to_hour) && $to_hour == $h_key){echo "selected";}else{echo '';} ?>><?php echo ucfirst($h_val);?></option>
									<?php } } ?>
								</select>
							</div>
							<div class="col-xs-11 col-sm-4 col-md-2 col-lg-1 form-group">
								<label>&nbsp;</label>
								<select id="to_min" class="form-control" name="to_min">
									<option value="">Min</option>
									<?php if(isset($minArray) && !empty($minArray)) {
										foreach($minArray as $h_key=>$h_val) {								
									?>
									<option value="<?php echo $h_key;?>" <?php if(isset($to_min) && $to_min == $h_key){ echo "selected"; } else { echo ''; } ?>><?php echo ucfirst($h_val);?></option>
									<?php } } ?>
								</select>
							</div>
							<div class="col-xs-6 col-sm-6 col-md-6 col-lg-1 form-group clear-left search_right">
								<label class="col-xs-12">&nbsp;</label>
								<input type="submit" class="btn btn-success" name="Search" id="Search" value="Search" title="Search" onclick="return validateFromToTime();">
							</div>
							<div class="col-xs-6 col-sm-6 col-md-4 col-lg-1 no-padding" align="right">
								<label>&nbsp;</label>
								<div class="btn-group-trans">
								<button class="btn btn-default btn-sm dropdown-toggle" value="" type="button" data-toggle="dropdown">
									<span id="statusType">Select</span><span class="caret"></span>
								</button>
								<ul role="menu" class="dropdown-menu" style="text-align:left;">
									  <li><a id="Select" onclick="getTransactions(this.id,'0','3','Select','<?php echo $stype; ?>');" href="#">Select</a></li>
									  <li><a id="New" onclick="getTransactions(this.id,'0','3','New','<?php echo $stype; ?>');" href="#">New</a></li>
									  <li><a id="Accepted" onclick="getTransactions(this.id,'0','3','Accepted','<?php echo $stype; ?>');" href="#">Accepted</a></li>
									  <li><a id="Rejected" onclick="getTransactions(this.id,'0','3','Rejected','<?php echo $stype; ?>');" href="#">Rejected</a></li>
									  <li><a id="Refunded" onclick="getTransactions(this.id,'0','3','Refunded','<?php echo $stype; ?>');" href="#">Refunded</a></li>
								</ul>
								</div>
						</div>
						<span class="error col-xs-12" id="greaterfrom" style="display:none">To date should be greater than From date</span>
						</div>
						
						<div class="box-footer col-sm-12" align="center">
							
						</div>	
					
					</div>
					</form>
				</div>
			</div>
			<section class="content no-padding  clear ">
				<div class="box box-primary no-padding">
					<div class="row box-body box-border" style="padding-bottom:0px;">
						<div class="col-xs-12 col-sm-12  col-lg-12  box-center no-padding">
							<div style="background-color:#f2f2f2;"></div>  
							<div class="col-xs-12"><div class="clear" id="transaction_append"></div></div>
						
						</div>
					</div>
				</div>
			</section>
	</div>		
	</section>
	<form name="exporttocsv" id="exporttocsv" action="" method="post">
		<input type="hidden" id="FromDateHide" name="FromDateHide" value="<?php echo $FromDate; ?>"/>
		<input type="hidden" id="ToDateHide" name="ToDateHide" value="<?php echo $ToDate; ?>"/>
		<input type="hidden" id="DataTypeHide" name="DataTypeHide" value="<?php echo $date_type; ?>"/>
		<input type="hidden" id="OrderStatusHide" name="OrderStatusHide" value=""/>	
		<input type="hidden" id="StartLimitHide" name="StartLimitHide" value="0"/>	
		<input type="hidden" id="TotalRecordsHide" name="TotalRecordsHide" value=""/>
	</form>	
	<?php footerLogin(); ?>
	<?php commonFooter(); ?>
<script type="text/javascript">
$(document).ready(function(){
	getTransactions('<?php echo $date_type;?>','0',1,'','<?php echo $stype; ?>');
});

</script>



