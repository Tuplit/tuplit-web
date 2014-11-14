<?php
	require_once('includes/CommonIncludes.php');
	require_once('controllers/AdminController.php');
	require_once('controllers/UserController.php');
	$genderBreakdown	= 	array('0'=>'UNSPECIFIED','1'=>'MALE','2'=>'FEMALE');
	$userObj   			=   new UserController();
	$groupby			= 	" group by Gendergroup order by Gendergroup desc";
	$condition			= 	$groupby;
	$total				=   0;
	
	/* SELECT count(*) as Grandtotal,  CASE WHEN Gender = 0 THEN 'Unspecified' WHEN Gender = 1  THEN 'Male' WHEN 
		Gender = 2 THEN 'Female' END AS Gendergroup, count( Gender ) AS total, count(Gendergroup ) 
		as Grand  FROM users WHERE 1 group by Gendergroup order by Gendergroup asc
	*/
	
	$fields			= " CASE WHEN Gender = 0 THEN '0' WHEN Gender = 1  THEN '1' WHEN Gender = 2 THEN '2' END 
						AS Gendergroup, count( Gender ) AS total ";
	$result 		= $userObj->getUserDemographics($fields,$condition);
	//echo "<pre>"; echo print_r($result); echo "</pre>";//die();
	//$gender_breakdown 	= array("MALE"=>40,"FEMALE"=>30,"UNSPECIFIED"=>30);
	$colors			  	= array("#FFEDA6","#01a99a","#fc7f09");
	$i = 0;
	$color1 = $color2 = $color3 ='';
?>
<div id="chart1" style="">
	<?php if(!empty($result)){?>
	<div id="legends" class="legend-category">
		<ul>
			<?php $sub_class = '';
				foreach($result as $key=>$value){
					$total +=$value->total;
					if($value->Gendergroup == 1)
						$class = 'fa fa-male';
					else if($value->Gendergroup == 2)
						$class = 'fa fa-female';
					else if($value->Gendergroup == 0){
						$class = '';
						$sub_class = 'color:#000';
					}
			?>
			<li>
				<span class="pie-chart" style="background:<?php echo $colors[$value->Gendergroup];?>;<?php echo $sub_class;?>">
					<i class="<?php echo $class;?>"></i>
					<?php echo $genderBreakdown[$value->Gendergroup];?>
				</span>
			</li>
			<?php $i++;} ?>
		</ul>
	</div>
	<div id="pie-canvas" class="svg_content"></div>
	<?php } else {?>
	<div id="" class="error" align="center">No data found</div>
	<?php } ?>
</div>
<script src="<?php echo SITE_PATH;?>/admin/webresources/js/jquery-latest.js" type="text/javascript"></script>
<script src="<?php echo ADMIN_SCRIPT_PATH; ?>theme/plugins/morris/raphel.min.js" type="text/javascript"></script>
<script src="<?php echo ADMIN_SCRIPT_PATH; ?>theme/plugins/morris/morris.js" type="text/javascript"></script>
<script type="text/javascript">
$(window).bind('load', function(){
	<?php if(isset($result) && is_array($result) && count($result)>0){?>
	Morris.Pie({
		element: 'pie-canvas',
		data: [<?php  $i=1; foreach($result as $key=>$value){
				if($value->Gendergroup == 1)
					$color1 = 1;
				else if($value->Gendergroup == 2)
					$color2 = 1;
				else if($value->Gendergroup == 0)
					$color3 = 1;
				$percentage	= ($value->total/$total)*100; ?>
				{value: <?php echo number_format($percentage,2,'.','');?>, label: '<?php echo $genderBreakdown[$value->Gendergroup];?>'}<?php if($i<count($result)){ echo ",\n";} else {echo "\n";} $i++;?>
			<?php }?>
			],
		<?php if($color1 != '' &&  $color2 != '' &&  $color3 !=''){?>
			colors		: ["#fc7f09","#01a99a","#ffeda6"],
		<?php }else if($color1 != '' &&  $color2 !=''){?>
			colors		: ["#fc7f09","#01a99a"],
		<?php }else if($color1 != '' &&   $color3 !=''){?>
			colors		: ["#01a99a","#ffeda6"],
		<?php }else if($color2 != '' &&   $color3 !=''){?>
			colors		: ["#fc7f09","#ffeda6"],
		<?php }else if($color3 !=''){?>
			colors		: ["#ffeda6"],
		<?php }else if($color2 !=''){?>
			colors		: ["#fc7f09"],
		<?php }else if($color1 !=''){?>
			colors		: ["#01a99a"],
		<?php } ?>
		label		: 'hide',
		resize		: true,
		redraw		: true,
		//hideHover	: 'always',
		legend: { show:true, location: 'south' },
		formatter	: function (x) { return x + "%"}
		//showInLegend: false
	});
	/*.on('click', function(i, row){
		console.log(i, row);
	});*/
<?php }?>
});
</script> 
<style type="text/javascript">
svg text{
	fill:#000;
}
.svg_content{
	width:600px;
	height:400px;
}
</style>