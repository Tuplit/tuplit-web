<?php 
require_once('includes/CommonIncludes.php');
merchant_login_check();

$CustomerPerformance = Array();

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

if(!isset($_SESSION['tuplit_ses_from_timeZone']) || $_SESSION['tuplit_ses_from_timeZone'] == ''){
	 $time_zone = getTimeZone();
	 $_SESSION['tuplit_ses_from_timeZone'] = strval($time_zone);	 
}
$url					=	WEB_SERVICE.'v1/merchants/performance/?TimeZone='.$_SESSION['tuplit_ses_from_timeZone'];
$curlOrderResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
if(isset($curlOrderResponse) && is_array($curlOrderResponse) && $curlOrderResponse['meta']['code'] == 201 && isset($curlOrderResponse['CustomerPerformance']) ) {
	if(isset($curlOrderResponse['CustomerPerformance']))
		$CustomerPerformance 	= $curlOrderResponse['CustomerPerformance'];	
} else if(isset($curlOrderResponse['meta']['errorMessage']) && $curlOrderResponse['meta']['errorMessage'] != '') {
		$errorMessage	=	$curlOrderResponse['meta']['errorMessage'];
} else {
		$errorMessage	= 	"Bad Request";
} 
$PercentageGraphicWeek = $PercentageGraphicMonth = Array();
if(count($CustomerPerformance) > 0) {
	$Purchasesweek	=	$Purchasesmonth	= Array();
	if(isset($CustomerPerformance['PercentageGraphic']['Week']) && !empty($CustomerPerformance['PercentageGraphic']['Week']))
		$PercentageGraphicWeek = $CustomerPerformance['PercentageGraphic']['Week'];

	if(isset($CustomerPerformance['PercentageGraphic']['Month']) && !empty($CustomerPerformance['PercentageGraphic']['Month']))
		$PercentageGraphicMonth = $CustomerPerformance['PercentageGraphic']['Month'];
	
	if(isset($CustomerPerformance['Purchases']['Week']['Current']) && !empty($CustomerPerformance['Purchases']['Week']['Current'])) {
		$Purchasesweekcurrent = $CustomerPerformance['Purchases']['Week']['Current'];
		foreach($Purchasesweekcurrent as $val) {
			$Purchasesweek[$val['DisplayDate']]['OrderDate']	=	$val['DisplayDate'];			
			$Purchasesweek[$val['DisplayDate']]['a']			=	$val['Customers'];
		}
	}
	
	if(isset($CustomerPerformance['Purchases']['Week']['Last']) && !empty($CustomerPerformance['Purchases']['Week']['Last'])) {
		$Purchasesweeklast = $CustomerPerformance['Purchases']['Week']['Last'];
		foreach($Purchasesweeklast as $val) {
			$Purchasesweek[$val['DisplayDate']]['OrderDate']	=	$val['DisplayDate'];			
			$Purchasesweek[$val['DisplayDate']]['b']		=	$val['Customers'];
		}
	}
	
	if(isset($CustomerPerformance['Purchases']['Month']['Current']) && !empty($CustomerPerformance['Purchases']['Month']['Current'])) {
		$PurchasesMonthcurrent = $CustomerPerformance['Purchases']['Month']['Current'];
		foreach($PurchasesMonthcurrent as $val) {
			$Purchasesmonth[$val['DisplayDate']]['OrderDate']	=	$val['DisplayDate'];			
			$Purchasesmonth[$val['DisplayDate']]['a']			=	$val['Customers'];
		}
	}
	
	if(isset($CustomerPerformance['Purchases']['Month']['Last']) && !empty($CustomerPerformance['Purchases']['Month']['Last'])) {
		$PurchasesMonthlast = $CustomerPerformance['Purchases']['Month']['Last'];
		foreach($PurchasesMonthlast as $val) {
			$Purchasesmonth[$val['DisplayDate']]['OrderDate']	=	$val['DisplayDate'];			
			$Purchasesmonth[$val['DisplayDate']]['b']			=	$val['Customers'];
		}
	}	
	if(isset($CustomerPerformance['Listing']['Last24Hours']) && !empty($CustomerPerformance['Listing']['Last24Hours']))
		$Last24Hours 	= $CustomerPerformance['Listing']['Last24Hours'];
	
	if(isset($CustomerPerformance['Listing']['Last7Days']) && !empty($CustomerPerformance['Listing']['Last7Days']))
		$Last7Days 		= $CustomerPerformance['Listing']['Last7Days'];
			
	if(isset($CustomerPerformance['Listing']['Last30Days']) && !empty($CustomerPerformance['Listing']['Last30Days']))
		$Last30Days 	= $CustomerPerformance['Listing']['Last30Days'];
		
	if(isset($CustomerPerformance['Listing']['ThisYear']) && !empty($CustomerPerformance['Listing']['ThisYear']))
		$ThisYear 	= $CustomerPerformance['Listing']['ThisYear'];
	
	if(isset($Purchasesweek) && count($Purchasesweek) > 0) {
		foreach($Purchasesweek as $key=>$val) {				
			if(!isset($Purchasesweek[$key]['a']))
				$Purchasesweek[$key]['a']	=	0;
			if(!isset($Purchasesweek[$key]['b']))
				$Purchasesweek[$key]['b']	=	0;
		}		
		$Purchasesweek = array_values($Purchasesweek);
	}
	if(isset($Purchasesmonth) && count($Purchasesmonth) > 0) {
		foreach($Purchasesmonth as $key=>$val) {				
			if(!isset($Purchasesmonth[$key]['a']))
				$Purchasesmonth[$key]['a']	=	0;
			if(!isset($Purchasesmonth[$key]['b']))
				$Purchasesmonth[$key]['b']	=	0;
		}
		ksort($Purchasesmonth);
		$Purchasesmonth = array_values($Purchasesmonth);
	}
} 
commonHead();
 ?>
