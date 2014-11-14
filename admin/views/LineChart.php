<?php	
	require_once('includes/CommonIncludes.php');
	require_once('controllers/AdminController.php');
	
/********------- day number of the month-----------*********/
	function days_in_month($month, $year){
	// calculate number of days in a month
	return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
	} 
/*----------------------------------------------------*/
	$search = '';
	if(isset($_SESSION['loc_mer_name']) && trim($_SESSION['loc_mer_name'])!=''){
		$search .= " and o.fkMerchantsId = '".$_SESSION['loc_mer_name']."'";	
	}
	if(isset($_SESSION['loc_mer_category']) && trim($_SESSION['loc_mer_category'])!=''){
		$search .= " and o.fkMerchantsId in (select group_concat(fkMerchantsId)  from merchantcategories where fkCategoriesId = '".$_SESSION['loc_mer_category']."') ";
	}
	if(isset($_SESSION['loc_mer_city']) && trim($_SESSION['loc_mer_city'])!=''){		
		$search .= " and o.fkMerchantsId in (select group_concat(id)  from merchants where City like '%".$_SESSION['loc_mer_city']."%')";
	}	
	if(isset($_SESSION['loc_mer_price']) && trim($_SESSION['loc_mer_price'])!=''){
		$search .= " and o.fkMerchantsId in (select group_concat(id) from merchants where '".$_SESSION['loc_mer_price']."' between substring_index(PriceRange,',',1) and substring_index(PriceRange,',',-1))";
	}
	if(isset($_GET['action']) && $_GET['action']!='' && $_GET['action']=="GET_LINECHART_DATA"){
		require_once('controllers/OrderController.php');
		$orderObj   	=   new OrderController();	
		$arg_cur		= $arg_prev = '';
		$cond = '';
		$fields 		= ",o.fkMerchantsId,o.TotalItems,o.OrderDate";
		$groupby		= "GROUP BY DATE_FORMAT( o.OrderDate, '%Y-%m-%d'), ORDER BY DATE_FORMAT( o.OrderDate, '%Y-%m-%d')";			
		if($_GET['option']==1 || $_GET['option']==3){
			$groupby		= "GROUP BY WEEK( o.OrderDate ),WEEK(o.OrderDate) ORDER BY WEEK( o.OrderDate )";
			//$cond .= " and DATE_FORMAT(o.OrderDate,'%Y-%m-%d') BETWEEN DATE_SUB( CURDATE( ) , INTERVAL 14 DAY )  AND CURDATE( )".$groupby;
			//$cond	.= " and date(o.OrderDate) >= '".date('Y-m-d ',strtotime('last week'))."' and date(o.OrderDate) <= '".date('Y-m-d ',strtotime('today'))."'".$groupby;
			$cond	.= $search." and date(o.OrderDate) >( CURDATE( ) - INTERVAL DAYOFWEEK( CURDATE( ) ) DAY) and date(o.OrderDate) <= '".date('Y-m-d ',strtotime('today'))."'".$groupby;
		}
		if($_GET['option']==2){
			$apply			= 1;
			$groupby		= "group by WEEK(o.OrderDate),MONTH( o.OrderDate) order by MONTH( o.OrderDate)";
			//$cond .= " and (DATE_FORMAT(o.OrderDate,'%Y-%m-%d')) BETWEEN DATE_SUB( CURDATE( ) ,INTERVAL 2 MONTH ) AND CURDATE( )".$groupby;
			$cond	.= $search."and date(o.OrderDate) >= '".date('Y-m-1',strtotime('last month'))."' and date(o.OrderDate) <= '".date('Y-m-d',strtotime('today'))."'".$groupby;
		}
		$PurchaseArray		= $orderObj->getTotalRevenue($fields,$cond);
	}	
	
