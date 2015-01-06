<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();
$start	=	$start_count	=	$tot_rec	=	0;
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

if(isset($_GET['cs']) && $_GET['cs'] == 1){
	unset($_SESSION['tuplit_sess_comment_start']);
}

if(isset($_POST['start']) && isset($_POST['Previous']) && isset($_POST['totalcount'])) {
	$start 			= 	$_POST['start'] - 10;	
	$start_count	=	$start;
} else if(isset($_POST['start']) && isset($_POST['Next']) && isset($_POST['totalcount'])) {
	$start = $_POST['start'] + 10;
	$start_count	=	$start;
}
$_SESSION['tuplit_sess_comment_start'] = $start;
commonHead();
?>
<body class="skin-blue fixed body_height">
	<?php  top_header(); ?>
	<section class="content no-top-padding clear">
		<div class="col-lg-12 box-center">	
			<section class=" content-header">
				<div class="col-xs-12 col-sm-6 no-padding">
					<h1 class="">
					<?php 
					echo "Customer Analytics"; 
					/*if(isset($_GET['analytics']) && $_GET['analytics'] == 'customer'){
							echo "Customer Analytics"; 
						}else{ 
							echo "Product Analytics";
						}*/?>
					</h1>
				</div>
				<div  class="col-xs-12 col-sm-6 no-padding">
					<!--<span class="fright search-box" style="margin-top:23px;">
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

			<section class="content no-padding gray_bg top-sale  clear fleft">
				<div class="col-sm-12 no-padding ">
					<?php  
						CustomerAnalyticsTab(); 
						/*if(isset($_GET['analytics']) && $_GET['analytics'] == 'customer'){
							CustomerAnalyticsTab(); 
						}else{ 
							ProductAnalyticsTab();
						}*/
					?>
					<div class="today_btn col-xs-12 col-sm-2 col-md-2 col-lg-2 no-padding text-right LH56 ">
						<div class="btn-group">
						  <button class="btn btn-default btn-sm dropdown-toggle" value="" type="button" data-toggle="dropdown">
							<span id="dateTypes"><?php echo $AnalyticsView[$date_type];?></span><span class="caret"></span>
						  </button>
							<ul role="menu" class="dropdown-menu">
								  <!--<li><a id="day" onclick="getProdComments(this.id,'<?php echo $start;?>');" href="#">Today</a></li>
								  <li><a id="7days" onclick="getProdComments(this.id,'<?php echo $start;?>');" href="#">7 days</a></li>
								  <li><a id="month" onclick="getProdComments(this.id,'<?php echo $start;?>');" href="#">Month</a></li>
								  <li><a id="year" onclick="getProdComments(this.id,'<?php echo $start;?>');" href="#">Year</a></li>-->
								  <li><a id="day" onclick="getProdComments(this.id,'0');" href="#">Today</a></li>
								  <li><a id="7days" onclick="getProdComments(this.id,'0');" href="#">7 days</a></li>
								  <li><a id="month" onclick="getProdComments(this.id,'0');" href="#">Month</a></li>
								  <li><a id="year" onclick="getProdComments(this.id,'0');" href="#">Year</a></li>
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
							<div class="col-xs-12"><div class="clear" id="comment_append"></div></div>
						
						</div>
					</div>
			</section>
	</div>		
	</section>
	<?php footerLogin(); ?>
	<?php commonFooter(); ?>
<script type="text/javascript">
$(document).ready(function(){
	getProdComments('<?php echo $date_type;?>','<?php echo $start;?>');
});

</script>