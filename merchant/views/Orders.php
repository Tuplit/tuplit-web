<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();
if(isset($_GET['cs']) && $_GET['cs']==1) {
	unset($_SESSION['tuplitNewOrderTotal']);
	unset($_SESSION['tuplitNewOrderStart']);
}

//Rejecting order
if(isset($_GET['Reject']) || isset($_GET['Approve'])) {
	if(isset($_GET['Reject']) && !empty($_GET['Reject'])) {
		$data	=	array(
					'OrderId'		=> $_GET['Reject'],
					'OrderStatus'	=> '2'
					);
	}
	if(isset($_GET['Approve']) && !empty($_GET['Approve'])) {	
		$data	=	array(
					'OrderId'		=> $_GET['Approve'],
					'OrderStatus'	=> '1'
					);
	}
	$url				=	WEB_SERVICE.'v1/orders/';
	$method				=	'PUT';
	$curlResponse		=	curlRequest($url,$method,json_encode($data),$_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlResponse) && is_array($curlResponse) && $curlResponse['meta']['code'] == 201) {
		$_SESSION['successMessage'] = $curlResponse['notifications'][0];		
	} else if(isset($curlResponse['meta']['errorMessage']) && $curlResponse['meta']['errorMessage'] != '') {
		$errorMessage	=	$curlResponse['meta']['errorMessage'];
	} else {
		$errorMessage 	= 	"Bad Request";
	}
	if(isset($_GET['Reject'])){
		header("location:Orders?reject=1");
	}else if(isset($_GET['Approve'])){
		header("location:Orders?accept=1");
	}
	die();
}

if(isset($_SESSION['successMessage']) && !empty($_SESSION['successMessage'])) {
	$successMessage		=	$_SESSION['successMessage'];
	unset($_SESSION['successMessage']);
}
//getting merchant details
if(isset($_SESSION['merchantDetailsInfo']) && is_array($_SESSION['merchantDetailsInfo'])){
	$merchantInfo  				=	$_SESSION['merchantDetailsInfo'];	
	if(!empty($merchantInfo['DiscountTier']) || $merchantInfo['DiscountTier'] != 0 ) {
	}
	else {
		$hide					= 	1;	
	}
}

if(isset($_SESSION['merchantDetailsInfo']) && is_array($_SESSION['merchantDetailsInfo'])){
	$merchantInfo  				=	$_SESSION['merchantDetailsInfo'];	
}
else{
	$merchantId					= 	$_SESSION['merchantInfo']['MerchantId'];
	$url						=	WEB_SERVICE.'v1/merchants/'.$merchantId."?From=0";
	$curlMerchantResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201 && $curlMerchantResponse['merchant']['MerchantId'] != '' ) 
	 {
		$merchantInfo  			= 	$_SESSION['merchantDetailsInfo']   =	$curlMerchantResponse['merchant'];
	}
}
if(isset($_SESSION['merchantInfo']['AccessToken'])){ 	

	//getting Order List
	$url					=	WEB_SERVICE.'v1/orders/?Type=1';
	$curlCategoryResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlCategoryResponse) && is_array($curlCategoryResponse) && $curlCategoryResponse['meta']['code'] == 201 && is_array($curlCategoryResponse['OrderList']) ) {
		if(isset($curlCategoryResponse['OrderList']))
			$todayOrderList = $curlCategoryResponse['OrderList'];
	} 
	
	//getting new Order List
	$TotalNewOrders	 =	$totalorderlist =	0;
	if(isset($_SESSION['tuplitNewOrderStart']))
		$End	=	$_SESSION['tuplitNewOrderStart'];
	else
		$End	=	12;
	
	$url					=	WEB_SERVICE.'v1/orders/new?Type=1&End='.$End;
	//echo $url;
	$curlCategoryResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlCategoryResponse) && is_array($curlCategoryResponse) && $curlCategoryResponse['meta']['code'] == 201 && is_array($curlCategoryResponse['newOrderDetails']) ) {
		if(isset($curlCategoryResponse['newOrderDetails']))
			$TotalNewOrders		=	$curlCategoryResponse['meta']['totalCount'];
			$newOrderList = $curlCategoryResponse['newOrderDetails'];
			$_SESSION['tuplitNewOrderTotal']	=	$TotalNewOrders;
			if($TotalNewOrders > 12) {
				if($TotalNewOrders > $End)
					$totalorderlist	= $End;
				else
					$totalorderlist	=	$TotalNewOrders;
			}
			else
				$totalorderlist	=	$TotalNewOrders;
	} 
}

