<?php	
	$barArray	= array("bar_tf","bar_ls","bar_lm","bar_ly");
	if(isset($Last24Hours)) {
		foreach($Last24Hours as $val) {
			$temp 					= 	Array();
			$temp['OrderDate']		=	date('H a', strtotime(convertIntocheckinGmtSite($val['OrderDate'])));
			$temp['customers']		=	$val['Customers'];
			$barArray['bar_tf'][]	=	$temp;
		}
	}
	if(isset($Last7Days)) {
		foreach($Last7Days as $val) {
			$temp 					= 	Array();
			$temp['OrderDate']		=	date('D', strtotime(convertIntocheckinGmtSite($val['OrderDate'])));
			$temp['customers']		=	$val['Customers'];
			$barArray['bar_ls'][]	=	$temp;
		}
	}
	if(isset($Last30Days)) {
		foreach($Last30Days as $val) {
			$temp 					= 	Array();
			$temp['OrderDate']		=	date('M d', strtotime(convertIntocheckinGmtSite($val['OrderDate'])));
			$temp['customers']		=	$val['Customers'];
			$barArray['bar_lm'][]	=	$temp;
		}
	}
	if(isset($ThisYear)) {
		foreach($ThisYear as $val) {
			$temp 					= 	Array();
			$temp['OrderDate']		=	date('M', strtotime(convertIntocheckinGmtSite($val['OrderDate'])));
			$temp['customers']		=	$val['Customers'];
			$barArray['bar_ly'][]	=	$temp;
		}
	}

?>
<div id="chart3">
	<div class="col-xs-12 col-sm-3">
		<h4 class="no-padding no-margin text-center" style="color:#656565;">Last 24 hours</h4>
		<p id='bar_tf_total' class="bar_value text-center"></p>
		<div id="bar_tf" style="height:150px;width:auto;" class="chart_me">
			<div class="alert alert-danger alert-dismissable col-lg-11 col-sm-11 col-xs-11" align="center"><i class="fa fa-warning"></i> No result found</div>
		</div>
	</div>
	<div class="col-xs-12 col-sm-3">
		<h4 class="no-padding no-margin text-center" style="color:#656565;">Last 7 days</h4>
		<p id='bar_ls_total' class="bar_value text-center"></p>
		<div id="bar_ls" style="height:150px;width:auto;" class="chart_me">
			<div class="alert alert-danger alert-dismissable col-lg-11 col-sm-11 col-xs-11" align="center"><i class="fa fa-warning"></i> No result found </div>
		</div>
	</div>
	<div class="col-xs-12 col-sm-3">
		<h4 class="no-padding no-margin text-center" style="color:#656565;">Last 30 days</h4>
		<p id='bar_lm_total' class="bar_value text-center"></p>
		<div id="bar_lm" style="height:150px;width:auto;" class="chart_me" >
			<div class="alert alert-danger alert-dismissable col-lg-11 col-sm-11 col-xs-11" align="center"><i class="fa fa-warning"></i> No result found</div>
		</div>
	</div>
	<div class="col-xs-12 col-sm-3">
		<h4 class="no-padding no-margin text-center" style="color:#656565;">This Year</h4>
		<p id='bar_ly_total' class="bar_value text-center"></p>
		<div id="bar_ly" style="height:150px;width:auto;" class="chart_me" >
			<div class="alert alert-danger alert-dismissable col-lg-11 col-sm-11 col-xs-11" align="center"><i class="fa fa-warning"></i> No result found</div>
		</div>
	</div>
</div>
<style>
.morris-hover{position:absolute;z-index:1000;}
.morris-hover.morris-default-style{border-radius:0px;padding-top:60px;color:#666;margin-top:15px;background:rgba(255, 255, 255, 0);border:solid 0px rgba(255, 255, 255, 255);font-family:sans-serif;font-size:12px;text-align:center;}
.morris-hover.morris-default-style .morris-hover-row-label{font-weight:bold;margin-bottom:1em 0;position:relative}
.morris-hover.morris-default-style .morris-hover-point{white-space:nowrap;margin-bottom:0em 0;position:relative}
</style>
<script type="text/javascript">
<?php if(isset($barArray) && is_array($barArray) && count($barArray)>0){
		foreach($barArray as $key=>$value){
		$i = $total = 0;
		if(is_array($value) && count($value)>0){
		//echo $key;
?>
	Morris.Bar({
		element			: 	'<?php echo $key;?>',
		data			:	[
							<?php  foreach($value as $val){ ?>
								{y:'<?php if($key=='bar_tf') {echo $val['OrderDate'];}else if($key=='bar_ls') {echo $val['OrderDate'];}else if($key=='bar_lm') {echo $val['OrderDate'];}else if($key=='bar_ly') {echo $val['OrderDate'];}?>', a:<?php echo $val['customers'];?>}<?php if($i<count($value)) {echo ",\n";}else {echo "\n";}?>
							<?php 
								$total += $val['customers'];
							} ?>
							],
		xkey			: 	'y',
		ykeys			: 	['a'],
		labels			: 	[''],
		resize			:	true,
		hideHover		:	true,
		axes			:	false,
		grid 			:	false,
		stacked			: 	true,
		barColors		:	['<?php if($key=='bar_ly') echo '#fc7f09';else echo '#01a99a'?>'],
		hoverCallback	: 	function(index, options, content,row) {
								return row.y+'<br>'+row.a;
								//return row.a+'%';
							}
	});
	$("#<?php echo $key;?>_total").html('<?php echo $total; ?>');  //if($key=='bar_tf') else echo number_format($total,2);
<?php 
	}
	else { ?>
		$("#<?php echo $key;?>_total").text('No-data available'); 
		$("#<?php echo $key;?>_total").addClass('no-data');
<?php
		}
	}
}
?>
</script>