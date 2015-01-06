<?php 
require_once('includes/CommonIncludes.php');
merchant_login_check();
commonHead();
$Start 	= $TotalProducts = $i =  0;

if(isset($_GET['DataType']) && !empty($_GET['DataType'])) {
	$DataType							=	$_GET['DataType'];
	$_SESSION['TuplitAnalyticsView']	=	$DataType;
	$displayType						=	$AnalyticsView[$_GET['DataType']];
}
else if(isset($_SESSION['TuplitAnalyticsView']) && !empty($_SESSION['TuplitAnalyticsView'])) {
	$DataType							=	$_SESSION['TuplitAnalyticsView'];
	$displayType						=	$AnalyticsView[$_SESSION['TuplitAnalyticsView']];
} else {
	$DataType							=	'month';
	$_SESSION['TuplitAnalyticsView']	=	'month';
	$displayType						=	'Month';
}
$CustomerOverview =  $resultArray = Array();
if(isset($_GET['Start']) && !empty($_GET['Start'])) {
	$Start	=	$_GET['Start'];
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
if(!isset($_SESSION['tuplit_ses_from_timeZone']) || $_SESSION['tuplit_ses_from_timeZone'] == ''){
	 $time_zone = getTimeZone();
	 $_SESSION['tuplit_ses_from_timeZone'] = strval($time_zone);	 
}

$url					=	WEB_SERVICE.'v1/merchants/customeroverview/?DataType='.$DataType.'&Start='.$Start."&TimeZone=".$_SESSION['tuplit_ses_from_timeZone'];
//echo $url;
$curlOrderResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
//echo "<pre>";print_r($curlOrderResponse);echo "</pre>";
if(isset($curlOrderResponse) && is_array($curlOrderResponse) && $curlOrderResponse['meta']['code'] == 201 && isset($curlOrderResponse['CustomerOverview']) ) {
	if(isset($curlOrderResponse['CustomerOverview'])){
		$CustomerOverview 	= $curlOrderResponse['CustomerOverview'];			
	}
	if(isset($curlOrderResponse['meta'])){
		$Overview 		= $curlOrderResponse['meta'];			
	}
} else if(isset($curlOrderResponse['meta']['errorMessage']) && $curlOrderResponse['meta']['errorMessage'] != '') {
		$errorMessage	=	$curlOrderResponse['meta']['errorMessage'];
} else {
		$errorMessage	= 	"Bad Request";
} 

$NewCust_Percentage		=	(isset($CustomerOverview['NewCustomers']['Percentage']) && !empty($CustomerOverview['NewCustomers']['Percentage'])?($CustomerOverview['NewCustomers']['Percentage']):0);
$NewCust_Visits			=	(isset($CustomerOverview['NewCustomers']['Visits']) && !empty($CustomerOverview['NewCustomers']['Visits'])?($CustomerOverview['NewCustomers']['Visits']):0);
$NewCust_Sales			=	(isset($CustomerOverview['NewCustomers']['Sales']) && !empty($CustomerOverview['NewCustomers']['Sales'])?($CustomerOverview['NewCustomers']['Sales']):0);
$NewCust_AvgSales		=	(isset($CustomerOverview['NewCustomers']['AverageSales']) && !empty($CustomerOverview['NewCustomers']['AverageSales'])?($CustomerOverview['NewCustomers']['AverageSales']):0);

$RepeatCust_Percentage	=	(isset($CustomerOverview['RepeatCustomers']['Percentage']) && !empty($CustomerOverview['RepeatCustomers']['Percentage'])?($CustomerOverview['RepeatCustomers']['Percentage']):0);$NewCust_Percentage		=	(isset($CustomerOverview['NewCustomers']['Percentage']) && !empty($CustomerOverview['NewCustomers']['Percentage'])?($CustomerOverview['NewCustomers']['Percentage']):0);
$RepeatCust_Visits		=	(isset($CustomerOverview['RepeatCustomers']['Visits']) && !empty($CustomerOverview['RepeatCustomers']['Visits'])?($CustomerOverview['RepeatCustomers']['Visits']):0);
$RepeatCust_Sales		=	(isset($CustomerOverview['RepeatCustomers']['Sales']) && !empty($CustomerOverview['RepeatCustomers']['Sales'])?($CustomerOverview['RepeatCustomers']['Sales']):0);
$RepeatCust_AvgSales	=	(isset($CustomerOverview['RepeatCustomers']['AverageSales']) && !empty($CustomerOverview['RepeatCustomers']['AverageSales'])?($CustomerOverview['RepeatCustomers']['AverageSales']):0);

$Average_Spent			=	(isset($Overview['AverageSpentPerVisit']) && !empty($Overview['AverageSpentPerVisit'])?($Overview['AverageSpentPerVisit']):0);
$Average_Visits			=	(isset($Overview['AverageVisit']) && !empty($Overview['AverageVisit'])?($Overview['AverageVisit']):0);


$total					=	$RepeatCust_AvgSales + $NewCust_AvgSales;
if($total >0){
	$NewRad					= 	($NewCust_AvgSales/$total)*100;
	$RepRad					=	($RepeatCust_AvgSales/$total)*100;
}
//echo "=======radius=======><br>".$NewRad;
//echo "========radius======><br>".$RepRad;

//if($NewCust_AvgSales > $RepeatCust_AvgSales) { $NewRad	=	60; $RepRad	=	40;} else{ $NewRad	=	40; $RepRad	=	60; }

if(isset($CustomerOverview['NewCustomers']['Summary']) && is_array($CustomerOverview['NewCustomers']['Summary'])){
	foreach($CustomerOverview['NewCustomers']['Summary'] as $key=>$val){
		$Date	= strtotime($val['DisplayDate']);
		$completeArr[$Date]['NewCustomers']['Date']			= $val['DisplayDate'];
		$completeArr[$Date]['NewCustomers']['Visits']		= $val['Visits'];
		$completeArr[$Date]['NewCustomers']['Sales']		= $val['Sales'];
		$completeArr[$Date]['NewCustomers']['AverageSales']	= $val['AverageSales'];
	}
}	
if(isset($CustomerOverview['RepeatCustomers']['Summary']) && is_array($CustomerOverview['RepeatCustomers']['Summary'])){	
	foreach($CustomerOverview['RepeatCustomers']['Summary'] as $key=>$val){
		$Date	= strtotime($val['DisplayDate']);
		$completeArr[$Date]['RepeatCustomers']['Date']			= $val['DisplayDate'];
		$completeArr[$Date]['RepeatCustomers']['Visits']		= $val['Visits'];
		$completeArr[$Date]['RepeatCustomers']['Sales']			= $val['Sales'];
		$completeArr[$Date]['RepeatCustomers']['AverageSales']	= $val['AverageSales'];
	}
}


if(isset($completeArr) && is_array($completeArr)){	
	ksort($completeArr);
}
?>
<style>
	canvas{margin: 10px;}
</style>
<body class="skin-blue fixed body_height">
<?php if(!isset($_GET['action'])){ ?>
<?php  top_header(); ?>
<input type="hidden" id="urlType" value="AjaxCustomerOverview">
	<section class="content no-top-padding clear">
		<div class="col-lg-12 box-center">	
			<section class=" content-header">
				<div class="col-xs-12 col-sm-6 no-padding">
					<h1 class="">Customer Analytics</h1>
				</div>
				<div  class="col-xs-12 col-sm-6 no-padding"></div>
			</section>
			
			<section class="content no-padding gray_bg top-sale  clear fleft">
				<div class="col-sm-12 no-padding ">
					<?php  
						CustomerAnalyticsTab(); 
					?>
					<div class="today_btn col-xs-12 col-sm-2 col-md-2 col-lg-2 no-padding text-right LH56">
						<div class="btn-group">
							<button class="btn btn-default btn-sm dropdown-toggle" value="" type="button" data-toggle="dropdown">
								<span id="dateTypes"><?php echo $displayType;?></span><span class="caret"></span>
							</button>
							<ul role="menu" class="dropdown-menu">
								<li><a id="day" class ="dateType" onclick="getCustOverveiw(this.id)" href="#">Today</a></li>
								<li><a id="7days" class ="dateType" onclick="getCustOverveiw(this.id)" href="#">7 days</a></li>
								<li><a id="month" class ="dateType" onclick="getCustOverveiw(this.id)" href="#">Month</a></li>
								<li><a id="year" class ="dateType" onclick="getCustOverveiw(this.id)" href="#">Year</a></li>
						   	</ul>
						</div>
					</div>
				</div>
			</section>
			<?php } ?>
			<div id="append_div">
				<section class="content clear no-padding">
					<div class="box box-primary no-padding cust_analytics">
						<div class="row box-body box-border" style="padding-bottom:0px;">
							<div class="col-xs-12 col-sm-12  col-lg-12  box-center" >
								<div class="col-xs-12">
									<h3 style="color:#202020;">Repeat Customers and New Customers</h3>
								</div>
								<div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 text-center" style="padding-bottom:20px;">
									<h3 class="overview_head">Visits</h3>
									<?php if($NewCust_Visits == 0  && $RepeatCust_Visits ==0){ ?>
										<div align="center" class="alert alert-danger alert-dismissable col-lg-12 col-sm-12 col-xs-12">
												<i class="fa fa-warning"></i>No result found
										</div>
									<?php }else{ ?>
										<?php  if(isset($NewCust_Visits) && !empty($NewCust_Visits)){ ?>
											<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 no-padding newcust_visit_center <?php if($RepeatCust_Visits == 0 ) echo "visit_center"?>">
												<div class="LH185">
													<div id="myCanvas_1" class="new_canvas"></div>
												</div>
												<span class="newcust_visit"><?php echo $NewCust_Visits; ?> Customers</span>
												<span class="newcust_sales">&pound; <?php echo $NewCust_Sales; ?></span>
											</div>
										<?php } ?>
										<?php  if(isset($RepeatCust_Visits) && !empty($RepeatCust_Visits)){ ?>
											<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 no-padding <?php if($NewCust_Visits == 0 ) echo "visit_center"?>">
												<div class="LH185">
													<div id="myCanvas_2" class="return_canvas"></div>
												</div>
												<span class="newcust_visit"><?php echo $RepeatCust_Visits; ?> Customers</span>
												<span class="newcust_sales">&pound; <?php echo $RepeatCust_Sales; ?></span>
											</div>
										<?php } ?>
									<?php } ?>
								</div>
								<div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 text-center" style="padding-bottom:20px;">
									<h3 class="overview_head">Sale</h3>
									<?php if($Average_Spent == '0'  && $Average_Visits =='0'){ ?>
										<div align="center" class="alert alert-danger alert-dismissable col-lg-12 col-sm-12 col-xs-12">
												<i class="fa fa-warning"></i>No result found
										</div>
									<?php }else{ ?>
										<?php if(isset($Average_Spent) && !empty($Average_Spent)){?>
											<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 no-padding">
												<div class="square_canvas LH185">
													<div id="myCanvas_3" class="per_visit"></div>
												</div>
												<span class="newcust_visit">Per visit</span>
											</div>
										<?php } ?>
										<?php if(isset($Average_Visits) && !empty($Average_Visits)){?>
											<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 no-padding">
												<div class="square_canvas LH185">
													<div id="myCanvas_4" class="per_period"></div>
												</div>
												<span class="newcust_visit">For 30 day period</span>
											</div>
										<?php } ?>
									<?php } ?>
								</div>
								<div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 text-center clear-left" style="padding-bottom:20px;">
									<h3 class="overview_head">Average Sale</h3>
									<?php if($NewCust_AvgSales == 0  && $RepeatCust_AvgSales ==0){ ?>
										<div align="center" class="alert alert-danger alert-dismissable col-lg-12 col-sm-12 col-xs-12">
											<i class="fa fa-warning"></i>No result found
										</div>
									<?php }else { ?>
										<?php if(isset($NewCust_AvgSales) && !empty($NewCust_AvgSales)){?>
										<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 no-padding <?php if($RepeatCust_AvgSales == 0 ) echo "visit_center"?>">
											<div class="LH185">
												<div id="myCanvas_5"></div>
											</div>
											<span class="newcust_visit">New customers</span>
										</div>
										<?php } ?>
										<?php if(isset($RepeatCust_AvgSales) && !empty($RepeatCust_AvgSales)){?>
											<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 no-padding <?php if($NewCust_AvgSales == 0 ) echo "visit_center"?>">
												<div class="LH185">
													<div id="myCanvas_6"></div>
												</div>
												<span class="newcust_visit">Repeat Customers</span>
											</div>
										<?php } ?>
									<?php } ?>
								</div>
							</div>
						</div>
					</div>
				</section>

				<div class="col-xs-12 no-padding commentList">
				   <?php if(isset($completeArr) && is_array($completeArr) && count($completeArr)>0 ){ ?>
						<div class="box">
							<div class="box-body table-responsive no-padding no-margin">
								<table class="table table-hover customer_list cust_analy_overview">
									<tr>
										<th width="10%" style="padding-left:15px">Date</th>
										<th class="text-center">New</th>
										<th class="text-center"><b class="LTstdmedium">Visits</b></th>
										<th class="text-center">Repeat</th>
										<th class="text-center">New</th>
										<th class="text-center"><b class="LTstdmedium">Sales(&pound;)</b></th>
										<th class="text-center">Repeat</th>
										<th class="text-center">New</th>
										<th class="text-center"><b class="LTstdmedium">Average Sales(&pound;)</b></th>
										<th class="text-center">Repeat</th>
									</tr>
									<?php foreach($completeArr as $key=>$value){ ?>
									<tr class="LTstdmedium">	
										<td style="padding-left:15px"><?php echo (!empty($value['RepeatCustomers']['Date'])?$value['RepeatCustomers']['Date']:$value['NewCustomers']['Date']); ?></td>
										<td align="center"><?php if(isset($value['NewCustomers']['Visits']) && !empty($value['NewCustomers']['Visits'])) echo $value['NewCustomers']['Visits']; else echo '0'; ?></td>
										<td align="center">&nbsp;</td>
										<td align="center"><?php if(isset($value['RepeatCustomers']['Visits']) && !empty($value['RepeatCustomers']['Visits'])) echo $value['RepeatCustomers']['Visits']; else echo '0'; ?></td>
										<td align="center"><?php if(isset($value['NewCustomers']['Sales']) && !empty($value['NewCustomers']['Sales'])) echo $value['NewCustomers']['Sales']; else echo '0'; ?></td>
										<td align="center">&nbsp;</td>
										<td align="center"><?php if(isset($value['RepeatCustomers']['Sales']) && !empty($value['RepeatCustomers']['Sales'])) echo $value['RepeatCustomers']['Sales']; else echo '0'; ?></td>
										<td align="center"><?php if(isset($value['NewCustomers']['AverageSales']) && !empty($value['NewCustomers']['AverageSales'])) echo $value['NewCustomers']['AverageSales']; else echo '0'; ?></td>
										<td align="center">&nbsp;</td>
										<td align="center"><?php if(isset($value['RepeatCustomers']['AverageSales']) && !empty($value['RepeatCustomers']['AverageSales'])) echo $value['RepeatCustomers']['AverageSales']; else echo '0'; ?></td>		
									</tr>
									<?php } ?>	
							   </table>
								<?php } else { ?>
									<div class="col-sm-12 no-padding" style="background-color:#fff;min-height:150px;">		
										 <div align="center" class="alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10"><i class="fa fa-warning"></i> <?php echo $errorMessage;?>	</div>							
									</div>									
								<?php } ?>		
							</div>
						</div>					
				</div>
			</div>
	
		
		</div>
	</Section>
<?php if(!isset($_GET['action'])){ ?>
<?php footerLogin(); ?>
<?php commonFooter(); ?>
<?php } ?>
<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>raphel.min.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		<?php  if(isset($NewCust_Visits) && !empty($NewCust_Visits)){ ?>
			raphCircleChart('<?php  echo $NewCust_Percentage.'%';?>','New',<?php  echo $NewCust_Percentage;?>,'#3dc3b8','#59cdc1','myCanvas_1',1); // new customers visits
		<?php } ?>
		<?php  if(isset($RepeatCust_Visits) && !empty($RepeatCust_Visits)){ ?>
			raphCircleChart('<?php  echo $RepeatCust_Percentage.'%';?>','Returning',<?php  echo $RepeatCust_Percentage;?>,'#f79b58','#f9a971','myCanvas_2',1); // repeat customers visits
		<?php } ?>
		<?php if(isset($Average_Spent) && !empty($Average_Spent)){?>
			raphSquare('\u00A3<?php echo $Average_Spent;?>','Average Spent','#3dc3b8','#59cdc1','myCanvas_3'); // sale 
		<?php } ?>
		<?php if(isset($Average_Visits) && !empty($Average_Visits)){?>
			raphSquare('<?php echo $Average_Visits;?>','Average Visits','#f79b58','#f9a971','myCanvas_4'); //
		<?php } ?>
		<?php if(isset($NewCust_AvgSales) && !empty($NewCust_AvgSales)){?>
			raphCircleChart('\u00A3<?php echo $NewCust_AvgSales;?>','<?php echo $NewCust_Visits; ?>',<?php echo $NewRad;?>,'#3dc3b8','#59cdc1','myCanvas_5',2); // avg sale
		<?php } ?>
		<?php if(isset($RepeatCust_AvgSales) && !empty($RepeatCust_AvgSales)){?>
			raphCircleChart('\u00A3<?php echo $RepeatCust_AvgSales;?>','<?php echo $RepeatCust_Visits; ?>',<?php echo $RepRad;?>,'#f79b58','#f9a971','myCanvas_6',2); //
		<?php } ?>
	});
</script>
</body>