if(isset($errorMessage) && $errorMessage != ''){
	$msg			=	$errorMessage;
	$display 		= 	"block";
	$class   		= 	"alert-danger";
	$class_icon 	= 	"fa-warning";
	$errorMessage 	= 	'';
}/*else if(isset($successMessage) && $successMessage != ''){
	$msg			=	$successMessage;
	$display		=	"block";
	$class 			= 	"alert-success";
	$class_icon 	= 	"fa-check";
	$successMessage = 	'';	
}*/
commonHead();
?>
<body class="skin-blue fixed body_height">
		<?php top_header(); ?>		
		<section class="content top-spacing" align="center">
		<?php if(empty($merchantInfo['MangoPayUniqueId'])){?>
				<div align="center" class="alert alert-danger alert-dismissable  col-lg-5 col-sm-7  col-md-5 col-xs-12"><i class="fa fa-warning"></i>&nbsp;&nbsp;Please connect with MangoPay in Settings to view orders.</div>
		<?php }else{
			 if(isset($msg) && $msg != '') { ?>
			<div align="center" class="alert <?php  echo $class;  ?> alert-dismissable col-xs-10 col-sm-5 col-lg-3"><i class="fa <?php  echo $class_icon;  ?>"></i>  <?php echo $msg; ?></div>
			<?php } ?>			
			<div class="col-xs-12 col-md-12 col-lg-12 box-center row" >				
				<section class="content-header">
	                <h1 class="col-xs-12 col-sm-9 col-md-9 col-lg-10 no-padding text-left" style="margin-top:0px;margin-bottom:20px;">New Orders <?php if(!SERVER) { if(isset($_SESSION['tuplitNewOrderTotal']) && !empty($_SESSION['tuplitNewOrderTotal'])) echo " - ".$_SESSION['tuplitNewOrderTotal']; }  ?></h1>
					
				</section>
				<div class="clear order_list row">
					<div class="no-padding" id="NewOrderListHtml">
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
										$name = trim(ucfirst($value['FirstName']).' '.ucfirst($value['LastName']));
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
											<div class="col-xs-12 clear scroll-content-orders" style="height:60px;">
												<?php if(!empty($value['Products'])) {											
														foreach($value['Products'] as $key1=>$pro_val) { ?>		
															<div class="items_rows">																										
																<div class="col-xs-2 no-padding clear"><?php echo $pro_val['ProductsQuantity']; ?>pc</div>
																<div class="col-xs-6 no-padding"><?php echo  $pro_val['ItemName']?> </div>
																<div class="col-xs-4 no-padding text-right"><?php echo price_fomat($pro_val['TotalPrice']); ?></div>
															</div>
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
							<?php } } else { ?>
								<div class="alert alert-danger alert-dismissable col-xs-10 col-sm-5 col-lg-3" align="center"><i class="fa fa-warning"></i>&nbsp;&nbsp;No new orders found.</div>
							<?php } ?>
							<!-- End New Orders List -->						
							<?php if(isset($_SESSION['tuplitNewOrderTotal']) && !empty($_SESSION['tuplitNewOrderTotal']) && $_SESSION['tuplitNewOrderTotal'] > $End) { ?>
									<div class="col-xs-12 clear text-center" id="loadmorehome"> <a style="cursor:pointer" class="loadmore" id="loadmore" name="loadmore" class="btn btn-success" title="Load More" onclick="return loadMoreNewOrders();"><i class="fa fa-download"></i> <strong>Load More</strong></a><br><br></div>
							<?php } ?>
					</div>
					
					<!-- /.box-body -->
			
				</div>
				<input type="hidden" id="newOrderStart" name="newOrderStart" value="<?php if($End > 12) echo ($End - 12); else echo "0"; ?>" />
				<input type="hidden" id="newOrderTotalhide" name="newOrderTotalhide" value="<?php if(isset($_SESSION['tuplitNewOrderTotal'])) echo $_SESSION['tuplitNewOrderTotal']; ?>"/>
		 </div>
		 <div class="col-md-12 col-lg-12 box-center">				
				<section class="content-header">
	                <h1 class="col-sm-9 col-lg-10 no-padding text-left" style="margin-bottom:20px;">Today's Orders</h1>
	            </section>
				<div class="order_list clear row">
					<div class="no-padding">
					<!-- Start Today Orders List -->						
						<?php if(isset($todayOrderList) && !empty($todayOrderList)) {
								foreach($todayOrderList as $key=>$value) {
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
							<div class="small-box approve-box <?php echo $orderDivClass;?>"  style="height:100%;min-height:244px;">	
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
									<div style="float:right;position:absolute;right:12px;">
										<a id="printPopup" class="newWindow print_out" onclick="printPopup('<?php echo  $value['OrderId']; ?>');" title="Print"><em></em></a>
										<?php if(count($value['Products']) > 3) { ?>
										<a style="cursor:pointer" onclick="showFullOrder(<?php echo $value['OrderId']; ?>)"><i class="fa fa-search fa-lg"></i></a>
										<?php } ?>
									</div>
									<div class="col-xs-12 no-padding"><hr></div>
									<div class="col-xs-12 clear scroll-content-orders" style="height:60px;">
										<?php if(!empty($value['Products'])) {											
												foreach($value['Products'] as $key1=>$pro_val) { ?>																												
													<div class="col-xs-2 no-padding clear"><?php echo $pro_val['ProductsQuantity']; ?>pc</div>
													<div class="col-xs-6 no-padding"><?php echo  $pro_val['ItemName']?> </div>
													<div class="col-xs-4 no-padding text-right"><?php echo price_fomat($pro_val['TotalPrice']); ?></div>
										<?php }  } ?>
									</div>						
									
									<div class="col-xs-12 no-padding "><hr></div>	
									<div class="col-xs-8 HelveticaNeueBoldExtended sub_total"><strong>Sub Total</strong> </div>			
									<div class="col-xs-4 text-right HelveticaNeueBoldExtended sub_total"><strong><?php echo price_fomat($value['SubTotal']); ?></strong></div>
									<div class="col-xs-8 HelveticaNeueBoldExtended vat">VAT</div>			
									<div class="col-xs-4 text-right HelveticaNeueBoldExtended vat"><?php echo price_fomat($value['VAT']); ?></div>
									<div class="col-xs-8 HelveticaNeueBoldExtended "  style="padding-bottom:6px;"><strong>Total</strong> </div>			
									<div class="col-xs-4 text-right HelveticaNeueBoldExtended "  style="padding-bottom:6px;"><strong><?php echo price_fomat($value['TotalPrice']); ?></strong></div>
									
									<div class="col-xs-12 no-padding text-center btn btn-danger">
										<?php if($value['RefundStatus'] == 2) { $statusText = 'Rejected / Refunded'; $class='btn-danger'; } else {
										if($value['OrderStatus'] == 2) {$statusText = 'Rejected'; $class='';}else {$statusText = 'Approved'; $class='btn btn-success';}
										} ?>
										<a id="submit" class="no-link <?php echo $class;?>" title="<?php echo $statusText;?>"><em></em><?php echo $statusText;?></a>
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
									<div class="col-xs-12 clear ">
									<div class="">
										<?php if(!empty($value['Products'])) {											
													foreach($value['Products'] as $key1=>$pro_val) {?>
														<div class="col-xs-2 no-padding clear"><?php echo $pro_val['ProductsQuantity']; ?>pc</div>
														<div class="col-xs-6 no-padding"><?php echo  $pro_val['ItemName']?> </div>
														<div class="col-xs-4 no-padding text-right"><?php echo price_fomat($pro_val['TotalPrice']); ?></div>
										<?php } } ?>
									</div>
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
						<?php } }  else { ?>
							<div class="alert alert-danger alert-dismissable col-xs-10 col-sm-5 col-lg-3" align="center"><i class="fa fa-warning"></i>&nbsp;&nbsp;No orders approved / rejected today.</div>
						<?php } ?>
						<!-- End Today Orders List -->						
					</div><!-- /.box-body -->
				</div>					
		 </div>	
		 
 <!-- Rejected Popup -->
<div  id="rejectedPopup" style="display:none;">
	<form action="" name="rejected_popup"  method="post" >
		<div class="popup" id="firstDiv">
			<div class="form-group col-xs-12 no-padding" id="secondDiv" >
				<!-- <label class="col-xs-12">Category Name</label> -->
				<div class="col-xs-12 popup_title text-center"><h3>Your Order</h3></div>
				<div class="col-xs-12 delete_cat">
					<p class="help-block col-xs-12 text-center">You have rejected the order.</p>
				</div>
			</div>
			<div class="form-group col-xs-12 no-padding">
				<div class="complete_img">
					<img src="webresources/images/rejected.png" width="73" height="75" alt="">
					<h2 class="text-center text-red no-margin">Rejected!</h2>
				</div>
			</div>
			<div class="footer col-xs-12 text-center clear no-padding"> 
				<a href="Orders?cs=1" class="text-center btn btn-success col-xs-12">BACK TO ORDERS SCREEN</a>						
			</div>
		</div><!-- /row -->		
	</form>
</div>
<input type="hidden" id="rejected">
<!-- /Rejected Popup -->	
<!-- Accepted Popup -->
<div id="acceptedPopup" style="display:none;">
	<form action="" name="accepted_popup" id=""  method="post">
		<div class="popup" id="firstDiv">
			<div class="form-group col-xs-12" id="secondDiv" >
				<!-- <label class="col-xs-12">Category Name</label> -->
				<div class="col-xs-12 popup_title text-center"><h3>Your Order</h3></div>
				<div class="col-xs-12 delete_cat">
						<p class="help-block col-xs-12 text-center">You have approved the order</p>
				</div>
			</div>
			<div class="form-group col-xs-12 no-padding">
				<div class="complete_img">
					<img src="webresources/images/completed.png" width="73" height="75" alt="">
					<h2 class="text-center text-green no-margin">Completed!</h2>
				</div>
			</div>
			<div class="footer col-xs-12 text-center clear no-padding"> 
				<a href="Orders?cs=1" class="text-center btn btn-success col-xs-12">BACK TO ORDERS SCREEN</a>					
			</div>
		</div><!-- /row -->		
	</form>
	<input type="hidden" id="drag_pos_old" value=""/>
</div>
<!-- /Accepted Popup -->
<input type="hidden" id="manageOrderPopup">
<div id="manageOrderPrint" style="display:none"></div>
 <?php } ?>
