<?php	
	require_once('includes/CommonIncludes.php');
	require_once('controllers/AdminController.php');
	$search = '';
	$fields 		= ",o.TotalItems,o.OrderDate";
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
	$groupby	= " GROUP BY DATE_FORMAT(o.OrderDate,'%h') ORDER BY DATE_FORMAT(o.OrderDate,'%h')";
	$cond 		= $search ." and (DATE_FORMAT(o.OrderDate,'%Y-%m-%d')) BETWEEN DATE_SUB( CURDATE( ) ,INTERVAL 12 HOUR ) AND CURDATE( ) ".$groupby;
	$barArray['bar_tf']   = $orderObj->getTotalRevenue($fields,$cond);
	$groupby	= "GROUP BY DATE_FORMAT(o.OrderDate,'%Y-%m-%d') ORDER BY DATE_FORMAT(o.OrderDate,'%Y-%m-%d')";
	$cond 		= $search ." and (DATE_FORMAT(o.OrderDate,'%Y-%m-%d')) BETWEEN DATE_SUB( CURDATE( ) ,INTERVAL 7 DAY ) AND CURDATE( ) ".$groupby;
	$barArray['bar_ls']   = $orderObj->getTotalRevenue($fields,$cond);
	$groupby	= "GROUP BY MONTH(DATE_FORMAT(o.OrderDate,'%Y-%m-%d')) ORDER BY MONTH(DATE_FORMAT(o.OrderDate,'%Y-%m-%d'))";
	$cond 		= $search ." and date(o.OrderDate) >= '".date('Y-1-1',strtotime('this year'))."' and date(o.OrderDate) <= '".date('Y-m-d',strtotime('today'))."' ".$groupby;
	$barArray['bar_ly']   = $orderObj->getTotalRevenue($fields,$cond);		
	//echo "<pre>";print_r($barArray);echo "</pre>";
?>
<div class="col-xs-12 col-sm-4">
	<h3>Last 24 hours</h3>
	<p id='bar_tf_total' class="bar_value"></p>
	<div id="bar_tf" style="height:85px;width:200px;" class="chart_me">	</div>
</div>
<div class="col-xs-12 col-sm-4">
	<h3>Last 7 days</h3>
	<p id='bar_ls_total' class="bar_value"></p>
	<div id="bar_ls" style="height:85px;width:200px;" class="chart_me"></div>
</div>
<div class="col-xs-12 col-sm-4">
	<h3>Current Year</h3>
	<p id='bar_ly_total' class="bar_value"></p>
	<div id="bar_ly" style="height:85px;width:200px" class="chart_me"><div>
</div>
<style>
	/*.bar_value{
		color:#000;
		text-align:center;
		font-size:20px;
	}*/
.morris-hover{position:absolute;z-index:1000;margin-top:20px;}
.morris-hover.morris-default-style{border-radius:2px;padding:6px;color:#000;background:rgba(255, 255, 255,0);border:solid 2px rgba(255, 255, 255, 0);font-family:sans-serif;font-size:12px;text-align:center;}
.morris-hover.morris-default-style .morris-hover-row-label{font-weight:bold;margin:0.25em 0;position:relative;}
.morris-hover.morris-default-style .morris-hover-point{white-space:nowrap;margin:0.1em 0;}
</style>
<script src="<?php echo SITE_PATH;?>/admin/webresources/js/jquery-latest.js" type="text/javascript"></script>
<script src="<?php echo ADMIN_SCRIPT_PATH; ?>theme/plugins/morris/raphel.min.js" type="text/javascript"></script>
<script src="<?php echo ADMIN_SCRIPT_PATH; ?>theme/plugins/morris/morris.min.js" type="text/javascript"></script>
<script type="text/javascript">
/*
	$.each($('.chart_me'), function() {
	//alert(this.id);
	if(this.id == 'bar_ly')
		var color = '#fc7f09';
	else
		var color = '#01a99a';
*/
<?php if(isset($barArray) && is_array($barArray) && count($barArray)>0){
		foreach($barArray as $key=>$value){
		$i = $total = 0;
		if(is_array($value) && count($value)>0){
		//echo $key;
?>
Morris.Bar({
  element: '<?php echo $key;?>',
  data: [
<?php  foreach($value as $val){?>
    {y:'<?php if($key=='bar_tf') {echo date('h',strtotime($val->OrderDate));}else if($key=='bar_ls') {echo date('D',strtotime($val->OrderDate));}else if($key=='bar_ly') {echo date('M',strtotime($val->OrderDate));}?>', a:<?php echo number_format($val->TotalPrice,2,'.','');?>}<?php if($i<count($value)) {echo ",\n";}else {echo "\n";}?>
<?php 
	$total += $val->TotalPrice;
	}?>
  ],
  xkey: 'y',
  ykeys: ['a'],
  labels: [''],
  resize:true,
  hideHover:true,
  axes:false,
  grid:false,
  stacked: true,
  barColors:['<?php if($key=='bar_ly') echo '#fc7f09';else echo '#01a99a'?>']
});
<?php //$total = number_format($total,2);?>
$("#<?php echo $key;?>_total").html('<?php echo price_fomat($total,2); ?>');  //if($key=='bar_tf') else echo number_format($total,2);
<?php 
	}
	else{?>
		$("#<?php echo $key;?>_total").text('No-data available'); 
		$("#<?php echo $key;?>_total").addClass('no-data');
<?php
		}
	}
}

?>
/*
	Morris.Bar({
  element: 'bar_ls',
  data: [
    { y: '2006', a: 100  },
    { y: '2007', a: 75  },
    { y: '2008', a: 50  },
    { y: '2009', a: 75 },
    { y: '2010', a: 50  },
    { y: '2011', a: 75  },
    { y: '2012', a: 100 }
  ],
  xkey: '',
  ykeys: ['a'],
  labels: ['Series A'],
  resize:false,
  hideHover:'always',
  axes:false,
  grid:false,
  barColors:['#01a99a']
});
Morris.Bar({
  element: 'bar_ly',
  data: [
    { y: '2006', a: 100  },
    { y: '2007', a: 75  },
    { y: '2008', a: 50  },
    { y: '2009', a: 75 },
    { y: '2010', a: 50  },
    { y: '2011', a: 75  },
    { y: '2012', a: 100 }
  ],
  xkey: '',
  ykeys: ['a'],
  labels: ['Series A'],
  resize:false,
  hideHover:'always',
  axes:false,
  grid:false,
  barColors:['#fc7f09']
});
*/
//});

</script>