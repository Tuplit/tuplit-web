<?php
require_once('includes/CommonIncludes.php');
if(isset($_POST['action']) && $_POST['action']=='GET_CHART') { 
	$timeDay		=	1;
	if(isset($_POST['dataType']) && $_POST['dataType'] != '') {
		$date_type			=	$_POST['dataType'];
		$_SESSION['TuplitAnalyticsView']	=	$date_type;
	}
	if(isset($_POST['timeOfDay']) && $_POST['timeOfDay'] != ''){
		$timeDay			=	$_POST['timeOfDay'];
	}
	if(!isset($_SESSION['tuplit_ses_from_timeZone']) || $_SESSION['tuplit_ses_from_timeZone'] == ''){
		 $time_zone = getTimeZone();
		 $_SESSION['tuplit_ses_from_timeZone'] = strval($time_zone);	 
	}
	//$date_type  = 'day';
	$url					=	WEB_SERVICE.'v1/merchants/topProducts/?DataType='.$date_type.'&TimeZone='.$_SESSION['tuplit_ses_from_timeZone'].'';
	$curlProductResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlProductResponse) && is_array($curlProductResponse) && $curlProductResponse['meta']['code'] == 201 && is_array($curlProductResponse['TopProducts']) ) {
		if(isset($curlProductResponse['TopProducts']['result'])){
			$product_array	= 	$curlProductResponse['TopProducts']['result'];	
		}
		if(isset($curlProductResponse['TopProducts']['pieChart'])){
			$piechart_array	= 	$curlProductResponse['TopProducts']['pieChart'];	
		}
	} else if(isset($curlProductResponse['meta']['errorMessage']) && $curlProductResponse['meta']['errorMessage'] != '') {
			$errorMessage	=	$curlProductResponse['meta']['errorMessage'];
	} else {
			$errorMessage	= 	"Bad Request";
	} 
	if(isset($piechart_array) && is_array($piechart_array)) {
		foreach($piechart_array as $key=>$val){
			$discounted		=	((isset($val['specialProducts']) && $val['specialProducts'] != '')? $val['specialProducts'] : 0);
			$nonDiscounted	=	((isset($val['normalProducts']) && $val['normalProducts'] != '')? $val['normalProducts'] : 0);
			$total			= 	$discounted + $nonDiscounted;
			if($total > 0 ){
				$percentage1	= ($discounted/$total)*100;		
				$percentage2	= ($nonDiscounted/$total)*100;
			}else{ 
				$percentage1	= $percentage2	= 0;		
			} 
		}
	}
?>
<?php if(isset($product_array) && is_array($product_array) && count($product_array)>0) {
$product_count = count($product_array);
if($product_count == 1)
	$ht = 100;
else if($product_count > 4)
	$ht = 60;
else
	$ht = 100;

?>
<?php // $ht = (((count($product_array))>4)?((count($product_array))*75):((count($product_array))*85));?>
<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
	<h3 style="color:#202020;">Top Sellers</h3>
	<div id="bar-canvas" style="height:<?php  echo  $ht*$product_count; ?>px;"></div>
</div>
<?php }else { ?>
	<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
		<h3 style="color:#202020;">Top Sellers</h3>	
		 <div align="center" class="alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10">
		 	<i class="fa fa-fw fa-warning"></i> No results found</div>							
	</div>							
<?php } ?>	

