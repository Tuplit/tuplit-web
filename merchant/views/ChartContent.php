<?php
$show = 1;	
if(isset($_GET['action']) && !empty($_GET['action']) && $_GET['action'] == 'GET_BARCHART_DATA') {		
	if(isset($_GET['dataarray']) && !empty($_GET['dataarray']))	{
		if($_GET['value'] == 1)
			$show 	= 	1;
		else
			$show 	=	2;
		$ajaxArray	=	json_decode($_GET['dataarray']);			
		foreach($ajaxArray as $val) {		
			$temp 				=	Array();
			$temp['OrderDate']	=	convertIntocheckinGmtSite($val->OrderDate);
			$temp['TotalOrder']	=	$val->Percentage;
			$result[]			=	$temp;
		}
	}
} else{	
	if(isset($PercentageGraphicWeek)) {		
		foreach($PercentageGraphicWeek as $val) {
			$temp 				=	Array();
			$temp['OrderDate']	=	convertIntocheckinGmtSite($val['OrderDate']);
			$temp['TotalOrder']	=	$val['Percentage'];
			$result[]			=	$temp;
		}
	}	
}	
?>
<style>
.morris-hover{position:absolute;z-index:1000;}
.morris-hover.morris-default-style{border-radius:2px;padding:6px;color:#666;background:rgba(255, 255, 255, 0);border:solid 2px rgba(230, 230, 230, 0.8);font-family:sans-serif;font-size:12px;text-align:center;}
.morris-hover.morris-default-style .morris-hover-row-label{font-weight:bold;margin:0.25em 0;}
.morris-hover.morris-default-style .morris-hover-point{white-space:nowrap;margin:0.1em 0;
svg{
	height:300px;
	width:500px;
}
</style>
<div id="chart1">
	<div id="area-canvas" style=""></div>
</div>				
<script type="text/javascript">
<?php if(isset($result) && is_array($result)) {?>
	var area = Morris.Area({
				element				: 	'area-canvas',
				data				: 	[
											<?php $i=0;
												foreach($result as $key=>$val){ 
												$i++;
											?>
												{ y:'<?php if($show == 1)	echo date("D",strtotime($val['OrderDate'])); else  echo date("d",strtotime($val['OrderDate']));?>',a:<?php echo $val['TotalOrder'];?> }
													<?php if($i<count($result)) echo ",\n";else echo "\n";?>
											<?php } ?>
										],
				 behaveLikeLine		: 	true,
				 parseTime			: 	false,
				 xkey				: 	'y',
				 ykeys				: 	['a'],
				 lineColors			: 	["#01a99a"],
				 fillOpacity 		: 	0.4,
				 smooth				: 	false,
				 pointFillColors	: 	["#fff"],
				 pointStrokeColors	: 	["#fc7f09"],
				 pointSize			: 	5,
				 labels				: 	['value'],
				legend				: 	{ show:true, location: 'e' },
				yLabelFormat 		: 	function (y) { return y.toString(); },
				ymin 				: 	0,
				hideHover			: 	true,
				stacked				: 	true,
				redraw				: 	true,
				resize				: 	true,
				hoverCallback		: 	function(index, options, content,row) {
											//return row.a+'%<br>'+row.y;
											return row.a+'%';
										},
				yLabelFormat		: 	function (y) { return Math.floor(y); }
			});
		<?php }else{?>
			//$("#chart1").append("<div id='sample' align='center' class='error'>No-data available<div>");	
			$("#chart1").append('<div class="alert alert-danger alert-dismissable col-lg-11 col-sm-11 col-xs-11" align="center"><i class="fa fa-warning"></i>No result found</div>');
		<?php  }?>
</script> 
<script type="text/javascript">
function get_areachart(id,option,dataarray){
	$.ajax({
		url : "ChartContent",
		type: "GET",
		data: {"action":"GET_BARCHART_DATA","value":option,"dataarray":dataarray},
		success: function(response){
			$("#area-chart").html(response);
			if(option == 1) {
				$("#bar_month").removeClass('chart-active');
				$("#bar_week").addClass('chart-active');			
			} else {
				$("#bar_week").removeClass('chart-active');
				$("#bar_month").addClass('chart-active');
			}
		},
		beforeSend: function(){
			// Code to display spinner
			$('.loader-merchant').show();
		},
		complete: function(){
		// Code to hide spinner.
		 $('.loader-merchant').hide();
		}
	});
}
</script>
