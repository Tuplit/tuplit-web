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
$date_flag		= 0;
$month			= date('m');
$year			= date('Y');
$st_date		= date('m/d/Y', mktime(0, 0, 0, $month, 1, $year));
$end_date		= date('m/t/Y', mktime(0, 0, 0, $month, 1, $year));
$dis_st_date	= date('m/d/Y', mktime(0, 0, 0, $month, 1, $year));
$dis_end_date	= date('m/t/Y', mktime(0, 0, 0, $month, 1, $year));

commonHead();
?>
<body class="skin-blue fixed">
		<?php top_header(); ?>
		<section class="content no-padding">
			<div class="col-lg-10 box-center">	
				<?php  AnalyticsTab(); ?>
			</div>
		</section>
		
		<section class="row content no-padding no-margin">
		<div class="col-lg-10 box-center">	
			<section class="content-header">
                <h1 class="no-top-margin">Transaction Analytics</h1>
            </section>
			<div class="box box-success">
				<div class="product_list">
					<div class="dashboard_filter order_filter">
						<form name="dashboard_list_search" id="dashboard_list_search" action="orders" method="POST" >
							<div class="order_date dashboard_date float">
								<div>
									<ul class="list">
										<li class="sel"><a href="javascript:void(0);" title="Day Time" dashboard_date="timeofday" class="filter_dashboard_date">Day Time</a></li>
										<li><a href="javascript:void(0);" title="Today" dashboard_date="day" class="filter_dashboard_date">Today</a></li>
										<li><a href="javascript:void(0);" title="Last 7 days" dashboard_date="7days" class="filter_dashboard_date">Last 7 days</a></li>
										<li><a href="javascript:void(0);" title="Month" dashboard_date="month" class="filter_dashboard_date">This Month</a></li>
										<li><a href="javascript:void(0);" title="Year" dashboard_date="year" class="filter_dashboard_date">This Year</a></li>
										<input type="hidden" name="filter_dashboard_date" id="filter_dashboard_date" value="timeofday" />
										<input type="hidden" name="date_flag" id="date_flag" value="<?php echo $date_flag; ?>">
										<li class="float">
											<span class="calender">
													<span class="fleft float">From</span>
													<span id="date1"><?php echo $dis_st_date; ?></span>&nbsp;<input type="Hidden" name="st_date" id="st_date" value="<?php echo $st_date; ?>"/>
													<span>&nbsp;to&nbsp;</span>
													<span id="date2"><?php echo $dis_end_date; ?></span>&nbsp;<input type="Hidden" name="end_date" id="end_date" value="<?php echo $end_date; ?>"/>										
												</span>
										</li>
									</ul>
								</div>
							</div>
							<input type="hidden" id="cur_month_start_date" name="cur_month_start_date" value="<?php echo $st_date; ?>" />
							<input type="hidden" id="cur_month_end_date" name="cur_month_end_date" value="<?php echo $end_date; ?>" />
						</form>
					</div>
					<div class="main_graph  col-xs-12"><div class="graph"></div></div>
				</div>
		 	</div>
			
		</section>
	<?php  footerLogin(); ?>
	<?php commonFooter(); ?>
	 <script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
  <link href="<?php echo MERCHANT_STYLE_PATH; ?>theme/morris/morris.css" rel="stylesheet" type="text/css" />
<!-- Morris.js charts -->
    <script src="<?php echo MERCHANT_SCRIPT_PATH; ?>theme/plugins/morris/morris.min.js" type="text/javascript"></script>
	<script>
$(document).ready(function(){
		loadGraph(1,1);
});
$(function() {
		$( "#st_date" ).datepicker({
			dateFormat		:	'mm/dd/yy',
			showOn: "button",
			buttonImage: "../merchant/webresources/images/caleder_icon.png",
			buttonImageOnly: true,
			onSelect: function()
			{
				var date_val = $('#st_date').datepicker().val();
				date_val = dateDisplayFormat(date_val);
				$('#date1').html(date_val);
				$('#date_flag').val(1);
				$('#filter_dashboard_date').val('between');
				$('.filter_dashboard_date').parent().removeClass('sel');
				var graph = 0;
				graph = $('#graphType').val();
				loadGraph(1,1);
			}
		});
		$( "#end_date" ).datepicker({
			dateFormat		:	'mm/dd/yy',
			showOn: "button",
			buttonImage: "../merchant/webresources/images/caleder_icon.png",
			buttonImageOnly: true,
			onSelect: function()
			{
				var date_val = $('#end_date').datepicker().val();
				date_val = dateDisplayFormat(date_val);
				$('#date2').html(date_val);
				$('#date_flag').val(1);
				$('#filter_dashboard_date').val('between');
				$('.filter_dashboard_date').parent().removeClass('sel');
				var graph = 0;
				graph = $('#graphType').val();
				loadGraph(1,1);
			}
		});
	});
	$(".filter_dashboard_date").click(function() {
			$('.filter_dashboard_date').parent().removeClass('sel');
			$(this).parent().addClass('sel');
			var dashboard_date_type = $(this).attr('dashboard_date');
			$('#filter_dashboard_date').val(dashboard_date_type);
			var start_date = $('#cur_month_start_date').val();
			var end_date = $('#cur_month_end_date').val();
			var format_start_date = dateDisplayFormat(start_date);
			var format_end_date = dateDisplayFormat(end_date);
			$('#date1').html(format_start_date);
			$('#date2').html(format_end_date);
			$('#st_date').val(start_date);
			$('#end_date').val(end_date);
			var graph = 0;
			graph = $('#graphType').val();
			loadGraph(1,1);
	});
</script>
</html>