<?php if(isset($piechart_array) && is_array($piechart_array) && count($piechart_array)>0) {?>
	
	<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 discount_items" style="align:right;" >
		<h3 style="color:#202020;">Discounted Vs Non discounted items</h3>
		<div class="col-xs-12 col-sm-12 no-padding" style="margin-bottom:20px;">
			<div class="col-xs-6 col-sm-6 col-md-6 col-lg-7 no-padding">
				<div id="donut1"></div>
			</div>

			<div style="font-size:12px;margin-top:20px;" class="col-xs-6 col-sm-6 col-md-6 col-lg-5 no-padding">
				<?php echo "<div class='col-xs-12 no-padding' style='margin-bottom:12px;'><div class='discount_circle'></div><div class='col-xs-8 col-sm-8 col-md-7 col-lg-7' style='padding:4px 0px 4px 10px;'><strong>DISCOUNTED</strong></div><div style='text-align:right;' class='col-xs-2 col-sm-3 col-md-3 col-lg-3 discount_per no-padding'> ".number_format($percentage1,2,'.','')."%"."</div></div>"."<div class='col-xs-12 no-padding'><div class='nondiscount_circle'></div><div class='col-xs-8 col-sm-8 col-md-7 col-lg-7' style='padding:4px 0px 4px 10px;'><strong>NON DISCOUNTED</strong></div><div style='text-align:right;' class='col-xs-2 col-sm-3 col-md-3 col-lg-3 discount_per no-padding'>    ".number_format($percentage2,2,'.','')."%"."</div></div>";?>
			</div>
			
		</div>
		<div class="col-xs-12 col-sm-12 no-padding">
			<div class="col-xs-6 col-sm-6 col-md-6 col-lg-7 no-padding">
				<div id="donut2"></div>
			</div>
			<div style="font-size:12px;margin-top:20px;" class="col-xs-6 col-sm-6 col-md-6 col-lg-5 no-padding">
				<?php echo "<div class='col-xs-12 no-padding' style='margin-bottom:12px;'><div class='discount_circle'></div><div class='col-xs-8 col-sm-8 col-md-7 col-lg-7' style='padding:4px 0px 4px 10px;'><strong>DISCOUNTED</strong></div><div style='text-align:right;' class='col-xs-2 col-sm-3 col-md-3 col-lg-3 discount_per no-padding'> &pound;".number_format($discounted,2,'.',',')."</div></div>"."<div class='col-xs-12 no-padding'><div class='nondiscount_circle'></div><div class='col-xs-8 col-sm-8 col-md-7 col-lg-7' style='padding:4px 0px 4px 10px;'><strong>NON DISCOUNTED</strong></div><div style='text-align:right;' class='col-xs-2 col-sm-3 col-md-3 col-lg-3 discount_per no-padding'> &pound;".number_format($nonDiscounted,2,'.',',')."</div></div>";?>
			</div>
		</div>
	</div>
<?php }else { ?>
	<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
		<h3 style="color:#202020;">Discounted Vs Non discounted items</h3>
		<div align="center" class="alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10">
		<i class="fa fa-fw fa-warning"></i> No results found</div>							
	</div>							
<?php } ?>	

	
<style>
	.morris-hover{position:absolute;z-index:300;}
	.morris-hover.morris-default-style{border-radius:10px;padding:6px;color:#666;background:rgba(255, 255, 255, 0.8);border:solid 2px rgba(230, 230, 230, 0.8);font-family:sans-serif;font-size:12px;text-align:center;}
	.morris-hover.morris-default-style .morris-hover-row-label{font-weight:bold;margin:0.25em 0;}
	.morris-hover.morris-default-style .morris-hover-point{white-space:nowrap;margin:0.1em 0;}
	
</style>

<script type="text/javascript">
	<?php if(isset($product_array) && is_array($product_array) && count($product_array)>0) {?>
	window.m = Morris.Bar({
		element: 'bar-canvas',
		data: [
			 <?php  if(isset($product_array) && is_array($product_array)) {
				  			foreach($product_array as $key=>$val){
								$aVal	=	trim(unEscapeSpecialCharacters($val["TotalPrice"]));
								//$yVal	=	trim(unEscapeSpecialCharacters($val["Name"]));
								//$yVal	=	ucfirst(escapeSpecialCharacters($val["Name"]));
								$yVal	=	ucfirst($val["Name"]);
								?>
	                  	{y: decodeURI("<?php echo $yVal;?>"),a:'<?php echo $aVal;?>' },
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
		//barGap	: '1',
		barSizeRatio: '0.99',
		stacked		: true,
		redraw		: false,
		resize		: false,
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
	<?php } ?>
	<?php if(isset($piechart_array) && !empty($piechart_array) && count($piechart_array)>0) {?>
	$(document).ready(function() {
		var barchart3 = new Morris.Donut({
			    element: 'donut1',
			    data: [
					{value:'<?php echo $percentage1;?>', label:"Discounted"},
					{value:'<?php echo $percentage2; ?>', label:"Non-Discounted"}
			    ],
				legend: { show:true, location: 'e' },
				resize: true,
	            colors: ["#01a99a","#fc7f09"],
				hideHover: 'auto',
			   	formatter: function (x) { 
					var labelVal	=	parseFloat(x);
					labelVal		=  	labelVal.toFixed(2);
					return  labelVal + "%"}
			    }).on('click', function(i, row){
			    console.log(i, row);
			    });
	  });
	<?php  } ?>
	<?php if(isset($piechart_array) && !empty($piechart_array)) {?>
	$(document).ready(function() {
		var barchart3 = new Morris.Donut({
			    element: 'donut2',
			    data: [
					{value:'<?php echo $discounted;?>', label:"Discounted"},
					{value:'<?php echo $nonDiscounted; ?>', label:"Non-Discounted"}
			    ],
				legend: { show:true, location: 'e' },
				resize: true,
	            colors: ["#01a99a","#fc7f09"],
				hideHover: 'auto',
			   	formatter: function (x) {
					var labelVal	=	parseFloat(x);
					labelVal		=  	labelVal.toFixed(2);
					//return '\u00A3'+ labelVal; 
					return '\u00A3'+ labelVal.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
					}
			    }).on('click', function(i, row){
			    console.log(i, row);
			    });
	  });
	<?php  }	?>
	
</script>
<?php } ?>
