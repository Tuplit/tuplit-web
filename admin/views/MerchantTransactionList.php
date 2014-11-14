<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
require_once('controllers/OrderController.php');
$colorArray			= array('#008C81','#01B3A5','#02D2C2','#60F0E6','#A0F7EF');
$time_zone			= getTimeZone();
$time_zone_val		= strval($time_zone);
$curr_date 			= date('m/d/Y');
$condition_week		= $condition_day	= $condition = '';
$orderObj   		=   new OrderController();
//commonHead();
if(isset($_GET['merchantId']) && !empty($_GET['merchantId'])){
	$merchantIdVal	= $_GET['merchantId'];
	$condition 		= " and o.fkMerchantsId 	= ".$_GET['merchantId'];
	$condition_week .= " and o.fkMerchantsId 	= ".$_GET['merchantId'];
	$condition_day 	.= " and o.fkMerchantsId 	= ".$_GET['merchantId'];
}
$fields 			= " ";
$transactionTotal 	= $orderObj->getTotalRevenue($fields,$condition);

$condition_week 	.= 	" and (DATE_FORMAT(OrderDate,'%Y-%m-%d') <= '".date('Y-m-d',strtotime($curr_date))."' and DATE_FORMAT(OrderDate,'%Y-%m-%d') > '".date('Y-m-d',strtotime("-7 days"))."')";
$transactionWeekly 	= $orderObj->getTotalRevenue($fields,$condition_week);

$condition_day		.= 	" and date(DATE_ADD(OrderDate,INTERVAL '".$time_zone_val."' HOUR_MINUTE))='".date('Y-m-d',strtotime($curr_date))."'";
$transactiondays 	= $orderObj->getTotalRevenue($fields,$condition_day);
/*---------Transaction pie chart - popular products----------*/
$fields				=  "c.PurchasedDate,c.fkProductsId,count(c.fkProductsId) as total,sum(TotalPrice) as TotalPrice,p.ItemName";
$condition			= " and p.fkMerchantsId = '".$merchantIdVal."' and c.fkMerchantsId= '".$merchantIdVal."' ";
$TransactionArray	= $orderObj->getPopularProducts($fields,$condition);
//print_r($TransactionArray);


if(is_array($transactionTotal) && count($transactionTotal)>0){ ?>
<div class="col-xs-12 col-sm-4 Left">
	<p>
		Daily
		<span><?php if(!empty($transactiondays[0]->TotalPrice)){
						echo price_fomat($transactiondays[0]->TotalPrice); } 
				  else echo price_fomat('0');?></span>
	</p>
	<p>
		Weekly
		<span><?php if(!empty($transactionWeekly[0]->TotalPrice)){
						echo price_fomat($transactionWeekly[0]->TotalPrice); } 
				  else echo price_fomat('0');?></span>
	</p>
	<p>
		Average transaction revenue
		<span>
			<?php if(!empty($transactionTotal[0]->TotalPrice) && !empty($transactionTotal[0]->TotalOrders)){
						echo price_fomat($transactionTotal[0]->TotalPrice/$transactionTotal[0]->TotalOrders); } 
				  else echo price_fomat('0');?></span>
	</p>
</div>
<?php if(isset($TransactionArray) && !empty($TransactionArray)) { ?>
<div class="col-xs-12 col-sm-4 Middle">
<div>
	 <div class="box-body chart-responsive">
	     <div class="chart" id="bar-chart3" style="height: 300px;"></div>
	 </div>
</div> 
</div>
<?php } ?>
<div class="col-xs-12 col-sm-4 Right">
	<ul>
		<?php 
			$total = '0';
		if(is_array($TransactionArray) && count($TransactionArray)>0 ){ 
		foreach($TransactionArray as $key=>$val){
			 $total += $val->TotalPrice;?>
			
		<li>
			<i class="fa fa-square" style="color:<?php echo $colorArray[$key]; ?>"></i>
			<span class="fleft">
				<?php echo $val->ItemName; ?>
				<em><?php echo date('M  d, Y',strtotime($val->PurchasedDate)); ?></em>
			</span>
			<span class="fright"><?php echo price_fomat($val->TotalPrice); ?></span>
		</li>
		 <?php }?>
	</ul>
</div>
<?php  }?>
 <script src="<?php echo SITE_PATH;?>/admin/webresources/js/jquery-latest.js" type="text/javascript"></script>
 <script type="text/javascript">
<?php if(isset($TransactionArray) && !empty($TransactionArray)) {?>
	$(document).ready(function() {
		var barchart3 = new Morris.Donut({
			    element: 'bar-chart3',
			    data: [
				<?php  
					if(isset($TransactionArray) && is_array($TransactionArray)) {
						foreach($TransactionArray as $key=>$val){
							$percentage	= ($val->TotalPrice/$total)*100;
					 ?>
					{value:<?php echo number_format($percentage,2,'.','');?>, label:"<?php echo $val->ItemName;?>"}<?php echo ",";?>
					<?php } }?>
			    ],
				legend: { show:true, location: 'e' },
				resize: true,
	            colors: ["<?php echo $colorArray[0];?>","<?php echo $colorArray[1];?>","<?php echo $colorArray[2];?>","<?php echo $colorArray[3];?>","<?php echo $colorArray[4];?>"],
				hideHover: 'auto',
			   	formatter: function (x) { return  x + "%"}
			    }).on('click', function(i, row){
			    console.log(i, row);
			    });
	  });
	<?php  } ?>
</script> 
<?php } else { echo "no transactions";}