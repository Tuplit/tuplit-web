<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();

if(isset($_SESSION['TuplitAnalyticsView']) && !empty($_SESSION['TuplitAnalyticsView']))
	$date_type							=	$_SESSION['TuplitAnalyticsView'];
else {
	$date_type							=	'month';
	$_SESSION['TuplitAnalyticsView']	=	'month';
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
$date_flag		= 0;
$month			= date('m');
$year			= date('Y');
$st_date		= date('m/d/Y', mktime(0, 0, 0, $month, 1, $year));
$end_date		= date('m/t/Y', mktime(0, 0, 0, $month, 1, $year));
$dis_st_date	= date('m/d/Y', mktime(0, 0, 0, $month, 1, $year));
$dis_end_date	= date('m/t/Y', mktime(0, 0, 0, $month, 1, $year));

commonHead();
?>
<body class="skin-blue fixed body_height">
	<?php top_header();?>
	<section class="content no-padding  clear ">
		<div class=" col-lg-12  box-center ">	
			<section class=" content-header">
				<div class="col-sm-8 no-padding">
					<h1>Transaction Analytics</h1>
				</div>
				<div  class="col-sm-4 no-padding">
					<!--<span class="fright search-box">
						<input type="text" placeholder="Search" >						
						<div class="btn-group">
							<button class="btn btn-default btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
								Last 7 days <span class="caret"></span>
							</button>
							<ul role="menu" class="dropdown-menu">
								  <li><a href="#">Action</a></li>
								  <li><a href="#">Another action</a></li>
								  <li><a href="#">Something else here</a></li>
								  <li class="divider"></li>
								  <li><a href="#">Separated link</a></li>
							</ul>
						</div>
						<input type="submit" class="searh-btn" value="">
					</span>-->
				</div>
			</section>
			<section class="content no-padding top-sale  gray_bg fleft clear ">
				<div class=" col-lg-12  box-center ">	
					<div class="clear">
						<div class="col-sm-8 no-padding  ">
							<div class="row">
								<div class="col-xs-12 col-sm-10 col-md-10 col-lg-10 product_analytics LH56 no-padding">
									<div class="btn-inline"><a class="col-xs-12 btn btn-default btn-success" title="Overview" href="TransactionOverview?cs=1">Overview</a></div>
									<div class="btn-inline"><a class="col-xs-12 btn btn-default " title="Top sale" href="TopSales?cs=1">Top sale</a></div>
								</div>
							</div>
						</div>	
						<div class="col-sm-4 no-padding ">
							<div class="row">
								<div class="col-xs-12 text-right no-padding  ">
									<div class="btn-group">
									  <button class="btn btn-default btn-sm dropdown-toggle" value="" type="button" data-toggle="dropdown">
										<span id="dateTypes"><?php echo $AnalyticsView[$date_type];?></span><span class="caret"></span>
									  </button>
										<ul role="menu" class="dropdown-menu">
											  <li><a id="day" onclick="getTransactionDetails(this.id,'','',1);" href="javascript:void(0);">Today</a></li>
											  <li><a id="7days" onclick="getTransactionDetails(this.id,'','',1);" href="javascript:void(0);">7 days</a></li>
											  <li><a id="month" onclick="getTransactionDetails(this.id,'','',1);" href="javascript:void(0);">Month</a></li>
											  <li><a id="year" onclick="getTransactionDetails(this.id,'','',1);" href="javascript:void(0);">Year</a></li>
									   </ul>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
			<section class="content no-padding  clear ">
				<div class="box box-primary no-padding ">
					<div class="row box-body box-border" style="padding-bottom:0px;">
						<div class="col-xs-12 col-sm-12  col-lg-12  box-center">
							<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">							
								<h1 style="color:#202020;">Transactions Overview</h1>
							</div>
							<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
							<span class="fright search-box trans-box" style="margin-top:26px;margin-bottom:10px;">
								<input placeholder="Select date" type="text" class="datepicker" id="pickdate" name="pickdate" style="width:200px;" value=""/>
								<!--<input type="button" value="&nbsp;" class="search_icon" title="Search" onclick="return searchTransaction();" style="position:relative;width:30px;">-->
								<span class="caret"></span>
							</span>
							</div>
							<div class="main_graph  col-lg-12 no-right-pad"><div class="graph"></div></div>
						</div>
					</div>
				</div>
				<input type="hidden" id="selectedDate" value=""/>
			</section>
	</section>
			
			<?php  footerLogin(); ?>
	<?php commonFooter(); ?>
<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>raphel.min.js" type="text/javascript"></script>
<link href="<?php echo MERCHANT_STYLE_PATH; ?>theme/morris/morris.css" rel="stylesheet" type="text/css" />
<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>morris_custom.js" type="text/javascript"></script>
<script type="text/javascript">
$(document).ready(function(){
	$("#left_analytics").addClass("menu_active");
	$("#transaction_analytics").addClass("sub_menu_active");
	$('.treeview-menu').show();
	$('#left_analytics').addClass('opened');
	getTransactionDetails('<?php echo $date_type; ?>','','',1);
		// find the input fields and apply the time select to them.
$(".datepicker").datepicker({
	showButtonPanel	:	true,        
	buttonText		:	'<i class="fa fa-calendar"></i>',
	buttonImageOnly	:	true,
	buttonImage		:	path+'webresources/images/calender.png',
	dateFormat		:	'MM d, yy',
	changeMonth		:	true,
	changeYear		:	true,
	hideIfNoPrevNext:	true,
	showWeek		:	true,
	yearRange		:	"c-30:c",
	closeText		:   "Close",
	onSelect		: 	function (dateText) {
							monthNames 	= 	["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
							res 		= 	dateText.split(" ");
							var DateVal =	res[1].substring(0, res[1].length-1);
							Year 		=	res[2];							
							Month		= 	monthNames.indexOf(res[0]) + 1;
							$('#dateTypes').html('Select');							
							SelectDate	=	Year+'-'+Month+'-'+DateVal;
							$('#selectedDate').val(SelectDate);
							getTransactionDetails('day','',SelectDate,2);
						}
});
	});		 
</script>
</html>
