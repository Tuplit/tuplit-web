<?php popup_head()?>
<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();
$show	=	0;
if(isset($_GET['show']) && !empty($_GET['show']) && $_GET['show'] == 1) 
	$show	=	1;
if(isset($_SESSION['merchantInfo']['AccessToken'])){ 	
	if(isset($_GET['orderId']) && !empty($_GET['orderId']) || isset($_GET['transId']) && !empty($_GET['transId'])) {
		//getting Order List
		if(!empty($_GET['orderId'])) {
			$OrderId			=	$_GET['orderId'];
			$url				=	WEB_SERVICE.'v1/orders/'.$OrderId.'';
		}
		if(!empty($_GET['transId'])) {
			$transId			=	$_GET['transId'];
			$url				=	WEB_SERVICE.'v1/orders/'.$transId.'?Type=1';
		}
		$curlCategoryResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
		if(isset($curlCategoryResponse) && is_array($curlCategoryResponse) && $curlCategoryResponse['meta']['code'] == 201 && is_array($curlCategoryResponse['OrderDetails']) ) {
			if(isset($curlCategoryResponse['OrderDetails']))
				$OrderList = $curlCategoryResponse['OrderDetails'];
		} 
	}
}
if(isset($_GET['cs']) && $_GET['cs'] == 1) {
	unset($_SESSION['refund_errorMessage']);
	unset($_SESSION['refund_successMessage']);
}

//refunding full order
if(isset($_GET['OrderID']) && !empty($_GET['OrderID'])) {
	if(isset($_GET['ProductId']) && !empty($_GET['ProductId']))
		$url				=	WEB_SERVICE.'v1/orders/refund/'.$_GET['OrderID'].'?Type=2&ProductId='.$_GET['ProductId'];
	else
		$url				=	WEB_SERVICE.'v1/orders/refund/'.$_GET['OrderID'].'?Type=1&msg='. base64_encode($_POST['refund_msg']);
	$method				=	'GET';
	$curlResponse		=	curlRequest($url,$method,'',$_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlResponse) && is_array($curlResponse) && $curlResponse['meta']['code'] == 201 && isset($curlResponse['OrderRefund'])) {
		//$_SESSION['refund_successMessage'] = $curlResponse['notifications'][0];		
		$_SESSION['refund_successMessage'] = 'Refund has been done successfully';
		$OrderList['RefundStatus']	=	'2';
	} else if(isset($curlResponse['meta']['errorMessage']) && $curlResponse['meta']['errorMessage'] != '') {
		$_SESSION['refund_errorMessage']	=	$curlResponse['meta']['errorMessage'];
	} else {
		$_SESSION['refund_errorMessage'] 	= 	"Bad Request";
	}
	header("location:OrderProductDetail?orderId=".$_GET['OrderID']);
	die();
}

if(isset($_SESSION['refund_successMessage']))
	$successMessage	=	$_SESSION['refund_successMessage'];
if(isset($_SESSION['refund_errorMessage']))
	$errorMessage	=	$_SESSION['refund_errorMessage'];
