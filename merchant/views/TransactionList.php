<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();
$condition	=	'';
//$from_date	=	date('m/d/Y');
//$to_date		=	date('m/d/Y');

//getting merchant details
if(isset($_SESSION['merchantDetailsInfo']) && is_array($_SESSION['merchantDetailsInfo'])){
	$merchantInfo  =	$_SESSION['merchantDetailsInfo'];	
}
else{
	$merchantId					= 	$_SESSION['merchantInfo']['MerchantId'];
	$url						=	WEB_SERVICE.'v1/merchants/'.$merchantId;
	$curlMerchantResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);	
	if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201 && $curlMerchantResponse['merchant']['MerchantId'] != '' ) 
	 {
		$_SESSION['merchantDetailsInfo']   =	$curlMerchantResponse['merchant'];
		$merchantInfo  =	$_SESSION['merchantDetailsInfo'];
	}
}

if(isset($_POST['Search']) && !empty($_POST['Search'])) {
	$from_date	=	$_POST['from_date'];
	$to_date	=	$_POST['to_date'];
	if(isset($_POST['from_date']) && !empty($_POST['from_date']) && isset($_POST['to_date']) && !empty($_POST['to_date'])) {
		$condition		=	"?Start=".strtotime($_POST['from_date'])."&End=".strtotime($_POST['to_date']);
	} else if(isset($_POST['from_date']) && !empty($_POST['from_date']) && isset($_POST['to_date']) && empty($_POST['to_date'])) {
		$condition		=	"?Start=".strtotime($_POST['from_date']);
	} else if(isset($_POST['from_date']) && empty($_POST['from_date']) && isset($_POST['to_date']) && !empty($_POST['to_date'])) {
		$condition		=	"?Start=".strtotime($_POST['to_date']);
	}
	if(isset($_POST['Status']) && !empty($_POST['Status'])) {
		if(!empty($condition))
			$condition		.=	"&Status=".$_POST['Status'];
		else
			$condition		=	"?Status=".$_POST['Status'];
		$status	=	$_POST['Status'];
	}
}

//getting transaction list
$url					=	WEB_SERVICE.'v1/merchants/getTransactionList/'.$condition;
//echo $url;
$curlCustomerResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
if(isset($curlCustomerResponse) && is_array($curlCustomerResponse) && $curlCustomerResponse['meta']['code'] == 201 && is_array($curlCustomerResponse['TransactionsList']) ) {
	if(isset($curlCustomerResponse['TransactionsList'])){
		$TransactionsList 	= $curlCustomerResponse['TransactionsList'];
		//echo "<pre>"; echo print_r($TransactionsList); echo "</pre>";
	}
} else if(isset($curlCustomerResponse['meta']['errorMessage']) && $curlCustomerResponse['meta']['errorMessage'] != '') {
		$errorMessage	=	$curlCustomerResponse['meta']['errorMessage'];
} else {
		$errorMessage	= 	"Bad Request";
} 

commonHead();
?>

