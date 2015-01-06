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
										<div class="col-xs-12 col-sm-10 col-md-10 col-lg-10 product_analytics LH56 no-padding  ">
											<div class="btn-inline "><a class="col-xs-12 btn btn-default" title="" href="TransactionOverview?cs=1">Overview</a></div>
											<div class="btn-inline "><a class="col-xs-12 btn btn-default btn-success" title="Top sale" href="TopSales?cs=1">Top sale</a></div>
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
													  <li><a id="day" onclick="getTopsales(this.id,1,1);" href="#">Today</a></li>
													  <li><a id="7days" onclick="getTopsales(this.id,1,1);" href="#">7 days</a></li>
													  <li><a id="month" onclick="getTopsales(this.id,1,1);" href="#">Month</a></li>
													  <li><a id="year" onclick="getTopsales(this.id,1,1);" href="#">Year</a></li>
											   </ul>
											</div>
											<!-- <form name="dashboard_list_search" id="dashboard_list_search" action="TopSales" method="POST" >
												<div class="col-xs-5   no-padding fright" align="right">
													<select class="form-control selectpicker " name="dataType" id="dataType" onchange="getTopsales(this.id,1,1)">	
														<option value="day">Today</option>
														<option value="7days">7 days</option>
														<option value="month">Month</option>
														<option value="year">Year</option>
													</select>
												</div>
											</form> -->
										</div>
									</div>
								</div>
							</div>
						</div>
					</section>
					<section class="content no-padding  clear ">
						<div class="box box-primary no-padding">
							<div class="row box-body box-border" style="padding-bottom:0px;">
								<div class="col-xs-12 col-sm-12  col-lg-12  box-center no-padding">
									<!-- <div class="col-lg-12 col-xs-12 no-padding"><hr style="border-color:#dfdfdf;margin:10px 0px 0px;"></div> -->
									<div style="background-color:#f2f2f2;">
										
										<?php //if(isset($xarrays) &&  !empty($xarrays)) {?>
										<?php //} ?>
									</div>  
									<div class="main_graph  col-xs-12"><div class="graph"></div></div>
								</div>
							</div>
					</section>
				</div>
			</section>
			<section class="content">
				
			</section>
			<?php  footerLogin(); ?>
	<?php commonFooter(); ?>
<script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
<link href="<?php echo MERCHANT_STYLE_PATH; ?>theme/morris/morris.css" rel="stylesheet" type="text/css" />
<!-- Morris.js charts -->
<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>morris.js" type="text/javascript"></script>
<script type="text/javascript">
$(document).ready(function(){
	$("#left_analytics").addClass("menu_active");
	$("#transaction_analytics").addClass("sub_menu_active");
	$('.treeview-menu').show();
	$('#left_analytics').addClass('opened');
	getTopsales('<?php echo $date_type;?>',1,1);
});
</script>