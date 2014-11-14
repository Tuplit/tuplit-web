<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
commonHead();
require_once('controllers/AnalyticsController.php');
$analyticsObj   =   new AnalyticsController();
require_once('controllers/OrderController.php');
$OrderObj   	=   new OrderController();
require_once('controllers/MerchantController.php');
$merchantObj   	=   new MerchantController();
require_once('controllers/ManagementController.php');
$managementObj  =   new ManagementController();
$searchCond		=	$cond 	= $condition = $search_merchant = '';
$search_city 	=  $search_category = $search_price = '';
$having			=  $transCond = $leftjoin = $lj= '';
$limit			= 0;
$type			= 2;
if(isset($_GET['cs']) && $_GET['cs'] == 1){
	unset($_SESSION['loc_mer_name']);
	unset($_SESSION['loc_mer_price']);
	unset($_SESSION['loc_mer_city']);
	unset($_SESSION['loc_mer_category']);
}
if(isset($_POST['Search'])){
	if(isset($_POST['Merchant_Name']) && $_POST['Merchant_Name']!= ''){
		$_SESSION['loc_mer_name'] 	 = $_POST['Merchant_Name'];
		$cond						.= " and m.id = ".trim($_SESSION['loc_mer_name']);
		$condition					.= " and m.id = ".trim($_SESSION['loc_mer_name']);
	}else unset($_SESSION['loc_mer_name']);
	if(isset($_POST['Merchant_Price']) && $_POST['Merchant_Price']!= ''){
		$_SESSION['loc_mer_price'] 	 =  $_POST['Merchant_Price'];
		//$cond						.= " and o.TotalPrice = ".trim($_SESSION['loc_mer_price']);
		$cond 						.= " and ".$_SESSION['loc_mer_price']." between substring_index(m.PriceRange,',',1) and substring_index(m.PriceRange,',',-1)"; 
		$having						.= " Having TotalPrice <= ".trim($_SESSION['loc_mer_price']);
	}else unset($_SESSION['loc_mer_price']);
	if(isset($_POST['Merchant_city']) && $_POST['Merchant_city']!= ''){
		$_SESSION['loc_mer_city'] 	 = $_POST['Merchant_city'];
		$cond		.= $condition	.= $transCond	.= " and m.City LIKE '%".trim($_SESSION['loc_mer_city'])."%'";
	}else unset($_SESSION['loc_mer_city']);
	if(isset($_POST['Merchant_Category']) && $_POST['Merchant_Category']!= ''){
		$_SESSION['loc_mer_category'] = $_POST['Merchant_Category'];
		$cond		.= $condition	 .= $transCond	.= " and c.id = ".trim($_SESSION['loc_mer_category']);
	}else unset($_SESSION['loc_mer_category']);
}

/*-----------------Common Transaction List---------------------*/
$limit				= 0;
$fields    			= " o.TotalPrice,o.TransactionId,o.OrderDate,o.Status,m.CompanyName,u.FirstName,u.LastName,o.Commision ";
$sort				= " o.id desc ";
$leftjoin			= " left join users as u on  (u.id	= o.fkUsersId ) left join merchants as m on (m.id = o.fkMerchantsId)";
if(isset($_SESSION['loc_mer_category']) && $_SESSION['loc_mer_category']!=''){
	$leftjoin		.= "left join merchantcategories as mc on (m.id = mc.fkMerchantId) left join categories as c on (c.id = mc.fkCategoriesId)";
}
$OrderListResult  	= $OrderObj->getTransactionDetails($fields,$leftjoin,$transCond,$sort,$limit,$type);
$fields 			= '';
$analyticsList  	= $analyticsObj->getCustomerReport($fields,$condition,$having,$limit,$type,$lj);
$tot_rec 			= $analyticsObj->getTotalRecordCount();
$fields				= "group_concat(fkUsersId) as userArray,c.CategoryName,mc.fkCategoriesId,count(o.id) as transCount,count(distinct fkUsersId) as uniqueCustomers,fkMerchantsId,sum(TotalPrice) as TotalPrice,Min(OrderDate) as FirstTrans,m.City";
$locationResult		= $analyticsObj->getLocationReport($fields,$condition,$having);
function load(){
	require_once('PieChart.php');
}
	
