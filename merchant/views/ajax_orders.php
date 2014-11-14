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
						$ordersClass	=	$orderDivClass = '';
						if($value['TotalItems'] == 1){
							$ordersClass	=	'one_item';
							$orderDivClass	=	'one_items';
						}
						else if($value['TotalItems'] == 2){
							$ordersClass	=	'two_item';
							$orderDivClass	=	'two_items';
						}
						else if($value['TotalItems'] == 3){
							$ordersClass	=	'three_item';
							$orderDivClass	=	'three_items';
						}
						else if($value['TotalItems'] > 3){
							$ordersClass	=	'more_item';
							$orderDivClass	=	'more_items';
						}
						$name = ucfirst($value['FirstName']).' '.ucfirst($value['LastName']);
			?>
				<div class="col-md-4 col-sm-6 col-lg-3 col-xs-12">
					<div class="small-box  <?php echo $orderDivClass;?>" style="height:100%;min-height:244px;">	
						<div id="<?php echo $value['OrderId']; ?>" class="<?php if(!empty($value['Products']) && count($value['Products']) >= 4) echo "orderswipe"; ?> swipeless_<?php echo $value['OrderId']; ?>">
							<div class="col-md-3 col-lg-4 col-xs-4">
								<a onclick="return loaded;" href="<?php echo $value['Photo']?>" class="fancybox" title="<?php echo $name; ?>">
									<img height="65" width="65" src="<?php echo $value['ThumbPhoto']?>" alt=""/>
								</a>		
							</div>					
							<div class="col-md-9 col-lg-8 col-xs-8 min-height78">
								<span class="text-small" data-toggle="tooltip" title="<?php echo $name; ?>" data-original-title="<?php echo $name; ?>"><?php echo displayText($name,12); ?></span>
								<!-- <span class="help-block no-margin"><?php echo $value['Email']?></span> -->
								<span class="help-block no-margin HelveticaNeueBold"><?php echo $value['UserId']?></span>
								<span class="help-block no-margin time_post"><?php echo time_ago($value['OrderDate']); ?></span>
								
							</div>
							<div style="float:right;position:absolute;right:12px;">
								<a id="printPopup" class="newWindow print_out" style="cursor:pointer" onclick="printPopup('<?php echo  $value['OrderId']; ?>');" title="Print"><em></em></a>
								<?php if(count($value['Products']) > 3) { ?>
									<a style="cursor:pointer" onclick="showFullOrder(<?php echo $value['OrderId']; ?>)"><i class="fa fa-search fa-lg"></i></a>
								<?php } ?>
							</div>
							<div class="col-xs-12 no-padding"><hr></div>
							<div class="col-xs-12 clear scroll-content-orders mCustomScrollbar" style="height:60px;">
								<?php if(!empty($value['Products'])) {											
										foreach($value['Products'] as $key1=>$pro_val) { ?>																												
											<div class="col-xs-2 no-padding clear"><?php echo $pro_val['ProductsQuantity']; ?>pc</div>
											<div class="col-xs-6 no-padding"><?php echo  $pro_val['ItemName']?> </div>
											<div class="col-xs-4 no-padding text-right"><?php echo price_fomat($pro_val['TotalPrice']); ?></div>
								<?php }  } ?>
							</div>											
							<div class="col-xs-12 no-padding"><hr></div>
							<div class="col-xs-8 HelveticaNeueBoldExtended sub_total"><strong>Sub Total</strong> </div>
							<div class="col-xs-4 text-right HelveticaNeueBoldExtended sub_total"><strong><?php echo price_fomat($value['SubTotal']); ?></strong></div>
							<div class="col-xs-8 HelveticaNeueBoldExtended vat">VAT</div>
							<div class="col-xs-4 text-right HelveticaNeueBoldExtended vat"><?php echo price_fomat($value['VAT']); ?></div>
							<div class="col-xs-8 HelveticaNeueBoldExtended " style="padding-bottom:6px;"><strong>Total</strong> </div>
							<div class="col-xs-4 text-right HelveticaNeueBoldExtended " style="padding-bottom:6px;"><strong><?php echo price_fomat($value['TotalPrice']); ?></strong></div>		
						</div>									
						<div class="col-xs-12 no-padding min-height40" >
								<div class="col-xs-5 col-sm-4 col-md-4 col-lg-4 btn btn-default Rejected_class" style="float:left;width:34%;"><a class="text-red" class="Reject" href="?Reject=<?php echo  $value['OrderId']; ?>" onclick="return approveReject('reject');" ><em>&nbsp;</em> Reject</a></div>
								<div class="col-xs-7 col-sm-8 col-md-8 col-lg-8 text-right btn btn-success approve_class" style="float:right;width:66%;">
									<?php if($value['OrderDoneBy'] == 1) { ?><a href="?Approve=<?php echo  $value['OrderId']; ?>" id="submit" title="Approve" onclick="return approveReject('approve');"><em>&nbsp;</em>  Approve</a> <?php } else { ?><a href="#"  class="no-link" title="Approve">&nbsp;</a>
									<?php } ?>
								</div>
						</div>										
					</div>	
					<div class="small-box approve-box" id="showFullOrder<?php echo $value['OrderId']; ?>"  style="display:none">	
						<div id="<?php echo $value['OrderId']; ?>" class="<?php if(!empty($value['Products']) && count($value['Products']) >= 4) echo "orderswipe"; ?> swipeless_<?php echo $value['OrderId']; ?>">
							<div class="col-md-3 col-lg-4 col-xs-4">					
								<a onclick="return loaded;" href="<?php echo $value['Photo']?>" class="fancybox" title="<?php echo $name; ?>">
									<img height="65" width="65" src="<?php echo $value['ThumbPhoto']?>" alt=""/>
								</a>
									&nbsp;&nbsp;
									
							</div>		
							<div class="col-md-9 col-lg-8 col-xs-8 min-height78">				
								<span class="text-small" data-toggle="tooltip" title="<?php echo $name; ?>"><?php echo displayText($name,17); ?></span>
								<span class="help-block no-margin HelveticaNeueBold"><?php echo $value['UserId']?></span>
								<span class="help-block no-margin time_post"> <?php	echo time_ago($value['OrderDate']); ?> </span>
							</div>
							<div class="col-xs-12 no-padding"><hr></div>
							<div class="col-xs-12 clear">
								<?php if(!empty($value['Products'])) {											
											foreach($value['Products'] as $key1=>$pro_val) {?>
												<div class="col-xs-2 no-padding clear"><?php echo $pro_val['ProductsQuantity']; ?>pc</div>
												<div class="col-xs-6 no-padding"><?php echo  $pro_val['ItemName']?> </div>
												<div class="col-xs-4 no-padding text-right"><?php echo price_fomat($pro_val['TotalPrice']); ?></div>
								<?php } } ?>
							</div>							
							<div class="col-xs-12 no-padding"><hr></div>	
							<div class="col-xs-8 HelveticaNeueBoldExtendedsub_total"><strong>Sub Total</strong> </div>			
							<div class="col-xs-4 text-right HelveticaNeueBoldExtended sub_total"><strong><?php echo price_fomat($value['SubTotal']); ?></strong></div>
							<div class="col-xs-8 HelveticaNeueBoldExtended vat">VAT</div>			
							<div class="col-xs-4 text-right HelveticaNeueBoldExtended vat"><?php echo price_fomat($value['VAT']); ?></div>
							<div class="col-xs-8 HelveticaNeueBoldExtended "  style="padding-bottom:6px;"><strong>Total</strong> </div>			
							<div class="col-xs-4 text-right HelveticaNeueBoldExtended "  style="padding-bottom:6px;"><strong><?php echo price_fomat($value['TotalPrice']); ?></strong></div>
						</div>
					</div>
				</div> 			
			<?php } ?>
			<script type="text/javascript">
			
				 $(".scroll-content-orders").mCustomScrollbar();
			</script>
			<?php	} else if($TotalNewOrders > 0 ) { ?>
				<div class="alert alert-danger alert-dismissable col-xs-10 col-sm-5 col-lg-3" align="center"><i class="fa fa-warning"></i>&nbsp;&nbsp;No new orders found.</div>
			<?php } if(($_SESSION['tuplitNewOrderTotal'] - $_SESSION['tuplitNewOrderStart']) >= 1) { ?>			
			<div  class="col-xs-12 clear text-center" id="loadmorehome"><a class="loadmore" style="cursor:pointer" id="loadmore" name="loadmore" class="btn btn-success" title="Load More" onclick="return loadMoreNewOrders();"><i class="fa fa-download"></i> <strong>Load More</strong></a></div>
			<?php } ?>
		<!-- End New Orders List -->
<?php	
}
die();
?>


