<?php
	if(isset($_GET['action']) && !empty($_GET['action']) && $_GET['action'] == 'GET_LINECHART_DATA') {		
		if(isset($_GET['dataarray']) && !empty($_GET['dataarray']))	{			
			$temp = json_decode($_GET['dataarray']);
			foreach($temp as $val) {
				$result					=	Array();
				$result['OrderDate']	=	$val->OrderDate;
				$result['a']			=	$val->a;
				$result['b']			=	$val->b;
				$chart[]				=	$result;
			}
		}
	} else	{
		if(isset($Purchasesweek) && !empty($Purchasesweek))
			$chart = $Purchasesweek;	
	}	
?>
<div id="chart2">
	<div id="line-canvas"></div>
</div>				
<script type="text/javascript">
<?php if(isset($chart) && is_array($chart) && count($chart)>0) {?>
	var chart = Morris.Line({
		element				: 	'line-canvas',
		data				: 	[
									<?php  $i=0;
										foreach($chart as $key=>$val){ $i++; ?>	
										{ y:'<?php echo $val['OrderDate'];?>',a:<?php echo $val['a'];?>,b:<?php echo $val['b'];?> } <?php if($i<count($chart)) echo ",\n";else echo "\n";?>
									<?php  }?>  
								],
		xkey				: 	'y',
		ykeys				: 	['a','b'],
		labels				: 	['Current','Last'],
		parseTime			: 	false,
		smooth				:	false,
		lineColors			:	["#01a99a","#fc7f09"],
		pointSize			:	1,
		pointStrokeColors	:	["#01a99a","#fc7f09"],
		resize				: 	true,
		stacked				: 	true,
		gridIntegers		: 	true,
		ymin				: 	0
	});
<?php } else{?>
	//$("#chart2").append("<div id='sample' align='center' class='no-data'>No-data available</div>");
	$("#chart2").append('<div class="alert alert-danger alert-dismissable col-lg-11 col-sm-11 col-xs-11" align="center"><i class="fa fa-warning"></i>No result found</div>');
<?}?>	
</script>
<script type="text/javascript">
function get_linechart(id,option,dataarray){
	$.ajax({
		url : "LineChart",
		type: "GET",
		data: {"action":"GET_LINECHART_DATA","value":option,"dataarray":dataarray},
		success: function(response){
			$('#line-chart').html(response);
			if(option == 1) {
				$("#line_month").removeClass('chart-active');
				$("#line_week").addClass('chart-active');			
			} else {
				$("#line_week").removeClass('chart-active');
				$("#line_month").addClass('chart-active');
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