<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();
$start	=	$start_count	=	$tot_rec	=		0;
$searchtext	= '';

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

$show = 1;
if(isset($_GET['viewtype']) && !empty($_GET['viewtype']) && $_GET['viewtype'] == 1)
	$show = 0;
commonHead();
?>
<body class="skin-blue fixed body_height">
	<?php top_header();?>
	<section class="content no-padding  clear ">
		<div class=" col-lg-12  box-center ">	
			<section class=" content-header">
				<div class="col-sm-6 no-padding">
					<h1><?php if($show == 0) echo "Product Analytics"; else echo "Customer Analytics";?></h1>
				</div>
				<div  class="col-sm-6 no-padding">
					<!--<form id="search_form" method="post" action="" onsubmit="return getProductCustomers('<?php echo $date_type;?>','<?php echo $start;?>',1);">
						<span class="fright search-box" style="margin-top:40px;margin-bottom:10px;">
							<input type="text" id="Search" name="Search" placeholder="Search" value="<?php echo $searchtext; ?>">						
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
						</span>
					</form>	-->
				</div>
			</section>
			<section class="content no-padding gray_bg top-sale  clear fleft">
				<div class="col-sm-12 no-padding " >
					<?php 
						if($show == 0)
							ProductAnalyticsTab(); 
						else 
							CustomerAnalyticsTab();						
					?>
					<div class="today_btn col-xs-12 col-sm-2 col-md-2 col-lg-2 no-padding text-right LH56">
						<div class="btn-group">
						  <button class="btn btn-default btn-sm dropdown-toggle" value="" type="button" data-toggle="dropdown">
							<span id="dateTypes"><?php echo $AnalyticsView[$date_type];?></span><span class="caret"></span>
						  </button>
							<ul role="menu" class="dropdown-menu">
								  <li><a id="day" onclick="getProductCustomers(this.id,'<?php echo $start;?>','');" href="#">Today</a></li>
								  <li><a id="7days" onclick="getProductCustomers(this.id,'<?php echo $start;?>','');" href="#">7 days</a></li>
								  <li><a id="month" onclick="getProductCustomers(this.id,'<?php echo $start;?>','');" href="#">Month</a></li>
								  <li><a id="year" onclick="getProductCustomers(this.id,'<?php echo $start;?>','');" href="#">Year</a></li>
							</ul>
						</div>
					</div>
				</div>
			</section>
			<section class="content no-padding  clear ">
				<div class="box box-primary no-padding">
					<div class="row box-body box-border" style="padding-bottom:0px;">
						<div class="col-xs-12 col-sm-12  col-lg-12  box-center no-padding">
							<div style="background-color:#f2f2f2;"></div>  
							<div class="col-xs-12"><div id="append_id"></div></div>
						</div>
					</div>
				</div>
			</section>				
			<section class="content"></section>
			<input type="hidden" name="startcounter" id="startcounter" value="0"/> 
			<input type="hidden" name="totalcounter" id="totalcounter" value=""/>
		</div>
	</section>
	<?php  footerLogin(); ?>
	<?php commonFooter(); ?>
</html>
<script type="text/javascript">
	$(document).ready(function() {
		getProductCustomers('<?php echo $date_type;?>','<?php echo $start;?>','');		
	});
</script>