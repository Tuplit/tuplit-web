<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();
$condition	=	'';

if(isset($_GET['cs']) && $_GET['cs'] == 1) {
	unset($_SESSION['tuplit_transactionlist_condition']);
}

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
$from_date	=	date("m/d/Y",strtotime("-1 month"));
$to_date	=	date("m/d/Y",strtotime("+1 day"));
if((isset($_POST['Search']) && !empty($_POST['Search'])) || (isset($_POST['export-excel']) && $_POST['export-excel'] == 1)) {
	if(isset($_POST['from_date']) && !empty($_POST['from_date']))
		$from_date	=	$_POST['from_date'];
	if(isset($_POST['to_date']) && !empty($_POST['to_date']))
		$to_date	=	$_POST['to_date'];
}
if(isset($from_date) && !empty($from_date) && isset($to_date) && !empty($to_date)) {
	$condition		=	"?Start=".strtotime($from_date)."&End=".strtotime($to_date);
} else if(isset($from_date) && !empty($from_date) && isset($to_date) && empty($to_date)) {
	$condition		=	"?Start=".strtotime($from_date);
} else if(isset($from_date) && empty($from_date) && isset($to_date) && !empty($to_date)) {
	$condition		=	"?Start=".strtotime($to_date);
}
if(isset($_POST['Status']) && !empty($_POST['Status'])) {
	if(!empty($condition))
		$condition		.=	"&Status=".$_POST['Status'];
	else
		$condition		=	"?Status=".$_POST['Status'];
	$status	=	$_POST['Status'];
}
if(isset($_POST['Nature']) && !empty($_POST['Nature'])) {
	if(!empty($condition))
		$condition		.=	"&Nature=".$_POST['Nature'];
	else
		$condition		=	"?Nature=".$_POST['Nature'];
	$nature	=	$_POST['Nature'];
}
$_SESSION['tuplit_transactionlist_condition']	=	$condition;

//getting transaction list
$url					=	WEB_SERVICE.'v1/merchants/transactionlist/'.$condition;
$curlCustomerResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
if(isset($curlCustomerResponse) && is_array($curlCustomerResponse) && $curlCustomerResponse['meta']['code'] == 201 && is_array($curlCustomerResponse['TransactionsList']) ) {
	if(isset($curlCustomerResponse['TransactionsList'])){
		$TransactionsList 	= $curlCustomerResponse['TransactionsList'];
	}
} else if(isset($curlCustomerResponse['meta']['errorMessage']) && $curlCustomerResponse['meta']['errorMessage'] != '') {
		$errorMessage	=	$curlCustomerResponse['meta']['errorMessage'];
} else {
		$errorMessage	= 	"Bad Request";
} 

/* FOR CSV export */
if(isset($_POST['export-excel']) && $_POST['export-excel'] == 1)
{
	if(is_array($TransactionsList)){
		$TransactionsExport		=	array();
		foreach($TransactionsList as $key=>$value){	
			$TransactionsExport[$key]['Transaction ID']		=	$value['Id'];
			$TransactionsExport[$key]['Date']				=	date("m/d/Y", $value['CreationDate']);
			$TransactionsExport[$key]['Time']				=	date("H:i:s", $value['CreationDate']);
			
			if(!empty($value['Customer'])) 
				$TransactionsExport[$key]['Customer Name']	=	$value['Customer']; 
			else 
				$TransactionsExport[$key]['Customer Name']	=	"Test Transaction";
			
			$TransactionsExport[$key]['Amount Debited']		=	price_fomat_export($value['DebitedFunds']['Amount']/100);
			$TransactionsExport[$key]['Amount Credited']	=	price_fomat_export($value['CreditedFunds']['Amount']/100);
			$TransactionsExport[$key]['Transaction Fee']	=	price_fomat_export($value['Fees']['Amount']/100);
			$TransactionsExport[$key]['Nature']				=	$value['Nature'];
			$TransactionsExport[$key]['Status']				=	$value['Status'];
			$TransactionsExport[$key]['Status Message']		=	$value['ResultMessage'];
		}
		$TransactionsExport = subval_sort($TransactionsExport,'Transaction ID',1);
		csvDownload($TransactionsExport,'TuplitTransactionList.csv');
	}
}
commonHead();
?>