if(isset($errorMessage) && $errorMessage != ''){
	$msg			=	$errorMessage;
	$display 		= 	"block";
	$class   		= 	"alert alert-danger";
	$class_icon 	= 	"fa-warning";
	$errorMessage 	= 	'';
}else if(isset($successMessage) && $successMessage != ''){
	$msg			=	$successMessage;
	$display		=	"block";
	$class 			= 	"alert-success";
	$class_icon 	= 	"fa-check";
	$successMessage = 	'';	
}
//commonHead();
?>

	
<body class="skin-blue fixed popup_bg" >
<div class="popup_white">
<div style="padding:0 15px">
<section class="content-header"><h1 class="space_bottom">Product List</h1></section>
<?php if(isset($msg) && $msg != '') { ?>
<div align="center" class="success <?php  echo $class;  ?> alert-dismissable col-xs-10 col-sm-9 col-lg-3 box-center"><i class="fa <?php  echo $class_icon;  ?>"></i>  <?php echo $msg; ?></div>
<?php }  ?>	
<div class="box box-primary order_list" style="padding-left:0px;margin-bottom:15px;">
		<!-- Start New Orders List -->						
			<?php if(isset($OrderList) && !empty($OrderList)) {	?>
						<div class="col-xs-6 col-sm-1" style="width:auto">
								<img class="img_border" height="75" width="75" src="<?php echo $OrderList['Photo']?>" alt=""/>
						</div>					
						<div class="col-xs-6 col-sm-7 no-padding">
							<?php echo ucfirst($OrderList['FirstName'])."&nbsp;".ucfirst($OrderList['LastName']); ?>	<br>
							<span class="help-block no-margin"><?php echo $OrderList['Email']; ?></span>
							<span class="help-block no-margin"><?php echo $OrderList['UniqueId']; ?></span>
							<span class="help-block no-margin"><?php echo time_ago($OrderList['OrderDate']); ?></span>
						</div>
						<div class="clear"><br><hr class="no-margin"></div>
						<?php if (!empty($OrderList['TransactionId']) && $OrderList['RefundStatus'] == 1 && $show == 0){ ?>
							<!--<div class="margintb20" align="right" style="padding-right:5px;" id="refund_msg_but">
								<a class="btn btn-success" title="Refund Order" onclick="return refundBox();">
									<i class="fa fa-reply"></i> Refund Order
								</a>
							</div>		
							-->				
							<div class="col-xs-12 table-responsive no-padding list_height no-margin" id="refund_msg_box" style="display:block;">	
								<form method="post" action="OrderProductDetail?cs=1&OrderID=<?php echo $OrderList['OrderId']; ?>" id="RefundForm" name="RefundForm">
									<table class="table">
										<tr>
											<td width="2%">&nbsp;</td>
											<td align="right"><label>Message</label></td>
											<td width="2%">&nbsp;</td>
											<td width="50%"><textarea class="form-control" name="refund_msg" id="refund_msg" rows="5" cols="400"></textarea></td>
											<td width="2%">&nbsp;</td>
											<td align="right">
												<a class="btn btn-success margin-bottom" title="Refund Order" href="#" onclick="return refundSubmit(1,'1');">
													<i class="fa  fa-reply"></i> Refund Order
												</a>
												<input type="submit" value="submit" style="display:none;">
											</td>
										</tr>
									</table>	
								</form>
							</div>
						<?php } ?>
						<hr class="no-margin">				
						<div class="col-xs-12 table-responsive no-padding list_height no-margin">
						<table class="table  no-margin">
                              <tr>
								<th align="center" width="5%" class="text-center">#</th>				
								<th width="30%">Item Name</th>					
								<th width="15%">Item Photo</th>
								<th width="25%">Price Details</th>	
								<th width="5%" class="text-center">Quantity</th>	
								<th width="15%" class="text-right">Total Amount</th>												
								<!-- <?php //if ($_SERVER['HTTP_HOST'] == '172.21.4.104' && !empty($OrderList['TransactionId'])){ ?><th width="5%"></th><?php// } ?> -->
							</tr>
							<?php if(!empty($OrderList['Products'])) {											
									foreach($OrderList['Products'] as $key1=>$pro_val) {
									//echo "<pre>"; echo print_r($pro_val); echo "</pre>";
									?>
									<tr>
										<td class="text-center"><?php echo $key1+1;?></td>	
										<td>
											<div class="">
												<?php if(isset($pro_val["ItemName"]) && $pro_val["ItemName"] != ''){ ?>
													<span title="Item Name">
														&nbsp;<?php echo $pro_val["ItemName"];  ?>
													</span>
												<?php }?>
											</div></td>												
										<td>
											<?
												$image_path = '';
												$photo = $pro_val["Photo"];
												$image_path = SITE_PATH.'/Refer/site_source/no_photo_product1.png';
												if(isset($photo) && $photo != ''){
													$image_path = $photo;
												}
											?>
											<div class="col-sm-3 col-xs-4 no-padding">
											 	<img width="75" height="75" align="top" class="img_border" src="<?php echo $image_path;?>" >
											</div>
										</td>
										<td>	
											<div class="col-xs-12  no-padding"> 
												<?php	
													if(isset($pro_val["ProductsCost"]) && $pro_val["ProductsCost"] > 0)
													 	echo 'Price : '.price_fomat($pro_val["ProductsCost"]);
													$discountedAmount	=	$pro_val["ProductsCost"] - $pro_val["DiscountPrice"];
													if($discountedAmount > 0) {
														echo '</br>Discount Price : '.price_fomat($pro_val["ProductsCost"] - $pro_val["DiscountPrice"]); 
														echo '</br><strong>Amount</strong> : '.price_fomat($pro_val["DiscountPrice"]); 
													}
													else
														echo '</br><strong>Amount</strong> : '.price_fomat($pro_val["ProductsCost"]); 
												?>
											</div>						
										</td>
										<td align="center">	
											<div class="col-xs-12  no-padding"> 
												<?php if(isset($pro_val["ProductsQuantity"]) && $pro_val["ProductsQuantity"] > 0){ echo $pro_val["ProductsQuantity"]; } ?>
											</div>						
										</td>
										<td class="text-right"><?php echo price_fomat($pro_val['TotalPrice']); ?></td>
										<!-- <?php //if ($_SERVER['HTTP_HOST'] == '172.21.4.104' && !empty($OrderList['TransactionId'])){ ?>
										<td>
											<form method="post" action="OrderProductDetail?cs=1&OrderID=<?php //echo $OrderList['OrderId']; ?>&ProductId=<?php //echo $pro_val['ProductID']; ?>" id="RefundProduct<?php //echo $pro_val['ProductID']; ?>" name="RefundProduct<?php //echo $pro_val['ProductID']; ?>">
												<a class="btn btn-success margin-bottom"  data-toggle="tooltip" data-original-title="Refund &nbsp;<?php //echo $pro_val["ItemName"];  ?>" title="Refund &nbsp;<?php //echo $pro_val["ItemName"];  ?>" href="#" onclick="return refundSubmit(2,<?php //echo $pro_val['ProductID']; ?>);">
													<i class="fa  fa-reply"></i> 
												</a>
											</form>
										</td>
										<?php// } ?> -->
									</tr>
							<?php } }  ?>
								<tr>
								 	<td colspan="5">
											<div class="col-xs-12 no-padding text-right"><strong>Sub Total</strong></div>
											<div class="col-xs-12 no-padding text-right">VAT </div>
											<div class="col-xs-12 no-padding text-right"><strong>Total</strong></div>
									</td>
									<td class="text-right">
									<?php 
										echo "<strong>".price_fomat($OrderList['SubTotal'])."</strong>";
										echo "<br>".price_fomat($OrderList['VAT']); 
										echo "<br><strong>".price_fomat($OrderList['TotalPrice'])."</strong>"; 
									?>
									</td>
								</tr>								
						</table>
						</div>
			<?php }  else { ?>
				<div class="alert alert-danger alert-dismissable col-xs-10 col-sm-5 col-lg-3 box-center" align="center"><i class="fa fa-warning"></i>&nbsp;&nbsp;No products found.</div>
			<?php } ?>
		<!-- End New Orders List -->						
	</div><!-- /.box-body -->
</div>
</div>					
<?php commonFooter(); ?>
</body>
</html>
