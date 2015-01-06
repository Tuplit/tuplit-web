<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();
commonHead();
$newWeekArr	=	$newArr = array();
$Start 	= $TotalProducts = $i =  0;
$ProductList =  $resultArray = Array();
if(isset($_GET['Start']) && !empty($_GET['Start']))
	$Start	=	$_GET['Start'];

if(isset($_GET['datetype']) && !empty($_GET['datetype'])) {
	$date_type							=	$_GET['datetype'];
	$_SESSION['TuplitAnalyticsView']	=	$date_type;
} else if(isset($_SESSION['TuplitAnalyticsView']) && !empty($_SESSION['TuplitAnalyticsView']))
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
if(!isset($_SESSION['tuplit_ses_from_timeZone']) || $_SESSION['tuplit_ses_from_timeZone'] == ''){
	 $time_zone = getTimeZone();
	 $_SESSION['tuplit_ses_from_timeZone'] = strval($time_zone);	 
}
$url					=	WEB_SERVICE.'v1/products/analytics/?DataType='.$date_type.'&Start='.$Start."&TimeZone=".$_SESSION['tuplit_ses_from_timeZone'];
// $url					=	WEB_SERVICE.'v1/products/analytics/?DataType=day&Start='.$Start."&TimeZone=".$_SESSION['tuplit_ses_from_timeZone'];

$curlOrderResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
if(isset($curlOrderResponse) && is_array($curlOrderResponse) && $curlOrderResponse['meta']['code'] == 201 && isset($curlOrderResponse['ProductAnalytics']) ) {
	if(isset($curlOrderResponse['ProductAnalytics'])){
		$ProductList 	= $curlOrderResponse['ProductAnalytics'];	
		$TotalProducts 	= $curlOrderResponse['meta']['TotalProducts'];	
	}
} else if(isset($curlOrderResponse['meta']['errorMessage']) && $curlOrderResponse['meta']['errorMessage'] != '') {
		$errorMessage	=	$curlOrderResponse['meta']['errorMessage'];
} else {
		$errorMessage	= 	"Bad Request";
} 
	
foreach($ProductList as $key=>$value){
	$day_arr[$value['ProductID']]['Morning'] = (isset($value['Morning']['Percentage']) && $value['Morning']['Percentage'] != '' ? $value['Morning']['Percentage'] : 0.3) ; 
	$day_arr[$value['ProductID']]['Noon'] = (isset($value['Noon']['Percentage']) && $value['Noon']['Percentage'] != '' ? $value['Noon']['Percentage'] : 0.3) ; 
	$day_arr[$value['ProductID']]['Evening'] = (isset($value['Evening']['Percentage']) && $value['Evening']['Percentage'] != '' ? $value['Evening']['Percentage'] : 0.3) ;
	
}	

?>

<body class="skin-blue fixed body_height">
	<?php  top_header(); ?>
	<section class="content no-top-padding clear">
		<div class="col-lg-12 box-center">	
			<section class=" content-header">
				<div class="col-xs-12 col-sm-6 no-padding">
					<h1 class="">Product Analytics</h1>
				</div>
				<div  class="col-xs-12 col-sm-6 no-padding"></div>
			</section>
			<section class="content no-padding gray_bg top-sale  clear fleft">
				<div class="col-sm-12 no-padding ">
					<?php  ProductAnalyticsTab(); ?>
					<div class="col-xs-12 col-sm-2 col-md-2 col-lg-2 no-padding text-right LH56 ">
						<div class="btn-group">
							<button class="btn btn-default btn-sm dropdown-toggle" value="" type="button" data-toggle="dropdown">
								<span id="dateTypes"><?php if(!empty($date_type) && isset($AnalyticsView[$date_type])) echo $AnalyticsView[$date_type]; else echo "Month"; ?></span><span class="caret"></span>
							</button>
							<ul id="ulrole" role="menu" class="dropdown-menu">
								<li><a id="day" href="#">Today</a></li>
								<li><a id="7days" href="#">7 days</a></li>
								<li><a id="month" href="#">Month</a></li>
								<li><a id="year" href="#">Year</a></li>
							</ul>
						</div>
					</div>
				</div>
			</section>
		<section class="content no-padding clear">
			<div class="box box-primary gray_bg no-padding product_ht">
				<div class="row box-body box-border" style="padding-bottom:0px;">
					<div class="col-xs-12 col-sm-12  col-lg-12  box-center">
						<!--<div class="col-lg-6">							
							<h1 style="color:#202020;">Top Sellers</h1>
						</div>-->
							<div id="productCategory" class="clear" style="color:#000;">
								<div  id="slider" class="products col-xs-12 col-lg-12 col-sm-12">
									<ul class="slidesContainer prod_analy">
										<?php $i=0 ; ?>
										<?php require_once('AjaxProductAnalytics.php'); ?>
									 </ul>
								</div>
								<?php require_once('ProductAnalyticsScript.php');?>
								<div id="category_no_results" style="display:none;">
									<div class="alert alert-danger alert-dismissable col-sm-5 col-xs-11 merchant-margintop"><i class="fa fa-warning"></i> No Result Found</div> 
								</div>
							</div>
							<input type="hidden" id="image_display_count" value="<?php echo count($ProductList);?>">
							<input type="hidden" id="image_total_count" value="<?php echo $TotalProducts;?>"> 
					</div>
				</div>
			</div>
		</section>
				</div>

	</section>
