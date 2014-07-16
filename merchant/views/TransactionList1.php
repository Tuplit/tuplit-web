<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();

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
	echo "<pre>"; echo print_r($_POST); echo "</pre>";
}
//getting transaction list
$url					=	WEB_SERVICE.'v1/merchants/getTransactionList/';
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

$date_flag		= 0;
$month			= date('m');
$year			= date('Y');
$st_date		= date('m/d/Y', mktime(0, 0, 0, $month, 1, $year));
$end_date		= date('m/t/Y', mktime(0, 0, 0, $month, 1, $year));
$dis_st_date	= date('m/d/Y', mktime(0, 0, 0, $month, 1, $year));
$dis_end_date	= date('m/t/Y', mktime(0, 0, 0, $month, 1, $year));

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
			<div class="box box-success">
				<div class="product_list">
					<div class="dashboard_filter order_filter">
						<form name="dashboard_list_search" id="dashboard_list_search" action="orders" method="POST" >
							<div class="order_date dashboard_date float">
								<div>
									<ul class="list">
										<li class="sel"><a href="javascript:void(0);" title="Day Time" dashboard_date="timeofday" class="filter_dashboard_date">Day Time</a></li>
										<li><a href="javascript:void(0);" title="Today" dashboard_date="day" class="filter_dashboard_date">Today</a></li>
										<li><a href="javascript:void(0);" title="Last 7 days" dashboard_date="7days" class="filter_dashboard_date">Last 7 days</a></li>
										<li><a href="javascript:void(0);" title="Month" dashboard_date="month" class="filter_dashboard_date">This Month</a></li>
										<li><a href="javascript:void(0);" title="Year" dashboard_date="year" class="filter_dashboard_date">This Year</a></li>
										<input type="hidden" name="filter_dashboard_date" id="filter_dashboard_date" value="timeofday" />
										<input type="hidden" name="date_flag" id="date_flag" value="<?php echo $date_flag; ?>">
										<li class="float">
											<span class="calender">
													<span class="fleft float">From</span>
													<span id="date1"><?php echo $dis_st_date; ?></span>&nbsp;<input type="Hidden" name="st_date" id="st_date" value="<?php echo $st_date; ?>"/>
													<span>&nbsp;to&nbsp;</span>
													<span id="date2"><?php echo $dis_end_date; ?></span>&nbsp;<input type="Hidden" name="end_date" id="end_date" value="<?php echo $end_date; ?>"/>										
												</span>
										</li>
									</ul>
								</div>
							</div>
							<input type="hidden" id="cur_month_start_date" name="cur_month_start_date" value="<?php echo $st_date; ?>" />
							<input type="hidden" id="cur_month_end_date" name="cur_month_end_date" value="<?php echo $end_date; ?>" />
						</form>
					</div>
				</div>
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
			<div class="row">
            	<div class="col-xs-12 no-padding">
				   <?php if(isset($TransactionsList) && !empty($TransactionsList)) { ?>
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
									<td width="5%" class="text-right"><?php echo '$'.$value['DebitedFunds']['Amount']; ?></td>
									<td width="5%" class="text-right"><?php echo '$'.$value['CreditedFunds']['Amount']; ?></td>
									<td width="5%" class="text-right"><?php echo '$'.$value['Fees']['Amount'];//echo $value['DebitedFunds']['Currency'].' '.$value['Fees']['Amount']; ?></td>
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
	$(function() {
		$( "#st_date" ).datepicker({
			dateFormat		:	'mm/dd/yy',
			showOn: "button",
			buttonImage: "../merchant/webresources/images/caleder_icon.png",
			buttonImageOnly: true,
			onSelect: function()
			{
				var date_val = $('#st_date').datepicker().val();
				date_val = dateDisplayFormat(date_val);
				$('#date1').html(date_val);
				$('#date_flag').val(1);
				$('#filter_dashboard_date').val('between');
				$('.filter_dashboard_date').parent().removeClass('sel');
			}
		});
		$( "#end_date" ).datepicker({
			dateFormat		:	'mm/dd/yy',
			showOn: "button",
			buttonImage: "../merchant/webresources/images/caleder_icon.png",
			buttonImageOnly: true,
			onSelect: function()
			{
				var date_val = $('#end_date').datepicker().val();
				date_val = dateDisplayFormat(date_val);
				$('#date2').html(date_val);
				$('#date_flag').val(1);
				$('#filter_dashboard_date').val('between');
				$('.filter_dashboard_date').parent().removeClass('sel');
			}
		});
	});
	$(".filter_dashboard_date").click(function() {
			$('.filter_dashboard_date').parent().removeClass('sel');
			$(this).parent().addClass('sel');
			var dashboard_date_type = $(this).attr('dashboard_date');
			$('#filter_dashboard_date').val(dashboard_date_type);
			var start_date = $('#cur_month_start_date').val();
			var end_date = $('#cur_month_end_date').val();
			var format_start_date = dateDisplayFormat(start_date);
			var format_end_date = dateDisplayFormat(end_date);
			$('#date1').html(format_start_date);
			$('#date2').html(format_end_date);
			$('#st_date').val(start_date);
			$('#end_date').val(end_date);
	});
</script>
	
</html>
