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
$total			= 	0;
$cond			= 	'';
$join			= 	'';
if(isset($_GET['cs']) && $_GET['cs'] == 1){
 	unset($_SESSION['loc_mer_name']);
	unset($_SESSION['mer_sess_Category']);
	unset($_SESSION['merchant_sess_city']);
	unset($_SESSION['merchant_sess_price']);
	unset($_SESSION['mer_sess_name']);
	unset($_SESSION['ordertype']);
	unset($_SESSION['sortBy']); 
	unset($_SESSION['loc_mer_price']);
	unset($_SESSION['loc_mer_city']);
	unset($_SESSION['loc_mer_category']);	
		
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
	
commonHead(); 
?>
<?php top_header(); ?>
<body class="skin-blue">
<?php 
$formStatus = 6;
require_once("ReportSearchBox.php");
?>
<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box box-primary demo-graphy" id="chart-container">
				<div class="col-sm-6 col-xs-12">
					<h3>Male/Female Breakdown</h3>
					<div class="" id="pie-chart" style="">
						<?php 
							//$_GET['value']	= 3;
							//$_GET['action']	= "GET_BARCHART_DATA";
							require_once('PieChart.php');
						?>
					</div>
				</div>
				<div class="col-sm-6 col-xs-12">
					<h3>Age Breakdown</h3>
					<div class="" id="bar-chart" style="">	
						<?php 
							//$_GET['option']	= 3;
							//$_GET['action']	= "GET_LINECHART_DATA";
							require_once('DemographBarChart.php');
						?>
					</div>
				</div>	
			</div>	
		</div>		
	</div>
</section>
<?php commonFooter(); ?>