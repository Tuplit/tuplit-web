<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();
$TotalOrders	=	0;
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
	$url					=	WEB_SERVICE.'v1/orders/?UserId='.$UserId.'&Type=2';
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
//echo "<pre>";print_r($orderList);echo "</pre>";
//popup_head();
commonHead();
?>

<body class="skin-blue fixed popup_bg">
	<div class="popup_white">
		<div class="col-xs-12 customer_details no-padding">
			<?php if(isset($orderList) && !empty($orderList) && count($orderList) > 0) { ?>				
				<div class="text-center" style="padding-top:30px;padding-bottom:20px;">
					<img class="photo_img_border" width="75" height="75" src="<?php echo $orderList[0]['Photo']; ?>"><br>
					<div class="total_order"><div class="total_text"><?php echo $TotalOrders; ?></div></div>
					<div class="userorder_name"><?php echo $orderList[0]['FirstName']." ".$orderList[0]['LastName']; ?></div>
					<div class="userorder_id"><b><?php echo $orderList[0]['UserId']; ?> </b></div>
					<?php 
						$gmt_current_created_time = convertIntocheckinGmtSite($orderList[0]['OrderDate']);
						$time	=  displayDate($gmt_current_created_time,$_SESSION['tuplit_ses_from_timeZone']);
					?>
					<div class="userorder_time"><?php echo $time; ?> </div>
					<h5 class="title"><strong>Order History</strong></h5>
					<br>
					<?php 
						$ordercount 	= 	count($orderList);
						$orderheight	=	400;
						if($ordercount == 1) 		$orderheight 	=	120;
						else if($ordercount == 2) 	$orderheight 	=	140;
						else if($ordercount == 3) 	$orderheight 	=	160;
						else if($ordercount == 4) 	$orderheight 	=	180;
						else if($ordercount == 5) 	$orderheight 	=	200;
						else if($ordercount == 6) 	$orderheight 	=	220;
						else if($ordercount == 7) 	$orderheight 	=	240;
					?>
					
					<div class="scroll-content-Products mCustomScrollbar user_orderlist" style="height:<?php echo $orderheight; ?>px;">
						<div class="OrderList" id="OrderList">
							<div id="OrderListBody">
								<?php 	$i = 1; 
										foreach($orderList as $value) { 
										$ProductName	=	'';								
										foreach($value['Products'] as $product)
											$ProductName .= $product['ItemName'].', ';
											
										$ProductName	=	trim($ProductName,', ');								
										
								?>
	
								<div id="Order_<?php echo $value['OrderId']; ?>" onclick="return hideshowProduct(<?php echo $value['OrderId']; ?>)" class="col-xs-11 no-padding order_history ">
									<div class="orderlist_num col-xs-1 col-sm-1 col-md-1 col-lg-1 no-padding valign"><?php echo $i; ?></div>
									<?php 
										$gmt_current_created_time = convertIntocheckinGmtSite($value['OrderDate']);
										$time	=  displayDate($gmt_current_created_time,$_SESSION['tuplit_ses_from_timeZone']);
									?>
									<div class="orderlist_time col-xs-3 col-sm-2 col-md-2 col-lg-2 no-padding valign"><?php echo $time; ?></div>
									<div class="col-xs-7 col-sm-8 col-md-8 col-lg-8 text-left"><?php echo $ProductName; ?></div>
									
									<div id="arrow_<?php echo $value['OrderId']; ?>" class="arrow-up-down">
										<div class="text-right angle-double-down"><i class="fa fa-angle-double-down fa-2"></i></div>
										<div class="text-right angle-double-up"><i class="fa fa-angle-double-up fa-2"></i></div>
									</div>
								</div>	
									

								<div class="hideshowProduct col-xs-12 no-padding" id="Product_<?php echo $value['OrderId']; ?>" style="display:none;">
									<div class="col-xs-12 no-padding">
										<div class="order_details col-xs-12">Order Details</div>
									</div>

									<?php foreach($value['Products'] as $product) { ?>
									<div  class="col-xs-12 popup_details">
										<div class="col-xs-1">&nbsp;</div>
										<div class="col-xs-1 no-padding text-right"><?php echo $product['ProductsQuantity'];?>pc</div>
										<div class="col-xs-6" align="left"><?php echo $product['ItemName'];?></div>
										<div class="col-xs-3 text-right"><b><?php echo price_fomat($product['TotalPrice']);?></b></div>
									</div>
									<?php } ?>
									<div class="col-xs-12 popup_details">
										<div class="col-xs-1">&nbsp;</div>
										<div class="col-xs-10 no-padding" style="border-top:1.5px solid #DFDFDF;margin-top:10px;">&nbsp;</div>
										<div class="col-xs-12 no-padding">
											<div class="col-xs-1">&nbsp;</div>
											<div class="col-xs-7 text-left"><b>Sub Total</b></div>
											<div class="col-xs-3 text-right"><b><?php echo price_fomat($value['SubTotal']);?></b></div>
											<div class="col-xs-1 no-display">&nbsp;</div>
										</div>
										<div class="col-xs-12 no-padding">
											<div class="col-xs-1">&nbsp;</div>
											<div class="col-xs-7 text-left">VAT</div>
											<div class="col-xs-3 text-right"><b><?php echo price_fomat($value['VAT']);?></b></div>
											<div class="col-xs-1 no-display">&nbsp;</div>
										</div>
										<div class="col-xs-12 no-padding">
											<div class="col-xs-1">&nbsp;</div>
											<div class="col-xs-7 text-left"><b>Total</b></div>
											<div class="col-xs-3 text-right"><b><?php echo price_fomat($value['TotalPrice']);?></b></div>
											<div class="col-xs-1 no-display">&nbsp;</div>
										</div>
									</div>
									<input type="hidden" class="hidevalue" id="hidevalue_<?php echo $value['OrderId']; ?>" value="" />
								</div>										
								<?php $i++; } ?>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xs-12 no-padding">
					<div class="col-xs-12 btn btn-default Rejected_class">
						<a class="text-red" onclick="parent.jQuery.fancybox.close();">
							<em>&nbsp;</em> CLOSE
						</a>
					</div>

					<input type="hidden" name="userstartcounter" id="userstartcounter" value="0"/> 
					<input type="hidden" name="usertotalcounter" id="usertotalcounter" value=""/>
					<input type="hidden" name="UserID" id="UserID" value="<?php echo base64_encode($UserId); ?>"/>
				</div>				
			<?php } ?>	
		</div>
	</div>
	<?php commonFooter(); ?>
	<script>
		$(document).ready(function() {	
			$('#usertotalcounter').val(<?php echo $TotalOrders; ?>);
			$('#userstartcounter').val(0);
			 $(".scroll-content-Products").mCustomScrollbar( <?php if($TotalOrders > 10) { ?> {
				callbacks:{
					onTotalScroll: function(){
						if($('#usertotalcounter').val() != '' && $('#userstartcounter').val() <= $('#usertotalcounter').val())
							getUserOredersList();
					}
				}				
			 } <?php } ?> );			 
		});
	</script>
</html>
