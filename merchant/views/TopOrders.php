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

commonHead();
 ?>
<body class="skin-blue fixed body_height">
	<?php  top_header(); ?>
		<section class="content no-top-padding clear">
		<div class="col-lg-12 box-center">	
			<section class=" content-header">
				<div class="col-xs-12 col-sm-6 no-padding">
					<h1 class="">Product Analytics
					</h1>
				</div>
				<!--<div  class="col-xs-12 col-sm-6 no-padding">
					<span class="fright search-box">
						<input type="text" placeholder="Search" >						
						<div class="btn-group">
							  <button class="btn btn-default btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
								Last 7 days<span class="caret"></span>
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
						</span>
						
				</div>-->
			</section>
			<section class="content no-padding gray_bg top-sale  clear fleft">
				<!-- <div class="row">	 -->
					<div class="col-sm-12 no-padding ">
						<?php  ProductAnalyticsTab(); ?>
						<!-- <div  class="content no-padding top-sale  gray_bg fleft col-lg-12">	-->
							<div class="col-xs-12 col-sm-2 col-md-2 col-lg-2 no-padding text-right LH56 ">
							<!--	<div class="row">
									<div class="col-xs-12 text-right no-padding  ">  -->
										<div class="btn-group">
										  <button class="btn btn-default btn-sm dropdown-toggle" value="" type="button" data-toggle="dropdown">
											<span id="dateTypes">Today</span><span class="caret"></span>
										  </button>
											<ul role="menu" class="dropdown-menu">
												  <li><a id="day" onclick="getTopOrders(this.id,1,1);" href="#">Today</a></li>
												  <li><a id="7days" onclick="getTopOrders(this.id,1,1);" href="#">7 days</a></li>
												  <li><a id="month" onclick="getTopOrders(this.id,1,1);" href="#">Month</a></li>
												  <li><a id="year" onclick="getTopOrders(this.id,1,1);" href="#">Year</a></li>
										   	</ul>
										</div>
								<!--	</div>
								</div> -->
							</div>
						<!-- </div>   -->
					</div>
				<!-- </div>  -->
			</section>
		<section class="content  clear no-padding">
			<div class="box box-primary no-padding">
				<div class="row box-body box-border" style="padding-bottom:0px;">
					<div class="col-xs-12 col-sm-12  col-lg-12  box-center ">
						<!-- <div class="col-lg-12 col-xs-12 no-padding"><hr style="border-color:#dfdfdf;margin:10px 0px 0px;"></div> -->
						<div class="col-lg-12">							
							<h3 style="color:#202020;">Top Sellers</h3>
						</div>
					<div class="main_graph col-xs-12 no-padding"><div class="graph"></div></div>
				</div>
				</div>
			</div>
		</section>
			<?php  footerLogin(); ?>
			<?php commonFooter(); ?>

<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>raphel.min.js" type="text/javascript"></script>
<link href="<?php echo MERCHANT_STYLE_PATH; ?>theme/morris/morris.css" rel="stylesheet" type="text/css" />
<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>morris_custom.js" type="text/javascript"></script>
<script type="text/javascript">
$(document).ready(function(){
		getTopOrders('day',1,1);
	});
</script>