<body class="skin-blue fixed body_height">
	<?php  top_header(); ?>
	<section class="content no-top-padding clear">
		<div class="col-lg-12 box-center">	
			<section class=" content-header">
				<div class="col-xs-12 col-sm-6 no-padding">
					<h1 class="">Customer Analytics
					</h1>
				</div>
			</section>
			<section class="content no-padding gray_bg clear top-sale fleft">
				<div class=" col-lg-12  box-center   no-padding ">	
					<div class="col-sm-12 no-padding ">
						<?php  CustomerAnalyticsTab(); ?>						
					</div>					
				</div>
			</section>
			<link href="<?php echo MERCHANT_STYLE_PATH; ?>theme/morris/morris.css" rel="stylesheet" type="text/css" />
			<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>raphel.min.js"></script>
			<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>morris.js" type="text/javascript"></script>
			<section class="content no-padding gray_bg top-sale  clear fleft">
			<div class="main_graph box box-primary no-padding">
					<div class="row box box-primary no-padding" id="chart-container">
						<div class="col-xs-12 col-sm-12  col-lg-12  box-center">
							<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 per_graphic">
								<h3 class="col-lg-6 no-padding" style="color:#202020;">% of Customers ordered</h3>
								<div class="col-lg-6 no-padding text-right" style="margin-top:35px;">
										<a id='bar_week' onclick="get_areachart(this.id,1,'<?php if(isset($PercentageGraphicWeek) && !empty($PercentageGraphicWeek)) echo htmlspecialchars(json_encode($PercentageGraphicWeek)); ?>');" class='area-chart chart-active'>Week</a>
										<a id='bar_month' onclick="get_areachart(this.id,2,'<?php if(isset($PercentageGraphicMonth) && !empty($PercentageGraphicMonth)) echo htmlspecialchars(json_encode($PercentageGraphicMonth)); ?>');" class='area-chart'>Month</a>									
								</div>
							<div  id="area-chart" class="col-lg-12">
								<?php require_once("ChartContent.php");?>
							</div>

							</div>
							<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 pur_graphic">
								<h3 class="col-lg-6 no-padding" style="color:#202020;">Number of Customer ordered</h3>
								<div class="col-lg-6 no-padding text-right" style="margin-top:35px;">										
										<a class="area-chart chart-active" id="line_week" onclick="get_linechart(this.id,1,'<?php if(isset($Purchasesweek) && !empty($Purchasesweek)) echo htmlspecialchars(json_encode($Purchasesweek)); ?>');">Week</a>
										<a class="area-chart" id="line_month" onclick="get_linechart(this.id,2,'<?php if(isset($Purchasesmonth) && !empty($Purchasesmonth)) echo htmlspecialchars(json_encode($Purchasesmonth)); ?>');">Month</a>
								</div>	
							<div  id="line-chart" class="col-lg-12">	
								<?php require_once("LineChart.php");?>
							</div>

							</div>

						</div> 
						<div class="col-xs-12 col-sm-12 bottom-fullwidt"  style="border:none;">
							<?php 
								require_once('BarChart.php');
							?>
						</div> 					
					</div>
			</div>
			</section>
		</div>
	</section>
	<?php  footerLogin(); ?>
	<?php commonFooter(); ?>
		