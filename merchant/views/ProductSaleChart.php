<?php
require_once('includes/CommonIncludes.php');
$headTabs = array();
$grossTotal = $discounts	=$subTotal = $tax	= $total = 0;
if(isset($_POST['action']) && $_POST['action']=='GET_CHART') { 
	$timeDay		=	1;
	if(isset($_POST['dataType']) && $_POST['dataType'] != '')
		$date_type			=	$_POST['dataType'];
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
	$url					=	WEB_SERVICE.'v1/merchants/topProducts/?DataType='.$date_type.'';
	$curlProductResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlProductResponse) && is_array($curlProductResponse) && $curlProductResponse['meta']['code'] == 201 && is_array($curlProductResponse['TopProducts']) ) {
		if(isset($curlProductResponse['TopProducts']['result'])){
			$product_array	= 	$curlProductResponse['TopProducts']['result'];	
		}
	} else if(isset($curlProductResponse['meta']['errorMessage']) && $curlProductResponse['meta']['errorMessage'] != '') {
			$errorMessage	=	$curlProductResponse['meta']['errorMessage'];
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
	$url					=	WEB_SERVICE.'v1/merchants/productcustomerlist/?DataType='.$date_type.'&Type=1';
	$curlCustomerResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlCustomerResponse) && is_array($curlCustomerResponse) && $curlCustomerResponse['meta']['code'] == 201 && is_array($curlCustomerResponse['CustomerList']) ) {
		if(isset($curlCustomerResponse['CustomerList'])){
			$customerList = $curlCustomerResponse['CustomerList'];	
			$tot_rec	  = $curlCustomerResponse['meta']['totalCount'];
		}
	} else if(isset($curlCustomerResponse['meta']['errorMessage']) && $curlCustomerResponse['meta']['errorMessage'] != '') {
			$errorMessage	=	$curlCustomerResponse['meta']['errorMessage'];
	} else {
			$errorMessage	= 	"Bad Request";
	}
	/*if($_SERVER['REMOTE_ADDR'] == '172.21.4.113'){
		echo "<br>=================>";
		echo "<pre>";print_r($headTabs);echo "</pre>";
	}*/
	
?>
<div class="col-xs-12 no-padding" style="margin-top:30px;">
<div class="sales-summary col-sm-6" >
	<div class="row no-margin">
		<div class="col-lg-6 no-padding">							
			<h3 class="no-margin" style="color:#202020;">Top Sales Summary</h3>
		</div>
	</div>
	<?php if(!empty($headTabs)){?>
	<div class="row">
		<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 LH18 no-right-pad" align="left">
			<b>Gross sales</b>
		</div>
		<!-- <div class="col-lg-9 LH18 no-right-pad" align="left"> </div>--> <!-- dashed-border -->
		<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 LH18 no-left-pad" align="right">
			<b>&pound;<?php echo number_format((float)$grossTotal,2,'.',',');?></b>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 LH18 no-right-pad sel" align="left">
			<b>Discounts</b>
		</div>
		<!-- <div class="col-lg-9 LH18 no-right-pad" align="left"></div> --><!-- dashed-border -->
			
		
		<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 LH18 no-left-pad sel" align="right">
			<b>&ndash;&pound;<?php  echo number_format((float)$discounts,2,'.',','); ?></b>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 LH18 no-right-pad" align="left">
			<b>Subtotal</b>
		</div>
		<!-- <div class="col-lg-9 LH18 no-right-pad" align="left"></div> --><!-- dashed-border -->
		
		<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 LH18 no-left-pad" align="right">
			<b>&pound;<?php  echo number_format((float)$subTotal,2,'.',',');?></b>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 LH18 no-right-pad" align="left">
			<b>Tax</b>
		</div>
		<!-- <div class="col-lg-9 LH18 no-right-pad" align="left"></div> --><!-- dashed-border -->
		
		<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 LH18 no-left-pad" align="right">
			<b>&pound;<?php echo number_format((float)$tax,2,'.',',');?></b>
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
	<?php } else { ?>
	<div class="col-xs-12 col-sm-12">
		<div align="center" class="alert alert-danger alert-dismissable col-lg-4 col-sm-8 col-xs-10">
		<i class="fa fa-fw fa-warning"></i> No results found</div>		
	</div>
	<?php } ?>