<?php  footerLogin(); ?>
<?php commonFooter(); ?>
<style>
	.morris-hover{position:absolute;z-index:300;}
	.morris-hover.morris-default-style{border-radius:10px;padding:3px;color:#000;background:rgba(255, 255, 255, 0.8);border:solid 2px rgba(230, 230, 230, 0.8);font-family:sans-serif;font-size:12px;text-align:center;}
	.morris-hover.morris-default-style .morris-hover-row-label{font-weight:bold;margin:0.25em 0;}
	.morris-hover.morris-default-style .morris-hover-point{white-space:nowrap;margin:0.1em 0;}
	
</style>
<script src="<?php echo SITE_PATH;?>/webresources/js/jquery.sudoSlider.js" type="text/javascript"></script>
<script type="text/javascript">
$(document).ready(function(){	
	var Total	=	$("#image_total_count").val();
	if(Total == 0){
		$("#category_no_results").show();
	}
	var sudoSlider 	= getProductCategory('','');
	$(window).on("resize focus load", function () {
		var width = $(window).width();
		var orgSlideCount = sudoSlider.getOption("slideCount");
		var slideCount;
		if (width >= 1200) {
			slideCount = 4;
		} else if (width > 900) {
			slideCount = 4;
		} else if (width > 640) {
			slideCount = 3;
		}else if (width > 360) {
			slideCount = 2;
		} else {
			slideCount = 1;
		}
		if (slideCount != orgSlideCount) {
			sudoSlider.setOption("slideCount", slideCount);
			sudoSlider.setOption("moveCount", slideCount);
		}
	}).resize();
	$('#day').click(function() {
		getAjaxProductCategory('day');
		$('#dateTypes').html('Today');
       // return false;
      });
	$('#7days').click(function() {
		getAjaxProductCategory('7days');
		$('#dateTypes').html('7 days');
        // return false;
      });
	$('#month').click(function() {
		getAjaxProductCategory('month');
		$('#dateTypes').html('Month');
        // return false;
      });
	$('#year').click(function() {
		getAjaxProductCategory('year');
		$('#dateTypes').html('Year');
        // return false;
      });
	function getAjaxProductCategory(datetype){
		$('#category_no_results').hide();		
		$.ajax({
			type		: 	"GET",
			url			: 	actionPath+"AjaxProductAnalytics",
			data		: 	'action=GET_MORE_CATEGORY&Start=&datetype='+datetype,
			success		: 	function (result){
								if($.trim(result) != 'fails' && $.trim(result) != ''){
									var totalSlides = sudoSlider.getValue('totalSlides');
									$('#image_display_count').val(8);								
									for(var i=1;i<=totalSlides;i++){
										sudoSlider.removeSlide(i);
									}
									totalSlides = 0;
									var objres 	= jQuery.parseJSON(result);
									var obj		= objres['result'];
									$('#image_total_count').val(objres['total']);
									$.each(obj, function(i, objects) {
										sudoSlider.insertSlide(objects, totalSlides, '');
										totalSlides =  (+totalSlides) + (+1);
									});
									
									sudoSlider.init();
								}
								else {
									var totalSlides = sudoSlider.getValue('totalSlides');
									if(totalSlides > 0){										
										for(var i=1;i<=totalSlides;i++){
											sudoSlider.removeSlide(i);
										}
										totalSlides = 0;
									}
									$('#category_no_results').show();
								}
							},
			beforeSend	: 	function(){
								$('.loader-merchant').show();
							},
			complete	: 	function(){
								$.ajax({
									type	: 	"GET",
									url		: 	actionPath+"ProductAnalyticsScript",
									data	: 	'action=GET_MORE_CATEGORY',
									success	: 	function (result){
													$('<div>'+result+'</div>').insertAfter("#category_no_results");
												}
								});
								setTimeout( function() {
									$('.loader-merchant').hide();
								},100);
							}		
		});
		//return sudoSlider;
	}

});

</script>