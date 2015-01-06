<?php
unset($_SESSION['orderList']);
require_once('includes/CommonIncludes.php');
$summary_trans	=	$summary_sales	=	$summary_refunds	= $summary_refunded	=	0;
$start_count = $start = '';
$Start	= '';
$spent = $commission = 0;
$OrderStatus	= $DataType = $FromDate = $ToDate = '';
$stype	=	1;
if(isset($_POST['action']) && $_POST['action']=='GET_TRANSACTIONS') { 
	if(isset($_POST['dataType']) && $_POST['dataType'] != '') {
		$DataType		=	$_POST['dataType'];
		if(isset($_POST['Showtype']) && $_POST['Showtype'] == 1) {
			$_SESSION['TuplitAnalyticsView']	=	$DataType;
			$stype		=	$_POST['Showtype'];
		}
	}
	if(isset($_POST['Starts']) && $_POST['Starts'] != '')					$Start			=	$_POST['Starts'];
	if(isset($_POST['OrderStatus']) && $_POST['OrderStatus'] != '')			$OrderStatus	=	$_POST['OrderStatus'];
	if(isset($_POST['FromDate']) && $_POST['FromDate'] != '')				$FromDate		=	$_POST['FromDate'];
	if(isset($_POST['ToDate']) && $_POST['ToDate'] != '')					$ToDate			=	$_POST['ToDate'];	
}
$totalRecords	 = '';

//getting comment list of users
$url					=	WEB_SERVICE."v1/orders/transactions/?FromDate=".$FromDate."&ToDate=".$ToDate."&Start=".$Start."&DataType=".$DataType."&OrderStatus=".$OrderStatus;
$curlOrderResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
if(isset($curlOrderResponse) && is_array($curlOrderResponse) && $curlOrderResponse['meta']['code'] == 201 && is_array($curlOrderResponse['OrderList']) ) {
	if(isset($curlOrderResponse['OrderList'])){
		$orderList 	  	= 	$curlOrderResponse['OrderList'];	
		$_SESSION['orderList'] = $orderList;
		$totalRecords  	= 	$curlOrderResponse['meta']['totalCount'];
		if(isset($curlOrderResponse['meta']['transactions'])) 			$Summary['transactions'] 	= $curlOrderResponse['meta']['transactions'];
		if(isset($curlOrderResponse['meta']['sales'])) 					$Summary['sales'] 			= $curlOrderResponse['meta']['sales'];
		if(isset($curlOrderResponse['meta']['refunds'])) 				$Summary['refunds'] 		= $curlOrderResponse['meta']['refunds'];
		if(isset($curlOrderResponse['meta']['refunded'])) 				$Summary['refunded'] 		= $curlOrderResponse['meta']['refunded'];	
	}
} else if(isset($curlOrderResponse['meta']['errorMessage']) && $curlOrderResponse['meta']['errorMessage'] != '') {
		$errorMessage	=	$curlOrderResponse['meta']['errorMessage'];
} else {
		$errorMessage	= 	"Bad Request";
}
?>
<?php if(isset($orderList) && !empty($orderList) && count($orderList) > 0) { 
	if(isset($Summary['transactions']) && !empty($Summary['transactions']) ){
		$summary_trans 	=	$Summary['transactions'];
	}
	if(isset($Summary['sales']) && !empty($Summary['sales']) ){
		$summary_sales 	=	$Summary['sales'];
	}
	if(isset($Summary['refunds']) && !empty($Summary['refunds']) ){
		$summary_refunds 	=	$Summary['refunds'];
	}
	if(isset($Summary['refunded']) && !empty($Summary['refunded']) ){
		$summary_refunded 	=	$Summary['refunded'];
	}
}


?>
<form name="AjaxCustomerTransaction" action="CustomerTransaction" id="AjaxCustomerTransaction" method="POST" ></form>
<div class="col-xs-12 col-sm-3 col-md-3 col-lg-2 form-group" style="padding-top:12px;">
	<label class="valign">Transactions summary<label>
</div>
<div class="col-xs-3 col-sm-2 col-md-2 col-lg-2 no-padding form-group text-center">
	<label class="col-sm-12 col-md-12 col-lg-12 col-xs-12 control-label no-padding"><h3 class="no-margin text-color"><?php echo $summary_trans; ?></h3></label>
	<p class="help-block col-sm-12 no-padding">Transactions</p>
</div>
<div class="col-xs-3 col-sm-2 col-md-2 col-lg-2 no-padding form-group text-center">
	<label class="col-sm-12 col-md-12 col-lg-12 col-xs-12 control-label no-padding"><h3 class="no-margin text-color"><?php echo price_fomat($summary_sales); ?></h3></label>
	<p class="help-block col-sm-12 no-padding">Sales</p>
</div >
<div class="col-xs-3 col-sm-2 col-md-1 col-lg-2 no-padding form-group text-center">
<label class="col-sm-12 col-md-12 col-lg-12 col-xs-12 control-label no-padding"><h3 class="no-margin text-color"><?php echo $summary_refunds; ?></h3></label>
	<p class="help-block col-sm-12 no-padding">Refunds</p>
</div>
<div class="col-xs-3 col-sm-2 col-md-2 col-lg-2 no-padding form-group text-center">
	<label class="col-sm-12 col-md-12 col-lg-12 col-xs-12 control-label no-padding"><h3 class="no-margin text-color"><?php echo price_fomat($summary_refunded); ?></h3></label>
	<p class="help-block col-sm-12 no-padding">Refunded</p>