</div>
<div class="sales_border_left">&nbsp;</div>
<div class="sales-summary col-sm-6">
	<div class="row no-margin">
		<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 no-padding">							
			<h3 class="no-margin" style="color:#202020;">Top Sales Detail</h3>
		</div>
	</div>
	<?php if(!empty($topSales)){
		 if($date_type=='day') {?>
	<div class="col-lg-12 session" align="right">
		<ul>
			<li>
				<a <?php if($timeDay == 1) {?> class="sel" <?php } ?> onclick="getAnalytics('day',1,1);">MORNING<?php if($timeDay == 1) {?><span class="caret"></span><?php } ?></a>
			</li>
			<li>
				<a <?php if($timeDay == 2) {?> class="sel" <?php } ?> onclick="getAnalytics('day',1,2);">NOON<?php if($timeDay == 2) {?><span class="caret"></span><?php } ?></a>
			</li>
			<li>
				<a <?php if($timeDay == 3) {?> class="sel" <?php } ?> onclick="getAnalytics('day',1,3);">EVENING<?php if($timeDay == 3) {?><span class="caret"></span><?php } ?></a>
			</li>
		</ul>
	</div>
	<?php }?>
	<?php if($date_type=='day') {?>
	<div class="col-xs-12 col-sm-12">
		    <div class="chart" id="bar-chart1" style="height: 235px;"></div>
	</div>
	<?php } else {?> 
	<div class="col-xs-12 col-sm-12">
		    <div class="chart" id="bar-chart1" style="height: 235px;"></div>
	</div>
	<?php } ?>
	<?php } else {?>
	<div class="col-xs-12 col-sm-12">
		<div align="center" class="alert alert-danger alert-dismissable col-lg-4 col-sm-8 col-xs-10">
		<i class="fa fa-fw fa-warning"></i> No results found</div>		
	</div>
	<?php } ?>
</div>
</div>
<!-- <div class="clear"></div> -->
<?php if(isset($product_array) && is_array($product_array) && count($product_array)>0) {
		$height = count($product_array) * 60;
	  } else
		$height = '300';
		?>
<div class="col-xs-12 no-padding" style="margin-top:30px;">
<div class="sales-summary col-sm-6" style="height:<?php echo $height.'px'?>;align:left;" >
	<div class="row no-margin">
		<div class="col-lg-6 no-padding">							
			<h3 class="no-margin" style="color:#202020;">Top Sellers</h3>
		</div>
	</div>
	<?php if(isset($product_array) && is_array($product_array) && count($product_array)>0) {?>
		<?php  $ht = (((count($product_array))>4)?((count($product_array))*50):((count($product_array))*80));?>
			<div id="bar-canvas" style="height:<?php  echo  $ht; ?>px;"></div>
		<?php }else{?>
			<div class="col-xs-12 col-sm-12">
				<div align="center" class="alert alert-danger alert-dismissable col-lg-4 col-sm-8 col-xs-10">
				<i class="fa fa-fw fa-warning"></i> No results found</div>		
			</div>
	<?php } ?>
