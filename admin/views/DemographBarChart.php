<?php
	require_once('includes/CommonIncludes.php');
	require_once('controllers/AdminController.php');
	require_once('controllers/UserController.php');
	$userObj   		=   new UserController();
	$ageBreakdown 	= 	array('1'=>"<18",'2'=>"19-24",'3'=>"25-34",'4'=>"35-44",'5'=>">44");
	$groupby		=	 " group by Agegroup order by Agegroup asc";
	$condition		= 	$groupby;
	$fields			=	" CASE WHEN Age <= 18 THEN '1' WHEN Age BETWEEN 19 AND 24 THEN '2' WHEN Age BETWEEN 25 AND 34 THEN '3' 
						WHEN Age BETWEEN 35 AND 44 THEN '4' WHEN Age >44 THEN '5'  END AS Agegroup, count( Age ) AS total ";
	$result 		=	$userObj->getUserDemographics($fields,$condition);
	//echo "<pre>"; echo print_r($result); echo "</pre>";//die();
	//$age_breakdown = array("<18"=>10,"19-24"=>40,"25-34"=>110,"35-44"=>160,">44"=>60)
?>
<div id="chart1">
	<!-- <div class="y-axis-text"># of customers</div> -->
	<?php if(!empty($result)){?>
	<div id="bar-canvas" style=""></div>
	<div style="text-align:center">age</div>
	<?php }else{ ?>
	<div id="" class="error" align="center">No data found</div>
	<?php } ?>
</div>
<style type="text/css">
svg {width: 100% !important;}
.morris-hover-row-label{  
  opacity: 0;
}
.y-axis-text{
	padding:10px;
	float:left;
	-webkit-transform: rotate(270deg);	
	-moz-transform: rotate(270deg);
	-ms-transform: rotate(270deg);
	-o-transform: rotate(270deg);
	transform: rotate(270deg);
}
/*.morris-hover{margin-left:300px;z-index:1000;}*/
/*
	svg{
	width:600px;
	height:400px;
}
*/
</style>
<script src="<?php echo SITE_PATH;?>/admin/webresources/js/jquery-latest.js" type="text/javascript"></script>
<script src="<?php echo ADMIN_SCRIPT_PATH; ?>theme/plugins/morris/raphel.min.js" type="text/javascript"></script>
<script src="<?php echo ADMIN_SCRIPT_PATH; ?>theme/plugins/morris/morris.js" type="text/javascript"></script>
<script type="text/javascript">
<?php if(isset($result) && is_array($result) && count($result)>0){?>
	window.m = Morris.Bar({
		element: 'bar-canvas',
		data: [<?php  $i=1; foreach($result as $key=>$value){	?>
				{y:'<?php echo $ageBreakdown[$value->Agegroup];?>', a: <?php echo $value->total;?>}<?php if($i<count($result)){ echo ",\n";} else {echo "\n";} $i++;?>
			<?php }?>
		],
		xkey		: 'y',
		ykeys		: ['a'],
		labels		: ['age'],
		barColors	: ['#01a99a'],
		grid		: true,
		//gridTextColor : '#888',
		//gridTextSize :14,
		hideHover	: 'always',
		stacked		: true,
		redraw		: true,
		resize		: true
		
	});
<?php }?>
//$(".morris-hover-point").text('');
</script>
