<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
require_once('controllers/UserController.php');
	$time_zone			= getTimeZone();
	$time_zone_val		= strval($time_zone);
	$curr_date 			= date('m/d/Y');
	$colorArray			= array('#008C81','#01B3A5','#02D2C2','#60F0E6','#A0F7EF');
	$condition_week		= $condition_day	= $condition = $condition_details ='';
	$userObj   			=  new UserController();
	$fields    			= ", u.FirstName,u.LastName,u.UniqueId,u.Photo";
	$condition 			.= " and u.Status in (1,2) ";
	if(isset($_GET['customerId']) && !empty($_GET['customerId'])){
		$condition 			.= " and o.fkUsersId 	= ".$_GET['customerId'];
		$condition_week 	.= " and o.fkUsersId 	= ".$_GET['customerId'];
		$condition_day 		.= " and o.fkUsersId 	= ".$_GET['customerId'];
		$condition_details 	.= " and o.fkUsersId 	= ".$_GET['customerId'];
	}
	$totalTransaction 	= $userObj->getCustomerTransaction($fields,$condition);
	
	$condition_week 	.= 	" and (DATE_FORMAT(OrderDate,'%Y-%m-%d') <= '".date('Y-m-d',strtotime($curr_date))."' and DATE_FORMAT(OrderDate,'%Y-%m-%d') > '".date('Y-m-d',strtotime("-7 days"))."')";
	$weeklyTransaction 	= $userObj->getCustomerTransaction($fields,$condition_week);
	
	$condition_day		.= 	" and date(DATE_ADD(OrderDate,INTERVAL '".$time_zone_val."' HOUR_MINUTE))='".date('Y-m-d',strtotime($curr_date))."'";
	$dayTransaction 	= $userObj->getCustomerTransaction($fields,$condition_day);
	
	$fields				= "sum(TotalPrice) as Total,o.OrderDate,m.FirstName,m.LastName,m.CompanyName";
	$transactionDetails	= $userObj->getTransactionDetails($fields,$condition_details);
	
	if(isset($totalTransaction) && is_array($totalTransaction) && count($totalTransaction) > 0 ) {
		//foreach($totalTransaction as $key=>$value){	echo $key;}
		$image_path 	= '';
		$photo 			= $totalTransaction[0]->Photo;
		$original_path 	= $image_path = ADMIN_IMAGE_PATH.'no_user.jpeg';
			$user_image = $photo;
			if(SERVER){
				if(image_exists(2,$user_image))
					$image_path 	= USER_THUMB_IMAGE_PATH.$user_image;
				if(image_exists(1,$user_image))
					$original_path 	= USER_IMAGE_PATH.$user_image;
			}else{
				if(file_exists(USER_IMAGE_PATH_REL.$user_image))
					$original_path 	= USER_IMAGE_PATH.$user_image;
				if(file_exists(USER_THUMB_IMAGE_PATH_REL.$user_image))
					$image_path 	= USER_THUMB_IMAGE_PATH.$user_image;
			}
?>
<?php //} ?>
<div class="list-comment col-md-12">
<?php  
	if(isset($image_path) && $image_path != ''){ 
		if($totalTransaction[0]->Photo 	 != '' ){ ?>
				<img src="<?php echo $image_path; ?>" width="110" height="110" />
		<?php } else { ?>
				<img src="<?php echo ADMIN_IMAGE_PATH.'no_user.jpeg'; ?>" width="110" height="110" />
		 <?}
	}?>	
	<span><?php  echo  (!empty($totalTransaction[0]->FirstName)?ucfirst($totalTransaction[0]->FirstName):'').' '.(!empty($totalTransaction[0]->LastName)?ucfirst($totalTransaction[0]->LastName):'');?></span>
	<span><?php  echo  "ID".' '.(!empty($totalTransaction[0]->UniqueId)?ucfirst($totalTransaction[0]->UniqueId):'');?></span>
</div>
<div class="col-md-12 no-padding h2margin">
	<h3>Transactions for a Customer</h3>
</div>
<div class="col-xs-12 col-sm-4 Left">
	<p>
		Daily
		<span><?php 
		if(!empty($dayTransaction[0]->TotalPrice)){
						echo price_fomat($dayTransaction[0]->TotalPrice); } 
				  else echo price_fomat('0');?></span>
	</p>
	<p>
		Weekly
		<span><?php if(!empty($weeklyTransaction[0]->TotalPrice)){
						echo price_fomat($weeklyTransaction[0]->TotalPrice); } 
				  else echo price_fomat('0');?></span>
	</p>
	<p>
		Average transaction revenue
		<span><?php if(!empty($totalTransaction[0]->TotalPrice) && !empty($totalTransaction[0]->TotalOrders)){
						echo price_fomat($totalTransaction[0]->TotalPrice/$totalTransaction[0]->TotalOrders); } 
				  else echo price_fomat('0');?></span>
	</p>
</div>
<?php if(isset($transactionDetails) && !empty($transactionDetails)) { ?>
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
		<?php  if(isset($transactionDetails) && is_array($transactionDetails) && count($transactionDetails) > 0 ) { 
				$total = 0;
				foreach($transactionDetails as $key=>$value){	?>
		<li>
			<i class="fa fa-square" style="color:<?php echo $colorArray[$key];?>"></i>
			<span class="fleft">
				<?php if(isset($value->CompanyName) && $value->CompanyName != ''){ echo ucfirst($value->CompanyName); }else echo "-"; ?>
				<em><?php if(isset($value->OrderDate) && $value->OrderDate != ''){ echo date('M  d, Y',strtotime($value->OrderDate)); }else echo "-"; ?></em>
			</span>
			<span class="fright"><?php if(isset($value->Total) && $value->Total != ''){ echo price_fomat($value->Total); } else echo price_fomat('0'); ?></span>
		</li>
		<?php $total += $value->Total;} }?>
	</ul>
</div>
<script src="<?php echo SITE_PATH;?>/admin/webresources/js/jquery-latest.js" type="text/javascript"></script>
<script type="text/javascript">
<?php if(isset($transactionDetails) && !empty($transactionDetails)) {
?>
	$(document).ready(function() {
		var barchart3 = new Morris.Donut({
			    element: 'bar-chart3',
			    data: [
				<?php  
					if(isset($transactionDetails) && is_array($transactionDetails)) {
						foreach($transactionDetails as $key=>$val){
					 ?>
					{value:<?php echo number_format((($val->Total/$total)*100),2,'.','');?>, label:"<?php echo ucfirst($val->CompanyName);?>"}<?php echo ",";?>
					<?php } }?>
			    ],
				resize: true,
	            colors: ["<?php echo $colorArray['0'];?>","<?php echo $colorArray[1];?>","<?php echo $colorArray[2];?>","<?php echo $colorArray[3];?>","<?php echo $colorArray[4];?>"],
				hideHover: 'auto',
			   	formatter: function (x) { return  x + "%"}
			    }).on('click', function(i, row){
			    console.log(i, row);
			    });
	  });
	<?php  } ?>
</script> 
<?php }
?>