?>
<body class="skin-blue" onload="">
<?php  top_header(); ?>
<?php 
	$formStatus = 4;
	require_once("ReportSearchBox.php");
?>
	
	<!-- Main content -->
<section class="content Report-overview">
	<div class="row">
		<!-- left column -->
		<div class="col-md-12"><!--  col-lg-6 -->
			<!-- TRANSACTIONS HISTORY -->
			<div class="box box-primary"><!--Category-manage sett-menu -->
				<h2 class="tabsection" id="transaction">Transaction History</h2>
				<div id="transaction_block" style="display:none">
					<form action="" class="l_form" name="transaction" id="transaction"  method="post">
						<div id="trans_hist_List" class="transaction_list">
						<?php 	
							$i				= 0 ; 
							$_GET['start'] 	= 0;
							$type			= 2;
							require_once("TransactionHistoryDetails.php");
						?>
						</div>
					</form>
				</div>
			</div><!-- TRANSACTIONS HISTORY END-->	
				
			<!-- PERFORMANCE-->
			<div class="box box-primary">
				<h2  class="tabsection" id="perfamence">Performance / Demographics</h2>
				<div class="overview-perfamence" id="perfamence_block" style="display:block">
					
					<div class="col-sm-6 col-xs-12">
						<h2>Performance</h2>					
							<div class="top-header">
								<h3>Percentage graphic</h3>
								<div class="month-week">
									<a id="bar_week" onclick="get_areachart(this.id,option='1');" class="area-chart chart-active">Week</a>
									<a id="bar_month" onclick="get_areachart(this.id,option='2');" class="area-chart">Month</a>
								</div>
							</div>
							<div id="bar-chart"><!--style="height:700px;width:700px;"-->
								<?php
									require_once('controllers/OrderController.php');
									$orderObj   	=   new OrderController();
									$_GET['value']	= 3;
									$_GET['action']	= "GET_BARCHART_DATA";
									require_once('ChartContent.php');
								?>
							</div>					
					</div>
					<div class="col-sm-6 col-xs-12">
						<h2>Demographics</h2>
						<h3>Male/Female Breakdown</h3>
						<div id="pie-chart">
							<?php 
								//$_GET['value']	= 3;
								//$_GET['action']	= "GET_BARCHART_DATA";
								require_once('PieChart.php');
							?>
						</div>
					</div>						
				</div>
			</div>
			<div class="box box-primary"> <!-- CUSTOMER LIST Category-manage sett-menu-->
				<h2 class="tabsection" id="customer">Customer List</h2>
				<div id="customer_block" style="display:none" >
					<form action="" class="l_form" name="customer" id="customer"  method="post">
						<div id="cust_hist_List" class="custom_report">
							<?php 	
								$i				=0 ; 
								$_GET['start'] 	= 0;
								$type			= 2;
								if(isset($cond) && $cond!= ''){
									$_GET['search']	= $cond;
								}
								if(isset($having) && $having != ''){
									$_GET['having'] = $having;
								}
								require_once("CustomerReportDetails.php");
							?>
						</div>	
					</form>
				</div>
			</div><!-- CUSTOMER LIST END-->
		
			<div class="box box-primary"><!-- LOCATION BREAKDOWN START Category-manage sett-menu-->
				<?php 
				require_once('LocationBreakdownList.php');?>
			</div><!-- LOCATION BREAKDOWN END-->
		</div>
</div><!-- /.row -->
</section><!-- /.content -->
<?php commonFooter(); ?>
<script type="text/javascript">
function loader(){
	$('.loader-merchant').show();	
}
window.onpaint = loader();
$(window).bind('load', function(){
	$("#perfamence_block").hide();
	$('.loader-merchant').hide();
});
</script>
</html>