</section>
<?php footerLogin();  commonFooter(); ?>
	<script type="text/javascript">
		(function($){
					$(window).load(function(){
					   $(".scroll-content-orders").mCustomScrollbar({
						callbacks:{
								onTotalScroll: function(){
									/*if($('#usertotalcounter').val() != '' && $('#userstartcounter').val() <= $('#usertotalcounter').val())
										getUserOredersList();*/
										//alert($('#mCSB_1_container').height());
								}
							}
						});
					});
				})(jQuery);
		function showFullOrder(orderId){
			var OrderContent	 =   $('#showFullOrder'+orderId).html(); 
			    $.fancybox({
			    	content: OrderContent, 
					'width': '350',
		        	'height': 'auto',
					autoSize: false,
				});
		}
		function printPopup(printid){
			var printId	= printid;
			$.ajax({
		        type: "GET",
		        url: '<?php echo MERCHANT_SITE_PATH;?>'+"/PrintOrder",
		        data: 'action=PRINT_POPUP&printId='+printId,
		        success: function (result){
					$("#manageOrderPrint").html(result);
					var popupContent	 =   $('#manageOrderPrint').html(); 
				    $.fancybox({
				    	content: popupContent, 
						'width': '350',
			        	'height': 'auto',
						autoSize: false,
					});
					$("#manageOrderPrint").print();
		        }			
		    });
		}
		
		$(document).ready(function() {
			$('.fancybox').fancybox();			
		});
		<?php if(isset($_GET['reject']) && $_GET['reject'] == 1){ ?>
			  	setTimeout( function() {
				var rejectedContent	 =   $('#rejectedPopup').html(); 
			    $.fancybox({
			    	content: rejectedContent, 
					'width': '350',
		        	'height': 'auto',
					autoSize: false,
				});
		 	},100);
		<?php }
			if(isset($_GET['accept']) && $_GET['accept'] == 1){  ?>
			setTimeout( function() {
				var acceptedContent	 =   $('#acceptedPopup').html(); 
			    $.fancybox({
			    	content: acceptedContent, 
					'width': '350',
		        	'height': 'auto',
					autoSize: false,
				});
			},100);
		<?php } ?>
		$(document).ready(function() {
			 /*(function($){
					$(window).load(function(){
					   $(".scroll-content-orders").mCustomScrollbar({
						});
					});
				})(jQuery);*/
			// $(".scroll-content-orders").mCustomScrollbar({});
			 
		});
		
	</script>
</html>