<body class="skin-blue fixed">	
		<?php  top_header(); ?>
		<section class="content no-padding">
			<div class="col-lg-10 box-center">	
				<?php  AnalyticsTab(); ?>
			</div>
		</section>
		<section class="content no-top-padding">
		<div class="col-lg-10" style="margin:auto;float:none;" >	
			<section class="row content-header">
                <h1 class="no-top-margin pull-left">Transactions List</h1>
            </section>	
			<div class="row">
				<div class="product_list">
					<form name="search_merchant" action="TransactionList?cs=1" method="post">
					<div class="box box-primary">
						<div class="box-body no-padding" >				
							<div class="col-sm-2 form-group">
								<label>Start Date</label>
								<input  type="text" id = "from_date" class="form-control datepicker" autocomplete="off" title="Select Date" name="from_date" value="<?php if(isset($from_date) && $from_date != '') echo $from_date;?>" onchange="return emptyDates(this);">
							</div>
						</div>
						<div class="box-body no-padding" >				
							<div class="col-sm-2 form-group">
								<label>End Date</label>
								<input type="text" id = "to_date" class="form-control datepicker" autocomplete="off"  title="Select Date" name="to_date" value="<?php if(isset($to_date) && $to_date != '') echo $to_date;?>" onchange="return emptyDates(this);">
							</div>
						</div>
						<div class="box-body no-padding" >				
							<div class="col-sm-2 form-group">
								<label>Status</label>
								<select id="Status" class="form-control" name="Status">
									<option value="">Select</option>
									<option value="1" <?php if(isset($status) && $status == 1) echo "selected"; ?>>Success</option>
									<option value="2" <?php if(isset($status) && $status == 2) echo "selected"; ?>>Failed</option>
								</select>
							</div>
						</div>
						<div class="box-footer col-sm-12" align="center">
							<input type="submit" class="btn btn-success" name="Search" id="Search" value="Search" title="Search">
						</div>					
					</div>
					</form>
				</div>
			<!--<div class="row product_list paging">
					<?php //if(isset($customerList) && is_array($customerList) && count($customerList) > 0){ ?>
					<div class="col-xs-12 col-sm-3 no-padding">
						<span class="totl_txt">Total Customer(s) : <b><?php //echo $tot_rec; ?></b>
							
						</span>
					</div>
					<div class="col-xs-12 col-sm-9 no-padding">
						<div class="dataTables_paginate paging_bootstrap row no-margin">
								<?php //pagingControlLatestAjax($tot_rec,'CustomerList'); ?>
						</div>
					</div>
					<?php //} ?>
			</div>-->
			<div class="clear">
            	<div class="col-xs-12 no-padding">
				   <?php if(isset($TransactionsList) && !empty($TransactionsList)) { 
						$TransactionsList = subval_sort($TransactionsList,'Id',1);
				   ?>
		              <div class="box">
		               <div class="box-body table-responsive no-padding no-margin">
						<table class="table table-hover">
                               <tr>
									<th align="center" width="3%" class="text-center">#</th>									
									<th width="10%">Transaction ID</th>
									<th width="7%">Date</th>
									<th width="7%">Time</th>
									<th width="10%">Customer Id</th>
									<th width="10%">Customer Name</th>
									<th width="5%" class="text-right">Amount Debited</th>
									<th width="5%" class="text-right">Amount Credited</th>
									<th width="5%" class="text-right">Transaction Fee</th>
									<th width="7%" class="text-right">Status</th>
								</tr>
                              <?php $count	=	0;
							  	foreach($TransactionsList as $key=>$value){									
									$count 	+= 1;
								?>
								<tr>
									<td align="center" width="3%" class="text-center"><?php echo $count; ?></td>									
									<td width="10%"><?php echo $value['Id']; ?></td>
									<td width="7%"><?php echo date("d-m-Y", $value['CreationDate']); ?></td>
									<td width="7%"><?php echo date("H:i:s", $value['CreationDate']); ?></td>
									<td width="10%"><?php echo $value['AuthorId']; ?></td>
									<td width="10%"><?php if(!empty($value['Customer'])) echo $value['Customer']; else echo "Test Transaction"; ?></td>
									<td width="5%" class="text-right"><?php echo '<b>'.price_fomat($value['DebitedFunds']['Amount'])."</b>"; ?></td>
									<td width="5%" class="text-right"><?php echo '<b>'.price_fomat($value['CreditedFunds']['Amount'])."</b>"; ?></td>
									<td width="5%" class="text-right"><?php echo '<b>'.price_fomat($value['Fees']['Amount'])."</b>";//echo $value['DebitedFunds']['Currency'].' '.$value['Fees']['Amount']; ?></td>
									<td width="7%" class="text-right"><?php 
										if($value['Status'] == 'SUCCEEDED')
											echo "<b style='color: #01a99a;'>".$value['Status']."</b>"; 
										else if($value['Status'] == 'FAILED')
											echo "<b style='color: #FF4747;'>".$value['Status']."</b>";
										?>
									</td>
								</tr>
							<?php } //end for ?>	
                           </table>
							<!-- End product List -->						 
						<?php } else { ?>
							<div class="row clear">		
								 <div align="center" class="alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10"><i class="fa fa-warning"></i> <?php echo $errorMessage	;?>	</div>							
							</div>							
						<?php } ?>						
					</div><!-- /.box-body -->
				</div>					
			</div>	
		 </div>
		</section>
		
		<?php footerLogin(); ?>
		<?php commonFooter(); ?>
<script type="text/javascript">
	$(".datepicker").datepicker({
			showButtonPanel	:	true,        
		    buttonText		:	'<i class="fa fa-calendar"></i>',
		    buttonImageOnly	:	true,
		    buttonImage		:	path+'webresources/images/calender.png',
		    dateFormat		:	'mm/dd/yy',
			changeMonth		:	true,
			changeYear		:	true,
			hideIfNoPrevNext:	true,
			showWeek		:	true,
			yearRange		:	"c-30:c",
			closeText		:   "Close"
		   });
		 function emptyDates(arg) { 
			var id = arg.getAttribute('name');		
			if(id == 'year' || id == 'month') {
				$('#from_date').val('');
				$('#to_date').val('');		
			}
			else {
				$("#year").children("option[value ='']").attr('selected', true);
				$("#month").children("option[value ='']").attr('selected', true);
			}
		 }
</script>
	
</html>
