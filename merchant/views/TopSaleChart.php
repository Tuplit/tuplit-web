<?php
require_once('includes/CommonIncludes.php');
$headTabs = array();
$grossTotal = $discounts	=$subTotal = $tax	= $total = 0;
if(isset($_POST['action']) && $_POST['action']=='GET_CHART') { 
	$timeDay		=	1;
	if(isset($_POST['dataType']) && $_POST['dataType'] != '') {
		$date_type							=	$_POST['dataType'];
		$_SESSION['TuplitAnalyticsView']	=	$date_type;
	}
	if(isset($_POST['timeOfDay']) && $_POST['timeOfDay'] != ''){
		$timeDay			=	$_POST['timeOfDay'];
	}
	if(!isset($_SESSION['tuplit_ses_from_timeZone']) || $_SESSION['tuplit_ses_from_timeZone'] == ''){
		 $time_zone = getTimeZone();
		 $_SESSION['tuplit_ses_from_timeZone'] = strval($time_zone);	 
	}
	$url					=	WEB_SERVICE.'v1/merchants/topsales/?DataType='.$date_type.'&TimeZone='.$_SESSION['tuplit_ses_from_timeZone'].'';
	$curlTopSaleResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlTopSaleResponse) && is_array($curlTopSaleResponse) && $curlTopSaleResponse['meta']['code'] == 201 && is_array($curlTopSaleResponse['TopSales']) ) {
		if(isset($curlTopSaleResponse['TopSales'])){
			$topSales = $curlTopSaleResponse['TopSales'];	
		}
		if(isset($curlTopSaleResponse['meta'])){
			$headTabs = $curlTopSaleResponse['meta'];	
		}
	} else if(isset($curlTopSaleResponse['meta']['errorMessage']) && $curlTopSaleResponse['meta']['errorMessage'] != '') {
			$errorMessage	=	$curlTopSaleResponse['meta']['errorMessage'];
	} else {
			$errorMessage	= 	"Bad Request";
	} 
	if(isset($topSales) && !empty($topSales)){
		if($date_type=='month') {
			if(isset($topSales) && is_array($topSales) && count($topSales)>0) {
				$orderStringArray = getStringForDay($topSales);
			}
			if(isset($orderStringArray) && $orderStringArray!='') {
				list($x_labels_string,$value_order_string) = explode('###',$orderStringArray);
				$all_series['order'] = $value_order_string;
			}
		} else if($date_type=='year') {
			$x_labels_string = '';
			if(isset($topSales) && is_array($topSales) && count($topSales)>0) {
				$all_series['order'] = getStringForMonth($topSales);
			}
		} else if($date_type=='day') {
			if(isset($topSales) && is_array($topSales) && count($topSales)>0) {
				$orderStringArray = getStringForDayTime($topSales,$timeDay);
			}
			if(isset($orderStringArray) && $orderStringArray!='') {
				list($x_labels_string,$value_order_string) = explode('###',$orderStringArray);
				$all_series['order'] = $value_order_string;
			}
		}else if($date_type=='7days') {
			if(isset($topSales) && is_array($topSales) && count($topSales)>0) {
				$orderStringArray = getStringForDay($topSales,'','',1);
			}
			if(isset($orderStringArray) && $orderStringArray!='') {
				list($x_labels_string,$value_order_string) = explode('###',$orderStringArray);
				$all_series['order'] = $value_order_string;
			}
		}
	}
	if(isset($x_labels_string) && $x_labels_string!='') { 
		$xarrays = explode(',',$x_labels_string);
	}
	else{
		$default_label = 'Jan,Feb,Mar,Apr,May,Jun,Jul,Aug,Sep,Oct,Nov,Dec';
		$xarrays = explode(',',$default_label);
	}
	$value_arrays = array();
	if(isset($all_series['order']) && $all_series['order'] != ''){
		$value_arrays = explode(',',$all_series['order']);
	}
	if(!empty($headTabs)){
		$grossTotal	=	$headTabs['GrossTotal'];
		$discounts	=	$headTabs['Discounts'];
		$subTotal	=	$headTabs['SubTotal'];
		$tax		=	$headTabs['Tax'];
		$total		=	$subTotal+$tax;
	}
	//echo "<pre>"; print_r($curlTransactionResponse); echo "</pre>";
	//echo "<pre>"; print_r($orderStringArray); echo "</pre>";
?>