<body class="skin-blue fixed body_height">	
		<?php  top_header(); ?>
		<?php if(isset($merchantInfo['MangoPayUniqueId']) && !empty($merchantInfo['MangoPayUniqueId'])) { ?>
		<section class="content top-spacing">
		<div class="col-md-12 col-lg-12 box-center row">	
			<section class="row content-header col-xs-12">
                <h1 class="no-top-margin pull-left">Transactions List</h1>
            </section>	
			
			<div class="clear">
				<div class="product_list">
					<form name="search_transaction" id="search_transaction" action="TransactionList?cs=1" method="post">
					<div class="box box-primary">
						<div class="box-body" >				
							<div class="col-sm-4  col-md-2 form-group">
								<label>After Date</label>
								<input  type="text" id = "from_date" class="form-control datepicker" autocomplete="off" title="Select Date" name="from_date" value="<?php if(isset($from_date) && $from_date != '') echo $from_date;?>" onchange="return emptyDates(this);">
							</div>
							<div class="col-sm-4 col-md-2 form-group">
								<label>Before Date</label>
								<input type="text" id = "to_date" class="form-control datepicker" autocomplete="off"  title="Select Date" name="to_date" value="<?php if(isset($to_date) && $to_date != '') echo $to_date;?>" onchange="return emptyDates(this);">
							</div>
							<div class="col-sm-4 col-md-2 form-group">
								<label>Status</label>
								<select id="Status" class="form-control" name="Status">
									<option value="">Select</option>
									<option value="1" <?php if(isset($status) && $status == 1) echo "selected"; ?>>Success</option>
									<option value="2" <?php if(isset($status) && $status == 2) echo "selected"; ?>>Failed</option>
								</select>
							</div>
							<div class="col-sm-4 col-md-2 form-group">
								<label>Transaction Type</label>
								<select id="Nature" class="form-control" name="Nature">
									<option value="">Select</option>
									<option value="REGULAR" <?php if(isset($nature) && $nature == 'REGULAR') echo "selected"; ?>>Regular</option>
									<option value="REFUND" <?php if(isset($nature) && $nature == 'REFUND') echo "selected"; ?>>Refund</option>
								</select>
							</div>
						</div>
						<div class="box-footer col-sm-12" align="center">
							<input type="submit" class="btn btn-success" name="Search" id="Search" value="Search" title="Search">
						</div>					
					</div>
					</form>
				</div>
			</div>
			 <?php if(isset($TransactionsList) && !empty($TransactionsList) && count($TransactionsList) > 0) { ?>
			<div class="box-footer col-sm-12 no-padding pull-right" align="right" style="margin-top:10px;">
				<input type="Button" class="btn btn-success" name="export_csv" onclick="exportExcelSubmit('search_transaction');" id="export_csv" value="Export CSV" title="Export CSV">
				&nbsp;&nbsp;&nbsp;<a href="PrintTransactionList" target="_blank"><input type="Button" class="btn btn-success" name="print" onclick="" id="print" value="Print" title="Print"></a>
			</div>
			<?php } ?>
			<div class="clear" style="padding-top:10px;">
            	<div class="col-xs-12 no-padding transcationlist">
				   <?php if(isset($TransactionsList) && !empty($TransactionsList)) { 
						$TransactionsList = subval_sort($TransactionsList,'Id',1);
				   ?>
		              <div class="box">
		               <div class="box-body table-responsive no-padding no-margin">
						<table class="table table-hover">
                               <tr>
									<th align="center" width="3%" class="text-center">#</th>									
									<th width="10%">Transaction ID</th>
									<th width="5%">Date</th>
									<th width="5%">Time</th>
									<!--<th width="10%">Customer Id</th>-->
									<th width="10%">Customer Name</th>
									<th width="5%" class="text-right">Amount Debited</th>
									<th width="5%" class="text-right">Amount Credited</th>
									<th width="5%" class="text-right">Transaction Fee</th>
									<th width="5%" class="">Nature</th>
									<th width="5%" class="">Status</th>
									<th width="12%">Status Message</th>
									<th align="center" width="3%">Action</th>
								</tr>
                              <?php $count	=	0;
							  	foreach($TransactionsList as $key=>$value){	
									//echo "<pre>"; echo print_r($value); echo "</pre>";
									$count 	+= 1;
								?>
								<tr>
									<td align="center" width="3%" class="text-center"><?php echo $count; ?></td>									
									<td><?php echo $value['Id']; ?></td>
									<td><?php echo date("m/d/Y", $value['CreationDate']); ?></td>
									<td><?php echo date("H:i:s", $value['CreationDate']); ?></td>
									<!--<td><?php echo $value['AuthorId']; ?></td>-->
									<td><?php if(!empty($value['Customer'])) echo $value['Customer']; else echo "Test Transaction"; ?></td>
									<td class="text-right"><?php echo '<b>'.price_fomat($value['DebitedFunds']['Amount']/100)."</b>"; ?></td>
									<td class="text-right"><?php echo '<b>'.price_fomat($value['CreditedFunds']['Amount']/100)."</b>"; ?></td>
									<td class="text-right"><?php echo '<b>'.price_fomat($value['Fees']['Amount']/100)."</b>";//echo $value['DebitedFunds']['Currency'].' '.$value['Fees']['Amount']; ?></td>
									<td class=""><?php echo $value['Nature']; ?></td>
									<td class=""><?php 
										if($value['Status'] == 'SUCCEEDED')
											echo "<b class='text-teal'>".$value['Status']."</b>"; 
										else if($value['Status'] == 'FAILED')
											echo "<b class='text-red'>".$value['Status']."</b>";
										//echo $value['Status'];
										?>
									</td>
									<td><?php echo $value['ResultMessage']; ?></td>
										<td align="center">
											<?php if($value['Nature'] != 'REFUND') { ?>
											<a class="fancybox fancybox.iframe" title="View transaction details" href="OrderProductDetail?cs=1&transId=<?php echo  $value['Id']; ?>"><i class="fa fa-search fa-lg"></i></a>
											<?php } else echo '-'; ?>
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
		 <?php } else { ?>
			<div class="row clear">		<br><br>
				 <div align="center" class="alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10"><i class="fa fa-warning"></i> Please connect with MangoPay in Settings to view transactions.</div>							
			</div>	
		 <?php } ?>
		
		
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
	$(document).ready(function() {
			$('.fancybox').fancybox();
			

			/*$(".newWindow").click(function() {
				$.fancybox.open({
					href : 'iframe.html',
					type : 'iframe',
					padding : 5
				});
			});*/
				
			/*$(".newWindow").fancybox({
				scrolling: 'auto',			
				'type' : 'inline',
				width: '800',
				height: '800',
				maxWidth: '100%',	
				title: null,			
				fitToView: false
			});	*/
		});
</script>
	
</html>