/*******-------------Comparison on Week/Month-------**********/
	if(isset($PurchaseArray) && is_array($PurchaseArray)) {
		//$arg = '';
		if(isset($_GET['option']) && ($_GET['option']==1 || $_GET['option']==3)){
			$curr_week = date("W");
			$week_array = array('0'=>'Sun','1'=>'Mon','2'=>'Tue','3'=>'Wed','4'=>'Thu','5'=>'Fri','6'=>'Sat');
			//$arg 		= 'Week';
			$loop 		= 7;
			$start 		= 0;
			$arg_cur	= 'this week';
			$arg_prev	= 'tast week';
			foreach($PurchaseArray as $key=>$val){ 
				if($curr_week == date('W',strtotime($val->OrderDate))){					
					$curr_data[date('w',strtotime($val->OrderDate))] = $val->TotalOrders;
				}else{
					$prev_data[date('w',strtotime($val->OrderDate))] = $val->TotalOrders;
				}
			}
		}else if((isset($_GET['option']) && $_GET['option']==2) || !isset($_GET['option'])){
			//$cur 	= weeks_in_month(date("m"),date("Y"));
			//$prev 	= weeks_in_month(date("m")-1,date("Y"));
			$count1 = days_in_month(date("m"),date("Y"));
			$count2 = days_in_month(date("m"),date("last year"));
			$loop	= $count1>$count2?$count1:$count2;
			$arg 	= 'Month';
			$start 	= 1;
			$arg_cur	= date("M",strtotime('this month'));
			$arg_prev	= date("M",strtotime('last month'));
			foreach($PurchaseArray as $key=>$val){ 
				$order_month = date('m',strtotime($val->OrderDate));
				if($order_month== date("m")){
					//$week_curr = ceil( date( 'j', strtotime( $val->OrderDate ) ) / 7 );						
					$curr_data[date("j",strtotime( $val->OrderDate ))] = $val->TotalOrders;		
				}else{
					//$week_prev = ceil( date( 'j', strtotime( $val->OrderDate ) ) / 7 );	
					$prev_data[date("j",strtotime( $val->OrderDate ))] = $val->TotalOrders;
				}
			}
		}
	}
	//echo "<pre>";print_r($PurchaseArray);echo "</pre>";
?>
<style>
</style>
<div id="chart2">
	<div id="line-canvas" class="clear"></div>
</div>
<script src="<?php echo SITE_PATH;?>/admin/webresources/js/jquery-latest.js" type="text/javascript"></script>
<script src="<?php echo ADMIN_SCRIPT_PATH; ?>theme/plugins/morris/raphel.min.js" type="text/javascript"></script>
<script src="<?php echo ADMIN_SCRIPT_PATH; ?>theme/plugins/morris/morris.js" type="text/javascript"></script><script src="<?php echo ADMIN_SCRIPT_PATH; ?>theme/plugins/morris/morris.js" type="text/javascript"></script>
<script type="text/javascript">
<?php if(isset($PurchaseArray) && is_array($PurchaseArray) && count($PurchaseArray)>0) {?>
var chart = Morris.Line({
	  element: 'line-canvas',
	  data: [
	   <?php  for($i=$start;$i<$loop;$i++){
		?>	{ y:'<?php if(isset($week_array[$i])) {echo $week_array[$i];}else { echo $i;}?>',a:<?php if(isset($curr_data[$i])){ echo $curr_data[$i]; }else {echo '0';}?>,b:<? if(isset($prev_data[$i])) { echo $prev_data[$i];}else {echo '0';}?> } <?php if($i<$loop-1){ echo ",\n";} else {echo "\n";}?>
		<?php  }?>  ],
	  xkey: 'y',
	  ykeys: ['a','b'],
	  labels: ['<?php echo $arg_cur;?>', '<?php echo $arg_prev;?>'],
	  grid:false,
	  parseTime: false,
	  smooth:false,
	  lineColors:["#01a99a","#fc7f09"],
	  resize:true,
	  pointSize:1,
	  pointStrokeColors:["#01a99a","#fc7f09"]
	});
	<?php } else{?>
	$("#chart2").append("<div id='sample' align='center' class='no-data'>No-data available</div>");
	<?}?>	
</script>
<script type="text/javascript">
function get_linechart(id,option){
			$.ajax({
				url : "LineChart",
				type: "GET",
				data: {"action":"GET_LINECHART_DATA","option":option},
				dataType:'html',
				success: function(response){
					$('#line-chart').html(response);
					$(".line-chart").removeClass('chart-active');
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