<?php if(!empty($topSales)){ ?>
<div class="sales-summary col-sm-12">
	<div class="row">
		<div class="col-lg-12 no-padding">							
			<h1 style="color:#202020;">Top Sales Summary</h1>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-6 col-sm-2 col-md-1 col-lg-1 LH18 no-padding" align="left">
			<b>Gross sales</b>
		</div>
		<div class="col-xs-9 col-sm-9 col-md-10 col-lg-10 LH18 no-right-pad dashed-border" align="left"></div>
		<div class="col-xs-6 col-sm-1 col-md-1 col-lg-1 LH18 no-left-pad" align="right">
			<b class="top_sales_total">&pound;<?php echo number_format((float)$grossTotal,2,'.',',');?></b>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-6 col-sm-2 col-md-1 col-lg-1 LH18 no-padding sel" align="left">
			<b>Discounts</b>
		</div>
		<div class="col-xs-9 col-sm-9 col-md-10 col-lg-10 LH18 no-right-pad dashed-border" align="left"></div>
		<div class="col-xs-6 col-sm-1 col-md-1 col-lg-1 LH18 no-left-pad sel" align="right">
			<b class="top_sales_total">&ndash;&pound;<?php  echo number_format((float)$discounts,2,'.',','); ?></b>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-6 col-sm-2 col-md-1 col-lg-1 LH18 no-padding" align="left">
			<b>Subtotal</b>
		</div>
		<div class="col-xs-9 col-sm-9 col-md-10 col-lg-10 LH18 no-right-pad dashed-border" align="left"></div>
		<div class="col-xs-6 col-sm-1 col-md-1 col-lg-1 LH18 no-left-pad" align="right">
			<b class="top_sales_total">&pound;<?php  echo number_format((float)$subTotal,2,'.',',');?></b>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-6 col-sm-2 col-md-1 col-lg-1 LH18 no-padding" align="left">
			<b>Tax</b>
		</div>
		<div class="col-xs-9 col-sm-9 col-md-10 col-lg-10 LH18 no-right-pad dashed-border" align="left"></div>
		<div class="col-xs-6 col-sm-1 col-md-1 col-lg-1 LH18 no-left-pad" align="right">
			<b class="top_sales_total">&pound;<?php echo number_format((float)$tax,2,'.',',');?></b>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-11 LH18 no-right-pad  gray_bg" align="right">
			<span>TOTAL</span>
			<span>
				&pound;<?php echo number_format((float)$total,2,'.',',');?>
			</span>
		</div>
		<div class="col-lg-1 LH18 no-right-pad" align="left">
			
		</div>
	</div>
</div>
<div class="col-lg-12">
	<div class="row">
		<div class="col-lg-12">							
			<h1 style="color:#202020;margin-top:25px;">Top Sales Detail</h1>
		</div>
	</div>
</div>
<?php if($date_type=='day') {?>
<div class="col-lg-4 session" align="right">
	<ul>
		<li>
			<a <?php if($timeDay == 1) {?> class="sel" <?php } ?> onclick="getTopsales('day',1,1);">MORNING<?php if($timeDay == 1) {?><span class="caret"></span><?php } ?></a>
		</li>
		<li>
			<a <?php if($timeDay == 2) {?> class="sel" <?php } ?> onclick="getTopsales('day',1,2);">NOON<?php if($timeDay == 2) {?><span class="caret"></span><?php } ?></a>
		</li>
		<li>
			<a <?php if($timeDay == 3) {?> class="sel" <?php } ?> onclick="getTopsales('day',1,3);">EVENING<?php if($timeDay == 3) {?><span class="caret"></span><?php } ?></a>
		</li>
	</ul>
</div>
<?php }?>
<?php if(isset($xarrays) && is_array($xarrays) && count($xarrays)>0) {?>
<div class="col-xs-12 col-sm-12">
	    <div class="chart" id="bar-chart1" style="height: 300px;"></div>
</div> 
<?php } else {?>
 <div class="row clear">		
		 <div align="center" class="alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10">
		 	<i class="fa fa-fw fa-warning"></i>No results found</div>							
	</div>	
<?php } ?>
<style>
	.morris-hover{position:absolute;z-index:1000;}
	.morris-hover.morris-default-style{border-radius:10px;padding:6px;color:#666;background:rgba(255, 255, 255, 0.8);border:solid 2px rgba(230, 230, 230, 0.8);font-family:sans-serif;font-size:12px;text-align:center;}
	.morris-hover.morris-default-style .morris-hover-row-label{font-weight:bold;margin:0.25em 0;}
	.morris-hover.morris-default-style .morris-hover-point{white-space:nowrap;margin:0.1em 0;}
	</style>
	<script type="text/javascript">
	<?php if(isset($xarrays) && is_array($xarrays) && count($xarrays)>0) {?>
	var chart = Morris.Line({
		  element			: 	'bar-chart1',
		  data				: 	[
									<?php  if(isset($xarrays) && is_array($xarrays)) {
											foreach($xarrays as $key=>$val){ if(isset($value_arrays[$key]) && $value_arrays[$key] != ''){
												?>
												
										{y: '<?php echo $val?>',a: <?php echo $value_arrays[$key];?>},
										<?php } }
									}?>
								],
		  xkey				: 	'y',
		  ykeys				: 	['a'],
		  labels			: 	['&pound;'],
		  grid				: 	false,
		  hideHover 		: 	true,
		  parseTime			: 	false,
		  smooth			:	false,
		  lineColors		:	["#01a99a"],
		  lineWidth			: 	2,
		  resize			:	true,
		  pointSize			:	4,
		  pointStrokeColors	:	["#fc7f09"],
		  hoverCallback		: 	function(index, options, content,row) {
									//return row.a+'%<br>'+row.y;
									return row.y+'<br>&pound;'+row.a;
								},
		});
		<?php } ?>
	</script>
 <?php }else {?>
 <div class="row clear">		
		 <div align="center" class="alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10">
		 	<i class="fa fa-fw fa-warning"></i>No results found</div>							
	</div>	
<?php } ?>
 <?php } ?>