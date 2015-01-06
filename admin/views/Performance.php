<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
require_once('controllers/AdminController.php');
$msg = '';
require_once('controllers/MerchantController.php');
$merchantObj   	=   new MerchantController();
require_once('controllers/ManagementController.php');
$managementObj  =   new ManagementController();
require_once('controllers/OrderController.php');
$orderObj   	=   new OrderController();
$total			=  	0;
if(isset($_GET['cs']) && $_GET['cs'] == 1){
 	//unset($_SESSION['loc_mer_name']);
	//unset($_SESSION['mer_sess_Category']);
	//unset($_SESSION['merchant_sess_city']);
	//unset($_SESSION['merchant_sess_price']);
	//unset($_SESSION['mer_sess_name']);
	unset($_SESSION['ordertype']);
	unset($_SESSION['sortBy']); 
	//unset($_SESSION['loc_mer_price']);
	//unset($_SESSION['loc_mer_city']);
	//unset($_SESSION['loc_mer_category']);	
	
}
/*----Merchant search----------------------*/
$condition       	= "  Status =1 order by CompanyName asc";
$field				=	' id,CompanyName';
$MerchantList		= 	$merchantObj->selectMerchantDetails($field,$condition);

/*Category search--------------------------*/
$condition       	= 1;
$field				='id,CategoryName';
$CategoryList  = $managementObj->selectCategoryDetails($field,$condition);

/*City search-----------------------------*/
$condition       	= " City<>''  group by City order by City asc";
$field				=	' City';
$CityList		= 	$merchantObj->selectMerchantDetails($field,$condition);

if(isset($_POST) && !empty($_POST)){
	if(isset($_POST['Merchant_Name']) && trim($_POST['Merchant_Name'])!='')
		$_SESSION['loc_mer_name'] = $_POST['Merchant_Name'];
	else
		unset($_SESSION['loc_mer_name']);
		
	if(isset($_POST['Merchant_Category']) && trim($_POST['Merchant_Category'])!='')
		$_SESSION['loc_mer_category'] = $_POST['Merchant_Category'];
	else
		unset($_SESSION['loc_mer_category']);
		
	if(isset($_POST['Merchant_city']) && trim($_POST['Merchant_city'])!='')
		$_SESSION['loc_mer_city'] = $_POST['Merchant_city'];
	else
		unset($_SESSION['loc_mer_city']);
		
	if(isset($_POST['Merchant_Price']) && trim($_POST['Merchant_Price'])!='')
		$_SESSION['loc_mer_price'] = $_POST['Merchant_Price'];
	else
		unset($_SESSION['loc_mer_price']);
}
	/*
	if($cond==''){
		$cond .= " and (DATE_FORMAT(o.OrderDate,'%Y-%m-%d')) BETWEEN DATE_SUB( CURDATE( ) ,INTERVAL 7 DAY ) AND CURDATE( ) ".$groupby;
		//$cond1.= " and DATE_FORMAT(o.OrderDate,'%Y-%m-%d') BETWEEN DATE_SUB( CURDATE( ) , INTERVAL 1 MONTH )  AND CURDATE( ) ".$groupby1;
	}
*/
	
commonHead(); 
?>

<?php top_header(); ?>
<style>
.chart_active{
	border:1px solid #000;
	background:#fff;
	color:#01a99a;
}
	
</style>
<body class="skin-blue">
<?php 
$formStatus = 5;
require_once("ReportSearchBox.php");
?>
<section class="content">
	<div class="row">
		<div class="col-xs-12">
			<div class="box box-primary perfomance-list" id="chart-container">
				<div  class="col-xs-12 col-sm-6  no-padding dia-border">
					<div class="top-header">
						<h3>Percentage graphic</h3>
						<div class="month-week">
							<a id="bar_week" onclick="get_areachart(this.id,option='1');" class="area-chart chart-active">Week</a>
							<a id="bar_month" onclick="get_areachart(this.id,option='2');" class="area-chart">Month</a>
						</div>
					</div>
					<div id="bar-chart">
						<?php 
							$_GET['value']	= 3;
							$_GET['action']	= "GET_BARCHART_DATA";
							require_once('ChartContent.php');
						?>
					</div>
				</div>
				
				<div  class="col-xs-12 col-sm-6  no-padding  dia-border">
					<div class="top-header">
						<h3>Purchase</h3>
						<div class="month-week">
							<a class="line-chart chart-active" id="line_week" onclick="get_linechart(this.id,option='1');">Week</a>
							<a class="line-chart" id= "line_month" onclick="get_linechart(this.id,option='2');">Month</a>
						</div>
					</div>
					<div  id="line-chart">	
						<?php 
							$_GET['option']	= 3;
							$_GET['action']	= "GET_LINECHART_DATA";
							require_once('LineChart.php');
						?>
					</div>
				</div>
				
				<div class="col-xs-12 col-sm-12 bottom-fullwidt" id="bar-chart">
					<?php 
							require_once('BarChart.php');
					?>
				</div>
			</div>	
		</div>			
	</div>
</section>
<?php commonFooter(); ?>
