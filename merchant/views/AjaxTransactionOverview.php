<?php
require_once('includes/CommonIncludes.php');
$dataArray = $Summary = array();
$error_div =  0;
$fromdate	=	'';
$cur_month = date('m');
$cur_year = date('Y');
$last_date =  date('m/t/Y');
$curr_date = date('m/d/Y');
$first_date_dbformat = date('Y-m-01 H:i:s');
$last_date_dbformat = date('Y-m-d H:i:s');
if(isset($_POST['action']) && $_POST['action']=='GET_CHART') { 
	$timeDay		=	1;
	if(isset($_POST['dataType']) && $_POST['dataType'] != '') {
		$date_type							=	$_POST['dataType'];
		if(isset($_POST['showtype']) && $_POST['showtype'] == 1)
			$_SESSION['TuplitAnalyticsView']	=	$date_type;
	}
	if(isset($_POST['timeOfDay']) && $_POST['timeOfDay'] != ''){
		$timeDay			=	$_POST['timeOfDay'];
	}
	if(isset($_POST['fromdate']) && $_POST['fromdate'] != ''){
		$fromdate			=	$_POST['fromdate'];
	}
	if(!isset($_SESSION['tuplit_ses_from_timeZone']) || $_SESSION['tuplit_ses_from_timeZone'] == ''){
		 $time_zone = getTimeZone();
		 $_SESSION['tuplit_ses_from_timeZone'] = strval($time_zone);	 
	}
	$url 	=	WEB_SERVICE.'v1/merchants/transaction/?DataType='.$date_type.'&StartDate='.$fromdate.'&TimeZone='.$_SESSION['tuplit_ses_from_timeZone'].'';
	//echo $url;
	$curlTransactionResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
	/*if($_SERVER['REMOTE_ADDR'] == '172.21.4.130')
	echo "<pre>";print_r($curlTransactionResponse);echo "</pre>";*/
	if(isset($curlTransactionResponse) && is_array($curlTransactionResponse) && $curlTransactionResponse['meta']['code'] == 201) {
		if(isset($curlTransactionResponse['Transaction']['Summary']))
			$Summary		=	$curlTransactionResponse['Transaction']['Summary'];
		if(isset($curlTransactionResponse['Transaction']['CurrentList']))
			$order_array	= 	$curlTransactionResponse['Transaction']['CurrentList'];	
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
			$orderStringArray = getStringForDayTime($order_array,$timeDay);
		}
		if(isset($orderStringArray) && $orderStringArray!='') {
			list($x_labels_string,$value_order_string) = explode('###',$orderStringArray);
			$all_series['order'] = $value_order_string;
		}	
	}else if($date_type=='7days') {
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

	$xarrays = $new_array = array();
	$count = 0;	
	if(isset($x_labels_string) && $x_labels_string!='') { 
		$xarrays = explode(',',$x_labels_string);
	}
	else{
		$default_label = 'Jan,Feb,Mar,Apr,May,Jun,Jul,Aug,Sep,Oct,Nov,Dec';
		$xarrays = explode(',',$default_label);
	}

	$value_arrays = array();
	if(isset($all_series['order']) && $all_series['order'] != ''){
		$value_arrays = explode(',',$all_series['order']);
	}
	?>
	<div class="row col-xs-12 no-padding trans-overview">
		<?php if(isset($order_array) && !empty($order_array)) { 
			$total_price = $total_orders = 0;
			foreach($order_array as $key=>$value){
				$total_price	+=	$value["TotalPrice"];
				$total_orders	+=	$value["TotalOrders"];			
				$count += 1;
			}
			$average		=	$total_price/$total_orders;
			?>
			<div class="col-xs-12 col-sm-9 col-md-10 col-lg-10">							
				<h4 class="HelveticaNeueBold">Total Revenue</h4>
				<div class="sel tot_revenue">
					<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4 price"><?php if(isset($Summary['Amount']) && $Summary['Amount'] > 0) echo price_fomat($Summary['Amount']);  else  	echo price_fomat(0); ?> </div>
					<div class="percantage <?php if(isset($Summary['AmountDifference']) && $Summary['AmountDifference'] < 0) echo "decrement"; else echo "increment"; ?>">
						<div class="per-<?php if(isset($Summary['AmountDifference']) && $Summary['AmountDifference'] < 0) echo "decrement"; else echo "increment"; ?>">
							<div style="margin-left:26px">
							<span class="amt_percant"><?php if(isset($Summary['AmountPercentage']) && $Summary['AmountPercentage'] > 0) echo $Summary['AmountPercentage']."%"; else echo "0%"; ?></span><br>				
							<?php if(isset($Summary['AmountDifference']) && $Summary['AmountDifference'] > 0) 
										echo "(+".price_fomat(abs($Summary['AmountDifference'])).")"; 
									else if(isset($Summary['AmountDifference']))
										echo "(-".price_fomat(abs($Summary['AmountDifference'])).")";
									else
										echo price_fomat(0); 
								?>
							</div>
						</div>
					</div>					
				</div>			
			</div>
			<div class="col-xs-12 col-sm-3 col-md-2 col-lg-2 fright no-padding">
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 no-padding">
					<div class="col-xs-6 col-sm-7 col-md-7 col-lg-7">Gross Sale</div>
					<div class="col-xs-6 col-sm-5 col-md-5 col-lg-5 text-right no-padding"><b><?php if(isset($Summary['GrossTotal']) && $Summary['GrossTotal'] > 0) echo price_fomat($Summary['GrossTotal']);  else  	echo price_fomat(0); ?> </b></div>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 no-padding">
					<div class="col-xs-6 col-sm-7 col-md-7 col-lg-7">Discount</div>
					<div class="col-xs-6 col-sm-5 col-md-5 col-lg-5 text-right no-padding"><b><?php if(isset($Summary['Discount']) && $Summary['Discount'] > 0) echo "-".price_fomat($Summary['Discount']);  else  	echo price_fomat(0); ?> </b></div>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 no-padding">
					<div class="col-xs-6 col-sm-7 col-md-7 col-lg-7">SubTotal</div>
					<div class="col-xs-6 col-sm-5 col-md-5 col-lg-5 text-right no-padding"><b><?php if(isset($Summary['SubTotal']) && $Summary['SubTotal'] > 0) echo price_fomat($Summary['SubTotal']);  else  	echo price_fomat(0); ?> </b></div>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 no-padding">				
					<div class="col-xs-6 col-sm-7 col-md-7 col-lg-7">VAT</div>
					<div class="col-xs-6 col-sm-5 col-md-5 col-lg-5 text-right no-padding"><b><?php if(isset($Summary['Vat']) && $Summary['Vat'] > 0) echo price_fomat($Summary['Vat']);  else  	echo price_fomat(0); ?> </b></div>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 no-padding">				
					<div class="col-xs-6 col-sm-7 col-md-7 col-lg-7">Total</div>
					<div class="col-xs-6 col-sm-5 col-md-5 col-lg-5 text-right no-padding"><b><?php if(isset($Summary['Amount']) && $Summary['Amount'] > 0) echo price_fomat($Summary['Amount']);  else  	echo price_fomat(0); ?> </b></div>
				</div>
			</div>
			
	<?php }  ?>
	</div>

	<?php if(isset($error_div) && $error_div==0 && !empty($order_array)) {?>
	<div class="box-success">
		<div class="col-xs-12 col-sm-6 col-md-8 col-lg-8 no-padding">							
			<h4 style="color:#202020;margin-bottom:0px;">Revenue</h1>
		</div>
		<?php if($date_type=='day') {?>
			<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 session" align="right" style="padding-top:10px;">
			<ul>
				<li>
					<a <?php if($timeDay == 1) {?> class="sel" <?php } ?> onclick="getTransactionDetails('day',1,'',3);">MORNING<?php if($timeDay == 1) {?><span class="caret"></span><?php } ?></a>
				</li>
				<li>
					<a <?php if($timeDay == 2) {?> class="sel" <?php } ?> onclick="getTransactionDetails('day',2,'',3);">NOON<?php if($timeDay == 2) {?><span class="caret"></span><?php } ?></a>
				</li>
				<li>
					<a <?php if($timeDay == 3) {?> class="sel" <?php } ?> onclick="getTransactionDetails('day',3,'',3);">EVENING<?php if($timeDay == 3) {?><span class="caret"></span><?php } ?></a>
				</li>
			</ul>
			</div>
		<?php }?>
		<div class="box-body chart-responsive col-xs-12" style="padding-left:0px;padding-right:0px;">
			 <div class="chart" id="bar-chart1" style="height: 300px;"></div>
		 </div>
	</div> 
	<?php } else {?>
	 <div class="row clear">		
			 <div align="center" class="alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10">
				<i class="fa fa-fw fa-warning"></i>No results found</div>							
		</div>	
	<?php } ?>

	<!--Transaction summary -->
	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 transaction">
		<?php if(isset($order_array) && !empty($order_array)) { ?>
			<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 avg_transaction">
				<h4>Average transaction</h4>
				<div class="sel">
					<div class="col-xs-12">
					<div class="avg"><?php if(isset($Summary['Average']) && $Summary['Average'] > 0) echo price_fomat($Summary['Average']);  else  	echo price_fomat(0); ?> </div>
					<div class="total_trans">
						<span class="<?php if(isset($Summary['AverageDifference']) && $Summary['AverageDifference'] < 0) echo "decrement"; else  echo "increment"; ?>">
							<?php if(isset($Summary['AveragePercentage']) && $Summary['AveragePercentage'] > 0) echo $Summary['AveragePercentage']."%"; else echo "0%"; ?>
						</span> 
						<?php if(isset($Summary['AverageDifference']) && $Summary['AverageDifference'] > 0) 
								echo "(+".price_fomat(abs($Summary['AverageDifference'])).")"; 
							else if(isset($Summary['AverageDifference']))
								echo "(-".price_fomat(abs($Summary['AverageDifference'])).")";
							else
								echo price_fomat(0); 
						?>
					</div>
					</div>
				</div>			
			</div>
			<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 tot_transaction">							
				<h4 style="color:#202020;">Total transactions</h4>
				<div class="sel">
					<div class="col-xs-12 col-sm-12 col-md-12 col-xs-12">
					<div class="avg"><?php if(isset($Summary['Transaction']) && $Summary['Transaction'] > 0) echo $Summary['Transaction'];  else  	echo "0"; ?> </div>
					<div class="total_trans">
						<span class="<?php if(isset($Summary['TransactionDifference']) && $Summary['TransactionDifference'] < 0) echo "decrement"; else  echo "increment"; ?>">
							<?php if(isset($Summary['TransactionPercentage']) && $Summary['TransactionPercentage'] > 0) echo $Summary['TransactionPercentage']."%"; else echo "0%"; ?>
						</span> 
						<?php if(isset($Summary['TransactionDifference']) && $Summary['TransactionDifference'] > 0) 
								echo "(+".abs($Summary['TransactionDifference']).")"; 
							else if(isset($Summary['TransactionDifference']))
								echo "(-".abs($Summary['TransactionDifference']).")";
							else
								echo 0; 
						?>
					</div>
					</div>
				</div>			
			</div>
	<?php }  ?>						
	</div>				
	<script type="text/javascript">
	$('.chart').html('');
	<?php if(isset($error_div) && $error_div==0 && !empty($order_array)) {?>
	$(document).ready(function() {
		var barchart2 = new Morris.Bar({
							element			: 	'bar-chart1',
							resize			: 	true,
							xLabelMargin	: 	10,
							data			: 	[
													<?php 
													$extra = '';
													if(isset($xarrays) && is_array($xarrays)) {
															foreach($xarrays as $key=>$val){
															if(isset($value_arrays[$key]) && $value_arrays[$key] != ''){
																?>
														{y: '<?php echo $val;?>',a: '<?php echo $value_arrays[$key];?>'},
														<?php } }
													}?>
												],
							barColors		: 	['#01B3A5'],
							xkey			: 	'y',
							ykeys			: 	['a'],
							labels			: 	['Revenue(&pound;)'],
							hideHover		: 	'auto'
						});
	 });
	<?php } ?>
	</script>
<?php } ?>



