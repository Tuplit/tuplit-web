<?php
require_once('includes/CommonIncludes.php');
admin_login_check();
require_once('controllers/MerchantController.php');
$merchantObj   	=   new MerchantController();
if(isset($_GET['cs']) && $_GET['cs']=='1') { 
	$_SESSION['tuplit_sess_merchant_id']		=	'';
}
$date_flag		= 0;
$month			= date('m');
$year			= date('Y');
$st_date		= date('m/d/Y', mktime(0, 0, 0, $month, 1, $year));
$end_date		= date('m/t/Y', mktime(0, 0, 0, $month, 1, $year));
$dis_st_date	= date('m/d/Y', mktime(0, 0, 0, $month, 1, $year));
$dis_end_date	= date('m/t/Y', mktime(0, 0, 0, $month, 1, $year));

$field				=	' id,CompanyName';
$condition       	= "  Status =1 order by CompanyName asc";
$merchantList		= 	$merchantObj->selectMerchantDetails($field,$condition);
if(isset($_POST['Search']) && $_POST['Search'] != ''){
	 if(isset($_POST['Merchant']) && $_POST['Merchant'] != ''){
		$_SESSION['tuplit_sess_merchant_id']		=	$_POST['Merchant'];
	}
}
commonHead();
?>
<body class="skin-blue">
	<?php top_header(); ?>
		<!-- Content Header (Page header) -->
	<section class="content-header">
		<div class="row col-xs-10">
			<h1><i class="fa fa-list"></i> Products/Category Analytics </h1>			
		</div>
	</section>
	<!-- Main content -->
	<section class="content">
		<div class="box box-success">
			<div class="product_list">
				<div class="dashboard_filter order_filter">
					<form name="dashboard_list_search" id="dashboard_list_search" action="orders" method="POST" >
						<div class="order_date dashboard_date">
							<div class="nav-tabs-custom">
								 <ul class="nav nav-tabs">
			                      <li id="item_breakdowns"><a href="#" title="Item Breakdown" id="item_breakdown" onclick="change_breakdown(1)" >ITEM BREAKDOWN</a></li>
			                      <li id="category_breakdowns"><a href="#"  title="Category Breakdown" id="category_breakdown" onclick="change_breakdown(2)">CATEGORY BREAKDOWN</a></li>
			                  	</ul>
								<input type="hidden" name="filter_dashboard" id="filter_dashboard" value="1" />
							</div>
							<div>
								<ul class="list">
									<li  class="sel"><a href="javascript:void(0);" title="Today" dashboard_date="day" class="filter_dashboard_date">Today</a></li>
									<li><a href="javascript:void(0);" title="Last 7 days" dashboard_date="7days" class="filter_dashboard_date">Last 7 days</a></li>
									<li><a href="javascript:void(0);" title="Month" dashboard_date="month" class="filter_dashboard_date">This Month</a></li>
									<li><a href="javascript:void(0);" title="Year" dashboard_date="year" class="filter_dashboard_date">This Year</a></li>
									<input type="hidden" name="filter_dashboard_date" id="filter_dashboard_date" value="day" />
									<input type="hidden" name="date_flag" id="date_flag" value="<?php echo $date_flag; ?>">
									<input type="hidden" name="sort_val" id="sort_val" value="">
									<input type="hidden" name="sort_field" id="sort_field" value="">
									<li>
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
				<section class="content">
					<div class="row">
						<div class="col-xs-12">
							<form name="trans_search" id="trans_search" action="ProductAnalytics?cs=1" method="POST" >
								<div class="box box-primary">
									<div class="box-body no-padding" >
										<div class="col-sm-4 form-group">
											<label>Merchant</label>
											<select class="form-control " name="Merchant" id="Merchant" onchange="getProductCategory(this.value);">
												<option value="" >All</option>								
												<?php if(isset($merchantList) && !empty($merchantList)) {
													foreach($merchantList as $m_key=>$m_val) {								
												?>
												<option value="<?php echo $m_val->id;?>" <?php if(isset($_SESSION['tuplit_sess_merchant_id']) && $_SESSION['tuplit_sess_merchant_id'] == $m_val->id) echo "selected"; ?>><?php echo ucfirst($m_val->CompanyName);?></option>
												<?php } } ?>								
											</select>
										</div>
									</div>
									<div class="col-sm-12 box-footer clear" align="center">
										<label>&nbsp;</label>
										<input type="submit" class="btn btn-success" name="Search" id="Search" value="Search" >
									</div>
								</div>
							</form>
						</div>
					</div>
				</section>
				<section class="content box fleft paging-margin">
					<div class="box box-primary">
						<div class="col-xs-12">	
		 					<div class="main_graph"><div class="graph"></div></div>
						</div>
					</div>
				</section>
			</div>
		</div>
	</section>
	<?php commonFooter(); ?>
	<script src="<?php echo ADMIN_SCRIPT_PATH; ?>theme/plugins/morris/raphel.min.js" type="text/javascript"></script>
	<!-- <script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script> -->
	<script>
$(document).ready(function(){
		
		$("#filter_dashboard").val('1');
		$('#item_breakdown').parent().addClass('sel');
		loadGraph(1,2);
});
function change_breakdown(val){

 	$("#filter_dashboard").val(val);
	
	if(val == '2'){
		$("#item_breakdowns").removeClass('sel');
		$('#category_breakdown').parent().addClass('sel');
		loadGraph(1,2);
	}
	else{
		$("#category_breakdowns").removeClass('sel');
		$('#item_breakdown').parent().addClass('sel');
		loadGraph(1,2);
	}

}
$(function() {
		$( "#st_date" ).datepicker({
			dateFormat		:	'mm/dd/yy',
			showOn: "button",
			buttonImage: "../admin/webresources/images/caleder_icon.png",
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
				loadGraph(1,2);
			}
		});
		$( "#end_date" ).datepicker({
			dateFormat		:	'mm/dd/yy',
			showOn: "button",
			buttonImage: "../admin/webresources/images/caleder_icon.png",
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
				loadGraph(1,2);
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
			loadGraph(1,2);
	});
</script>
</html>
