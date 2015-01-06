<?php	
	require_once('includes/CommonIncludes.php');
	require_once('controllers/AdminController.php');
	$search = '';
	$fields = ",o.fkMerchantsId,o.TotalItems,o.OrderDate";
	if(isset($_SESSION['loc_mer_name']) && trim($_SESSION['loc_mer_name'])!=''){
		$search .= " and o.fkMerchantsId = '".$_SESSION['loc_mer_name']."'";	
	}
	if(isset($_SESSION['loc_mer_category']) && trim($_SESSION['loc_mer_category'])!=''){
		$search .= " and o.fkMerchantsId in (select group_concat(fkMerchantsId)  from merchantcategories where fkCategoriesId = '".$_SESSION['loc_mer_category']."') ";
	}
	if(isset($_SESSION['loc_mer_city']) && trim($_SESSION['loc_mer_city'])!=''){
		//$fields	.= "  as userArray";
		$search .= " and o.fkMerchantsId in (select group_concat(id)  from merchants where City like '%".$_SESSION['loc_mer_city']."%')";
	}	
	if(isset($_SESSION['loc_mer_price']) && trim($_SESSION['loc_mer_price'])!=''){
		$search .= " and o.fkMerchantsId in (select group_concat(id) from merchants where '".$_SESSION['loc_mer_price']."' between substring_index(PriceRange,',',1) and substring_index(PriceRange,',',-1))"; 
	}
	if(isset($_GET['action']) && $_GET['action']!='' && $_GET['action']=="GET_BARCHART_DATA"){
		require_once('controllers/OrderController.php');
		$orderObj   	=   new OrderController();
		$con = '';
		$groupby		= " group by DATE_FORMAT( o.OrderDate, '%Y-%m-%d') order by DATE_FORMAT( o.OrderDate, '%Y-%m-%d')";
		if($_GET['value']==1 || $_GET['value']==3){
			//$con .= " and (DATE_FORMAT(o.OrderDate,'%Y-%m-%d')) BETWEEN DATE_SUB( CURDATE( ) ,INTERVAL 7 DAY ) AND CURDATE( ) ".$groupby;
			//$con	.= " and date(o.OrderDate) > '".date('Y-m-d ',strtotime('last week'))."' and date(o.OrderDate) <= '".date('Y-m-d ',strtotime('today'))."'".$groupby;
			$con	.= $search." and date(o.OrderDate) >( CURDATE( ) - INTERVAL DAYOFWEEK( CURDATE( ) ) DAY) and date(o.OrderDate) <= '".date('Y-m-d ',strtotime('today'))."'".$groupby;
		}
		if($_GET['value']==2){
			$apply			= 1;
			$groupby		= " group by DATE_FORMAT( o.OrderDate, '%Y-%m-%d') order by DATE_FORMAT( o.OrderDate, '%Y-%m-%d')";
			
			//$con .= " and (DATE_FORMAT(o.OrderDate,'%Y-%m-%d')) BETWEEN DATE_SUB( CURDATE( ) ,INTERVAL 1 MONTH ) AND CURDATE( )".$groupby;
			$con	.= $search." and date(o.OrderDate) >= '".date('Y-m-1',strtotime('this month'))."' and date(o.OrderDate) <= '".date('Y-m-d',strtotime('today'))."'".$groupby;
			$month 	 = 1;
		}
		$result		= $orderObj->getTotalRevenue($fields,$con);
	}
		/*if(isset($result) && is_array($result)) {
			echo "<pre>";print_r($result);echo "</pre>";
		}*/
?>
<style>
.morris-hover{position:absolute;z-index:1000;}
.morris-hover.morris-default-style{border-radius:2px;padding:6px;color:#666;background:rgba(255, 255, 255, 0);border:solid 2px rgba(230, 230, 230, 0.8);font-family:sans-serif;font-size:12px;text-align:center;}
.morris-hover.morris-default-style .morris-hover-row-label{font-weight:bold;margin:0.25em 0;}
.morris-hover.morris-default-style .morris-hover-point{white-space:nowrap;margin:0.1em 0;}
svg{
	height:300px;
	width:500px;
}
</style>
<div id="chart1">
	<div id="area-canvas"></div>
</div>
<script src="<?php echo SITE_PATH;?>/admin/webresources/js/jquery-latest.js" type="text/javascript"></script>
<script src="<?php echo ADMIN_SCRIPT_PATH; ?>theme/plugins/morris/raphel.min.js" type="text/javascript"></script>
<script type="text/javascript">
$(document).ready(function() {
<?php if(isset($result) && is_array($result)) {?>
	var area = Morris.Area({
				  element		: 'area-canvas',
				  data: [
					<?php $i=0;
						foreach($result as $key=>$val){ 
						$i++;
								//$percentage	= ($val->TotalOrders/$total)*100;?>
						{ y:'<?php if(isset($apply) && $apply==1) echo date('j',strtotime($val->OrderDate)); else  echo date('D',strtotime($val->OrderDate));?>',a:<?php echo $val->TotalOrders; ?> }<?php if($i<count($result)) echo ",\n";else echo "\n";?>
					<?php } ?>
					],
				 behaveLikeLine		: true,
				 parseTime			: false,
				 xkey				: 'y',
				 ykeys				: ['a'],
				 //xLabels			: "year",
				 //xLabelFormat		: function (x) { return x.toString(['radix']);},
				 lineColors			: ["#01a99a"],
				 fillOpacity 		: 0.4,
				 smooth				: false,
				 grid				: false,
				 pointFillColors	: ["#fff"],
				 pointStrokeColors	: ["#fc7f09"],
				 pointSize			: 5,
				 stacked			: true,
				 labels				: ['value'],
				 hoverCallback		: function(index, options, content) {
										return('<?php if(isset($month)) echo date('M',strtotime($val->OrderDate)); else echo ''; ?>'+content);
									 },
				legend				: { show:true, location: 'e' },
				yLabelFormat 		: function (y) { return y.toString(); },
				//ymax 				: 10,
				ymin 				: 0,
				resize				: true,
				hideHover			: true
			});
		<?php }else{?>
			$("#chart1").append("<div id='sample' align='center' style='color:red;' class='error'>No data found<div>");	
		<?php  }?>
});
</script> 
<script type="text/javascript">
function get_areachart(id,option){
			$.ajax({
				url : "ChartContent",
				type: "GET",
				data: {"action":"GET_BARCHART_DATA","value":option},
				success: function(response){
					$("#bar-chart").html(response);
					$(".area-chart").removeClass('chart-active');
					$("#"+id).addClass('chart-active');					
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