<?php
require_once('includes/CommonIncludes.php');
if(isset($_POST['action']) && $_POST['action']=='GET_CHART') { 
	$timeDay		=	0;
	if(isset($_POST['dataType']) && $_POST['dataType'] != '') {
		$date_type			=	$_POST['dataType'];
		$_SESSION['TuplitAnalyticsView']	=	$date_type;
	}
	if(!isset($_SESSION['tuplit_ses_from_timeZone']) || $_SESSION['tuplit_ses_from_timeZone'] == ''){
		 $time_zone = getTimeZone();
		 $_SESSION['tuplit_ses_from_timeZone'] = strval($time_zone);	 
	}
	$url					=	WEB_SERVICE.'v1/merchants/demographics/?DateType='.$date_type.'&TimeZone='.$_SESSION['tuplit_ses_from_timeZone'].'';
	$curlTopSaleResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlTopSaleResponse) && is_array($curlTopSaleResponse) && $curlTopSaleResponse['meta']['code'] == 201 && is_array($curlTopSaleResponse['Demographics']) ) {
		if(isset($curlTopSaleResponse['Demographics']['GenderList'])){
			$GenderList = $curlTopSaleResponse['Demographics']['GenderList'];	
		}
		if(isset($curlTopSaleResponse['Demographics']['AgeList'])){
			$AgeList   = $curlTopSaleResponse['Demographics']['AgeList'];	
		}
	} else if(isset($curlTopSaleResponse['meta']['errorMessage']) && $curlTopSaleResponse['meta']['errorMessage'] != '') {
			$errorMessage	=	$curlTopSaleResponse['meta']['errorMessage'];
	} else {
			$errorMessage	= 	"Bad Request";
	} 
}
$genderBreakdown	= 	array('0'=>'UNSPECIFIED','1'=>'MALE','2'=>'FEMALE');
$ageBreakdown 		= 	array('1'=>"<18",'2'=>"19-24",'3'=>"25-34",'4'=>"35-44",'5'=>">44");
$colors			  	= 	array("#FFEDA6","#01a99a","#fc7f09");
$i = 0;
$color1 = $color2 = $color3 = $class  = '';
?>
<div class="row">
		<div class="col-md-12 no-padding">
			<div class="box box-primary" id="chart-container">
				<div class="col-xs-12">
				<div class="col-sm-6 col-xs-12">
					<h3>Male/Female Breakdown</h3>
					<div class="" id="pie-chart" style="">
						<div id="chart1" style="">
							<?php if(!empty($GenderList)){?>
							<div id="legends" class="legend-category">
								<ul>
									<?php $totalVal = 0;$sub_class ='';
										foreach($GenderList as $key=>$value){
											$total		= 	$value['total'];
											$totalVal	+=	$total;
											if($value['Gendergroup'] == 1){
												$class = 'fa fa-male';
											}
											else if($value['Gendergroup'] == 2){
												$class = 'fa fa-female';
											}
											else if($value['Gendergroup'] == 0){
												$class = '';
												$sub_class = 'color:#000';
											}	
												
									?>
									<li>
										<span class="pie-chart" style="background:<?php  echo $colors[$value['Gendergroup']];?>;<?php echo $sub_class;?>">
											<i class="<?php echo $class;?>"></i>
											<?php echo $genderBreakdown[$value['Gendergroup']];?>
										</span>
									</li>
									<?php } ?>
								</ul>
							</div>
							<div id="pie-canvas" class="svg_content"></div>
							<?php } else {?>
							
							<div id="" align="center" class="alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10"><i class="fa fa-warning"></i> No data found</div>
							<?php } ?>
						</div>
					</div>
				</div>
				<div class="col-sm-6 col-xs-12 no-padding">
					<h3>Age Breakdown</h3>
					<div class="" id="bar-chart" style="">	
						<div id="chart1">
							<!-- <div class="y-axis-text"># of customers</div> -->
							<?php if(!empty($AgeList)){?>
							<div id="bar-canvas" style=""></div>
							<div style="text-align:center">age</div>
							<?php }else{ ?>
							<div id="" align="center" class="alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10"><i class="fa fa-warning"></i> No data found</div>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
			</div>	
		</div>		
	</div>
<style type="text/javascript">
svg text{
	fill:#000;
}
.svg_content{
	width:600px;
	height:400px;
}
</style>

<style type="text/css">
<!-- svg {width: 100% !important;}
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
} -->
/*.morris-hover{margin-left:300px;z-index:1000;}*/
/*
	svg{
	width:600px;
	height:400px;
}
*/
</style>
<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>raphel.min.js"></script>
<link href="<?php echo MERCHANT_STYLE_PATH; ?>theme/morris/morris.css" rel="stylesheet" type="text/css" />
<!-- Morris.js charts -->
<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>morris.js" type="text/javascript"></script>
<script type="text/javascript">
	<?php if(isset($GenderList) && is_array($GenderList) && count($GenderList)>0){?>
	var pie_chart = Morris.Pie({
		element: 'pie-canvas',
		data: [<?php  $i=1; foreach($GenderList as $key=>$value){	
				$percentage	= ($value['total']/$totalVal)*100; 
				if($value['Gendergroup'] == 1)
					$color1 = 1;
				else if($value['Gendergroup'] == 2)
					$color2 = 1;
				else if($value['Gendergroup'] == 0)
					$color3 = 1;?>
				{value: <?php echo number_format($percentage,2,'.','');?>, label: '<?php echo $genderBreakdown[$value['Gendergroup']];?>',formatter: '<?php echo number_format($percentage,2,'.','').'%';?>'}<?php if($i<count($GenderList)){ echo ",\n";} else {echo "\n";} $i++;?>
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
		/*label		: 'hide',
		resize		: true,
		redraw		: true,*/
		//hideHover	: 'always',
		/*legend: { show:true, location: 'south' },
		formatter	: function (x) { return x + "%"},
		showInLegend: false,*/
		
	});
	/*.on('click', function(i, row){
		console.log(i, row);
	});*/
<?php }?>
<?php if(isset($AgeList) && is_array($AgeList) && count($AgeList)>0){
?>
	var bar_chart = Morris.Bar({
		element: 'bar-canvas',
		data: [<?php  $i=1; foreach($AgeList as $key=>$value){	?>
				{y:'<?php echo $ageBreakdown[$value['Agegroup']];?>', a: <?php echo $value['total'];?>}<?php if($i<count($AgeList)){ echo ",\n";} else {echo "\n";} $i++;?>
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
		resize		: true,
		gridIntegers		: 	true,
		ymin				: 	0
		//yLabelFormat: function(y){return Math.round(y);}
		
	});
<?php }?>
//$(".morris-hover-point").text('');
</script>