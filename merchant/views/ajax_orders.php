<?php
require_once('includes/CommonIncludes.php');
//on loadmore
if(isset($_GET['orders']) && !empty($_GET['orders'])) {
	if(isset($_SESSION['merchantInfo']['AccessToken'])){ 
		$TotalNewOrders	=	0;
		if(isset($_SESSION['tuplitNewOrderStart']))
			$start	=	$_SESSION['tuplitNewOrderStart'];
		else
			$start	=	12;
		//getting new Order List
		$url					=	WEB_SERVICE.'v1/orders/new?Type=1&Start='.$start;
		$curlCategoryResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
		if(isset($curlCategoryResponse) && is_array($curlCategoryResponse) && $curlCategoryResponse['meta']['code'] == 201 && is_array($curlCategoryResponse['newOrderDetails']) ) {
			if(isset($curlCategoryResponse['newOrderDetails'])) {
				$TotalNewOrders		=	$curlCategoryResponse['meta']['totalCount'];
				$newOrderList = $curlCategoryResponse['newOrderDetails'];
				//$_SESSION['tuplitNewOrderTotal']	=	$TotalUsers;
				$_SESSION['tuplitNewOrderStart']	=	$start + 12;
			}				
		} 
	}
?>
		<!-- Start New Orders List -->						
			<?php if(isset($newOrderList) && !empty($newOrderList)) {					
					foreach($newOrderList as $key=>$value) {
						$ordersClass	=	'';
						if($value['TotalItems'] == 1)
							$ordersClass	=	'one_item';
						else if($value['TotalItems'] == 2)
							$ordersClass	=	'two_item';
						else if($value['TotalItems'] >= 3)
							$ordersClass	=	'more_item';
						$name = $value['FirstName'].' '.$value['FirstName'];
			?>
				<div class="col-md-3 col-sm-4 col-lg-2 col-xs-12">
					<div class="small-box">									
						<div class="col-xs-5 no-padding">
							<a href="<?php echo $value['Photo']?>" class="fancybox" title="<?php echo $value['FirstName']."&nbsp;".$value['LastName']; ?>">
								<img height="75" width="75" src="<?php echo $value['ThumbPhoto']?>" alt=""/>
							</a>							
						</div>					
						<div class="col-xs-7">
							<span data-toggle="tooltip" title="<?php echo $name; ?>"><?php echo displayText($name,7); ?></span>
							<span class="help-block no-margin"><?php echo $value['UserId']?></span>
							<span class="help-block no-margin"><?php echo time_ago($value['OrderDate']); ?></span>
							<a class="newWindow" href="PrintOrder?cs=1&printId=<?php echo  $value['OrderId']; ?>" ><i class="fa fa-print"></i></a>
						</div>
						<div class="col-xs-12 no-padding list_height <?php echo $ordersClass; ?>">
							<div class="help-block text-center col-xs-12 no-padding "><?php echo $value['Email']?></div>
							<?php if(!empty($value['Products'])) {											
									foreach($value['Products'] as $key1=>$pro_val) {
									if($key1 < 2) { ?>
									
									<div class="col-xs-8 no-padding"><?php echo  $pro_val['ItemName']?> </div>
									<div class="col-xs-4 no-padding text-right"><?php echo ' $'.number_format($pro_val['TotalPrice'],2,'.',','); ?></div>
										
									<?php } else {  ?> 
									
									<div class="col-xs-12 no-padding otherItemsNew<?php echo $key;?>" style="display:none;">
										<div class="col-xs-8 no-padding"><?php echo $pro_val['ItemName']; ?></div>
										<div class="col-xs-4 no-padding text-right"><?php echo ' $'.number_format($pro_val['TotalPrice'],2,'.',','); ?></div>
									</div>
							<?php } } ?>
								<div class="text-center col-xs-12 no-padding " <?php if(count($value['Products']) <= 2) {  ?>style="visibility:hidden;"<?php } ?>><a style="cursor:pointer" id="linkNew<?php echo $key; ?>" onclick="return showAllItems('New<?php echo $key; ?>');">Show all items</a></div>
							<?php } ?>
						</div>
						
						<div class="col-xs-12 no-padding"><hr></div>	
							
						<div class="col-xs-8 no-padding"><strong>Total</strong> </div>
						<div class="col-xs-4 no-padding text-right"><strong><?php echo ' $'.number_format($value['TotalPrice'],2,'.',','); ?></strong></div>		
												
						<!-- <div class="col-md-12 no-padding"><hr></div> -->
						<div class="col-xs-12" style="padding-top:7px;"></div>
						<div class="col-xs-4 no-padding"><a class="text-red" href="?Reject=<?php echo  $value['OrderId']; ?>" onclick="return approveReject('reject');" ><i class="fa fa-trash-o"></i> Reject</a></div>
						<?php if($value['OrderDoneBy'] == 1) { ?>
							<div class="col-xs-8 no-padding text-right"><a href="?Approve=<?php echo  $value['OrderId']; ?>"  id="submit" class="btn btn-success" title="Approve" onclick="return approveReject('approve');"><i class="fa fa-check"></i>  Approve</a></div>		
						<?php } ?>
					</div>
				</div> 				
			<?php } } else if($TotalNewOrders > 0 ) { ?>
				<div class="alert alert-danger alert-dismissable col-xs-10 col-sm-5 col-lg-3" align="center"><i class="fa fa-warning"></i>&nbsp;&nbsp;No new orders found.</div>
			<?php } if(($_SESSION['tuplitNewOrderTotal'] - $_SESSION['tuplitNewOrderStart']) >= 1) { ?>			
			<div  class="col-xs-12 clear text-center" id="loadmorehome"><a class="loadmore" style="cursor:pointer" id="loadmore" name="loadmore" class="btn btn-success" title="Load More" onclick="return loadMoreNewOrders();"><i class="fa fa-download"></i> <strong>Load More</strong></a></div>
			<?php } ?>
		<!-- End New Orders List -->
<?php	
}
die();
?>