</div>
 <?php if(isset($orderList) && !empty($orderList) && count($orderList) > 0) { ?>
<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2" align="">
	<input type="Button" class="btn btn-success export_csv" name="export_csv" onclick="exportExcelSubmit('exporttocsv');" id="export_csv" value="Export CSV" title="Export CSV">
	&nbsp;&nbsp;&nbsp;
</div>
<?php } ?>

<div class="clear" style="padding-top:0px;">
	<div class="col-xs-12 no-padding transcationlist">
	   <?php if(isset($orderList) && !empty($orderList)) { 
		 ?>
		  <div class="box">
		   <div class="box-body table-responsive no-padding no-margin">
			<table class="table table-hover">
				<tr>
					<th class="text-center"width="3%" >#</th>									
					<th class="text-center" width="5%">Transaction ID</th>
					<th class="text-center" width="10%">Date & Time</th>
					<th class="text-center" width="10%">Consumer</th>
					<th width="10%" class="text-center">Spent</th>
					<th width="10%" class="text-center">Commission</th>
					<th width="10%" class="text-center">Total</th>
					<th class="text-center" width="10%" class="">Status</th>
					<th class="text-center" width="10%" class="">Refund</th>
				</tr>
				  <?php $count	=	$Start;
						foreach($orderList as $key=>$value){
							$gmt_current_created_time = convertIntocheckinGmtSite($value['OrderDate'],$_SESSION['tuplit_ses_from_timeZone']);
							$count 	+= 1;
							if(isset($value['TotalPrice']) && !empty($value['TotalPrice'])) 
								$spent		= $value['TotalPrice']; 
							if(isset($value['Commision']) && !empty($value['Commision']))
								$commission	= $value['Commision']; 
							$total			=	$spent - $commission;
							
						?>
						<tr class="LTstdmedium">
							<td align="center" width="3%" class="text-center"><?php echo $count; ?></td>									
							<td align="center" ><?php if(isset($value['TransactionId']) && $value['TransactionId']!='') { ?>
							<a class="fancybox fancybox.iframe fancybox1 refund" title="View transaction details" href="OrderProductDetail?cs=1&show=1&transId=<?php echo  $value['TransactionId']; ?>"><?php echo $value['TransactionId']; ?></a>
							<?php } else echo "#";?></td>
							<td align="center"><?php echo date('d M Y H:i:s',strtotime($gmt_current_created_time)); //echo "<br>".date('d F Y H:i:s',strtotime($value['OrderDate']));?></td>
							<td align="center">
									<?php if(isset($value['FirstName']) && !empty($value['FirstName'])) echo $value['FirstName']." "; 
										  if(isset($value['LastName']) && !empty($value['LastName'])) echo $value['LastName'];
									?></td>
							<td class="text-center"><?php echo price_fomat($spent); ?></td>
							<td class="text-center"><?php echo price_fomat($commission); ?></td>
							<td class="text-center"><?php echo price_fomat($total); ?></td>
							<td align="center">
								<?php 
								if(isset($value['OrderStatus']) && $value['OrderStatus'] != ''){
									if(isset($value['RefundStatus']) && $value['RefundStatus'] != 2){
										if($value['OrderStatus'] == 0){
											echo "<div class='trans_new'>New</div>";
										}else if($value['OrderStatus'] == 1){
											echo "<div class='trans_completed'>Completed</div>";
										}else if($value['OrderStatus'] == 2){
											echo "<div class='trans_rejected'>Rejected</div>";
										}
									}else{	
										echo "Refunded";
									}
								}
								?>
							</td>
							<td align="center">
								<?php
									if(isset($value['OrderStatus']) && $value['OrderStatus'] != '' && isset($value['TransactionId']) && $value['TransactionId']!=''){
										if(($value['OrderStatus'] == 0 && isset($value['OrderDoneBy']) && $value['OrderDoneBy'] == 1) ||  $value['OrderStatus'] == 1){ ?>
											<a class="fancybox fancybox.iframe fancybox2 refund" title="View transaction details" href="OrderProductDetail?cs=1&transId=<?php echo  $value['TransactionId']; ?>">Refund</a>
								<?php	}	}	?>
							</td>
						</tr>
					<?php } //end for ?>	
				   </table>
					<!-- End product List -->						 
				<?php } else { ?>
					<div class="row clear">		
						 <div align="center" class="alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10" style="margin:90px auto;"><i class="fa fa-warning"></i> <?php echo 'No result found';?>	</div>							
					</div>							
				<?php } ?>						
			</div><!-- /.box-body -->
		</div>					
	</div>	
 </div>
<div class="customer_trans">
	<div class="col-xs-12 next-prev">
	<form method="post" action="CustomerTransaction">		
		<?php if($Start != 0) { ?> <div class="col-xs-6 col-sm-6" align="center"><a class="" id="Previous" title="Previous" onclick="getTransactions('','',4,'','<?php echo $stype; ?>');">PREVIOUS</a></div><?php } ?>
		<?php $chStart = $Start + 10; if($chStart < $totalRecords) { ?> <div class="col-xs-6 col-sm-6" align="center" style="float:right;"><a class="" id="Next" title="Next" onclick="getTransactions('','',5,'','<?php echo $stype; ?>');">NEXT</a><?php } ?>
	</form>
</div>
</div>
<script type="text/javascript">
	$(document).ready(function(){
		$('#TotalRecordsHide').val(<?php echo $totalRecords; ?>);
		$('.fancybox1').fancybox();
		$('.fancybox2').fancybox({
			afterClose : function() {
							getTransactions('','',7,'','<?php echo $stype; ?>');
							return;
						}
		});
	});
</script>
	