</div>
<div class="sales_border_left">&nbsp;</div>
<div class="sales-summary col-sm-6"  ><!-- height:300px;align:left; -->
	<div class="row no-margin">
		<div class="col-lg-6 no-padding">							
			<h3 class="no-margin" style="color:#202020;">Top Customers</h3>
		</div>
	</div>
	<div class="box-body table-responsive no-padding no-margin" style="border:0px solid #fff;">
		<table class="table table-hover product_sale_chart" width="50%">

	<?php if(!empty($customerList)){
		$i = 1;
		foreach($customerList as $key=>$value){
			$imagepath 	=	$value['Photo'];
			if(empty($imagepath))
				$imagepath = MERCHANT_IMAGE_PATH.'no-user.png';?>
		
					<tr>
					<td width="3%"><b><?php echo $i; ?></b></td>
					<td width="5%"><img class="photo_img_border" src="<?php echo $imagepath; ?>" width="50" height="50"></td>
					<td width="40%" style="font-size:18px;"><?php  echo  ucfirst($value["FirstName"]).' '.ucfirst($value["LastName"]);?></td>
					<td width="10%"><div class="text-right"><b><?php if(isset($value["TotalPrice"]) && $value["TotalPrice"]!= ''){ echo '&pound;'.number_format((float)$value["TotalPrice"],2,'.',',');}?></b></div></td>
					</tr>

	<?php $i++; } } else {?>
	<div class="col-xs-12 col-sm-12">
		<div align="center" class="alert alert-danger alert-dismissable col-lg-4 col-sm-8 col-xs-10">
		<i class="fa fa-fw fa-warning"></i> No results found</div>		
	</div>
	<?php } ?>
			</table>
			</div>

</div>
</div>
<style>
	.morris-hover{position:absolute;z-index:300;}
	.morris-hover.morris-default-style{border-radius:10px;padding:6px;color:#666;background:rgba(255, 255, 255, 0.8);border:solid 2px rgba(230, 230, 230, 0.8);font-family:sans-serif;font-size:12px;text-align:center;}
	.morris-hover.morris-default-style .morris-hover-row-label{font-weight:bold;margin:0.25em 0;}
	.morris-hover.morris-default-style .morris-hover-point{white-space:nowrap;margin:0.1em 0;}
	
</style>
<?php if(isset($product_array) && is_array($product_array) && count($product_array)>0) {?>

<script type="text/javascript">
	window.m = Morris.Bar({
		element: 'bar-canvas',
		data: [
			 <?php  if(isset($product_array) && is_array($product_array)) {
				  			foreach($product_array as $key=>$val){
								$aVal	=	trim(unEscapeSpecialCharacters($val["TotalPrice"]));
								//$yVal	=	trim(unEscapeSpecialCharacters($val["Name"]));
								$yVal	=	ucfirst($val["Name"]);
								?>
								
	                  	{y: decodeURI("<?php echo $yVal;?>"),a: '<?php echo $aVal;?>' },
				<?php  }
			}?>
		],
		xkey		: 'y',
		ykeys		: ['a'],
		labels		: ['&pound'],
		barColors	: ['#01a99a'],
		grid		: false,
		hideHover	: true,
		//axes		: false,
		horizontal 	: true,
		barGap		: 3,
		barSizeRatio: '0.99',
		stacked		: true,
		redraw		: false,
		resize		: true,
		hoverCallback: function (index, options, content, row) {
			if(row.a != ''){
				var labelVal  	= parseFloat(row.a);
				labelVal		=	labelVal.toFixed(2);
			}else{
				labelVal		= 0;
			}
		  return ""+row.y+"<br>"+"&pound"+labelVal;
		}
	});
</script>
<?php } ?>
<?php if(!empty($topSales)){
	if(isset($xarrays) && is_array($xarrays) && count($xarrays)>0) { ?>
	<script type="text/javascript">
			var chart = Morris.Line({
		  element: 'bar-chart1',
		  data: [
		   <?php  if(isset($xarrays) && is_array($xarrays)) {
				  			foreach($xarrays as $key=>$val){ if(isset($value_arrays[$key]) && $value_arrays[$key] != ''){
								?>
								
	                  	{y: '<?php echo $val?>',a: <?php echo $value_arrays[$key];?>},
						<?php } }
					}?>
	              ],
		  xkey: 'y',
		  ykeys: ['a'],
		  labels: ['&pound;'],
		  grid: false,
		  parseTime: false,
		  smooth:false,
		  lineColors:["#01a99a"],
		  lineWidth: 2,
		  hideHover	: true,
		  resize:true,
		  pointSize:4,
		  pointStrokeColors:["#fc7f09"],
		  hoverCallback		: 	function(index, options, content,row) {
									//return row.a+'%<br>'+row.y;
									return row.y+'<br>&pound;'+row.a;
								},
		});
	</script>
		<?php } }?>
	

 <?php  } ?>