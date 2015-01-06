<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();
$TotalOrders	=	$Start 	=	0;

if(isset($_GET['Start']) && !empty($_GET['Start'])) {
	$Start	=	$_GET['Start'];
}

//getting merchant details
if(isset($_SESSION['merchantDetailsInfo']) && is_array($_SESSION['merchantDetailsInfo'])){
	$merchantInfo  =	$_SESSION['merchantDetailsInfo'];	
}
else{
	$merchantId					= 	$_SESSION['merchantInfo']['MerchantId'];
	$url						=	WEB_SERVICE.'v1/merchants/'.$merchantId;
	$curlMerchantResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);	
	if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201 && $curlMerchantResponse['merchant']['MerchantId'] != '' ) 
	 {
		$_SESSION['merchantDetailsInfo']   =	$curlMerchantResponse['merchant'];
		$merchantInfo  =	$_SESSION['merchantDetailsInfo'];
	}
}

if(isset($_GET['viewId']) && $_GET['viewId'] != ''){
    $UserId			=	base64_decode($_GET['viewId']);
	//getting order list of users
	$url					=	WEB_SERVICE.'v1/orders/?UserId='.$UserId.'&Type=2&Start='.$Start;
	//echo $url;
	$curlOrderResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlOrderResponse) && is_array($curlOrderResponse) && $curlOrderResponse['meta']['code'] == 201 && is_array($curlOrderResponse['OrderList']) ) {
		if(isset($curlOrderResponse['OrderList'])){
			$orderList 	  = $curlOrderResponse['OrderList'];	
			$TotalOrders  = $curlOrderResponse['meta']['totalCount'];	
		}
	} else if(isset($curlOrderResponse['meta']['errorMessage']) && $curlOrderResponse['meta']['errorMessage'] != '') {
			$errorMessage	=	$curlOrderResponse['meta']['errorMessage'];
	} else {
			$errorMessage	= 	"Bad Request";
	} 
}				
if(isset($orderList) && count($orderList) > 0) {					
	$i = $Start + 1; 
	foreach($orderList as $value) { 
		$ProductName	=	'';								
		foreach($value['Products'] as $product)
			$ProductName .= $product['ItemName'].', ';
			
		$ProductName	=	trim($ProductName,', ');								
				
		?>
		<div id="Order_<?php echo $value['OrderId']; ?>" onclick="return hideshowProduct(<?php echo $value['OrderId']; ?>)" class="col-xs-11 no-padding order_history ">
									<div class="orderlist_num col-xs-1 no-padding valign"><?php echo $i; ?></div>
									<?php 
										$gmt_current_created_time = convertIntocheckinGmtSite($value['OrderDate']);
										$time	=  displayDate($gmt_current_created_time,$_SESSION['tuplit_ses_from_timeZone']);
									?>
									<div class="orderlist_time col-xs-2 no-padding valign"><?php echo $time; ?></div>
									<div class="col-xs-8 text-left"><?php echo $ProductName; ?></div>
									
									<div id="arrow_<?php echo $value['OrderId']; ?>" class="" style="position:absolute;right:-4px;z-index:100;">
										<div class="text-right angle-double-down"><i class="fa fa-angle-double-down fa-2"></i></div>
										<div class="text-right angle-double-up"><i class="fa fa-angle-double-up fa-2"></i></div>
									</div>
								</div>	
									

								<div class="hideshowProduct col-xs-12 no-padding" id="Product_<?php echo $value['OrderId']; ?>" style="display:none;">
									
									<!-- <div class="text-right angle-double-up"><i class="fa fa-angle-double-up fa-2"></i></div> -->
										<div class="col-xs-12 no-padding">
											<div class="order_details col-xs-12">Order Details</div>
										</div>
											
	
										<?php foreach($value['Products'] as $product) { ?>
										<div  class="col-xs-12">
											<div class="col-xs-1">&nbsp;</div>
											<div class="col-xs-1 text-left"><?php echo $product['ProductsQuantity'];?>pc</div>
											<div class="col-xs-7 text-left"><?php echo $product['ItemName'];?></div>
											<div class="col-xs-2 text-right"><b><?php echo price_fomat($product['TotalPrice']);?></b></div>
										</div>
										<?php } ?>
										<div class="col-xs-12">
										<div class="col-xs-1">&nbsp;</div>
										<div class="col-xs-10" style="border-top:1.5px solid #DFDFDF;margin:10px 0px 0px 10px;">&nbsp;</div>
											<div class="col-xs-12 no-padding">
												<div class="col-xs-1">&nbsp;</div>
												<div class="col-xs-7 text-left"><b>Sub Total</b></div>
												<div class="col-xs-3 text-right"><b><?php echo price_fomat($value['SubTotal']);?></b></div>
												<div class="col-xs-1">&nbsp;</div>
											</div>
											<div class="col-xs-12 no-padding">
												<div class="col-xs-1">&nbsp;</div>
												<div class="col-xs-7 text-left">VAT</div>
												<div class="col-xs-3 text-right"><b><?php echo price_fomat($value['VAT']);?></b></div>
												<div class="col-xs-1">&nbsp;</div>
											</div>
											<div class="col-xs-12 no-padding">
												<div class="col-xs-1">&nbsp;</div>
												<div class="col-xs-7 text-left"><b>Total</b></div>
												<div class="col-xs-3 text-right"><b><?php echo price_fomat($value['TotalPrice']);?></b></div>
												<div class="col-xs-1">&nbsp;</div>
											</div>
										</div>
										<input type="hidden" class="hidevalue" id="hidevalue_<?php echo $value['OrderId']; ?>" value="" />
									</div>										

	
	<?php $i++; 
	}
}
 ?>
						
