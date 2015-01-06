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
	<?php  top_header(); ?>
	<section class="content no-top-padding clear">
		<div class="col-lg-12 box-center">	
			<section class=" content-header">
				<div class="col-xs-12 col-sm-6 no-padding">
					<h1 class="">Customer Analytics
					</h1>
				</div>
				<div  class="col-xs-12 col-sm-6 no-padding">
					<!--<span class="fright search-box">
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
						</span>-->
						
				</div>
			</section>
			<section class="content no-padding gray_bg clear top-sale fleft">
				<div class=" col-lg-12  box-center   no-padding ">	
					<div class="col-sm-12 no-padding ">
						<?php  CustomerAnalyticsTab(); ?>
						<div class="today_btn col-xs-12 col-sm-2 col-md-2 col-lg-2 no-padding text-right LH56">
						
									<div class="btn-group" style="float:right;">
									  <button class="btn btn-default btn-sm dropdown-toggle" value="" type="button" data-toggle="dropdown">
										<span id="dateTypes"><?php echo $AnalyticsView[$date_type];?></span><span class="caret"></span>
									  </button>
										<ul role="menu" class="dropdown-menu">
											  <li><a id="day" onclick="getTopsales(this.id,2,1);" href="#">Today</a></li>
											  <li><a id="7days" onclick="getTopsales(this.id,2,1);" href="#">7 days</a></li>
											  <li><a id="month" onclick="getTopsales(this.id,2,1);" href="#">Month</a></li>
											  <li><a id="year" onclick="getTopsales(this.id,2,1);" href="#">Year</a></li>
										</ul>
									</div>
							
						</div>
					</div>					
				</div>
			</section>
			<!--<div align="right">
			<form name="dashboard_list_search" id="dashboard_list_search" action="Demographics" method="POST" >
				<div class="col-xs-2 padding" align="right">
					<select class="form-control selectpicker " name="dataType" id="dataType" onchange="getTopsales(this.id,2,1)">	
						<option value="day">Today</option>
						<option value="7days">7 days</option>
						<option value="month">Month</option>
						<option value="year">Year</option>
					</select>
				</div>
			</form>
			</div>-->
			<div class="main_graph  col-xs-12"><div class="graph"></div></div>
			</div>
		</div>
	</section>
			<?php  footerLogin(); ?>
			<?php commonFooter(); ?>

<script type="text/javascript">
$(document).ready(function(){
		getTopsales('<?php echo $date_type;?>',2,1);
	});
</script>