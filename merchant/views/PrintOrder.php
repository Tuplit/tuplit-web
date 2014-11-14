<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();
if(isset($_SESSION['merchantInfo']['AccessToken'])){ 	
	if(isset($_GET['printId']) && !empty($_GET['printId'])) {
		$PrintId				=	$_GET['printId'];
	//getting Order List
		$url					=	WEB_SERVICE.'v1/orders/'.$PrintId.'';
		$curlCategoryResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
		if(isset($curlCategoryResponse) && is_array($curlCategoryResponse) && $curlCategoryResponse['meta']['code'] == 201 && is_array($curlCategoryResponse['OrderDetails']) ) {
			if(isset($curlCategoryResponse['OrderDetails']))
				$OrderList = $curlCategoryResponse['OrderDetails'];
				//echo "<pre>";print_r($OrderList);echo "</pre>";
		} 
	}
}
if(isset($errorMessage) && $errorMessage != ''){
	$msg			=	$errorMessage;
	$display 		= 	"block";
	$class   		= 	"alert-danger";
	$class_icon 	= 	"fa-warning";
	$errorMessage 	= 	'';
}else if(isset($successMessage) && $successMessage != ''){
	$msg			=	$successMessage;
	$display		=	"block";
	$class 			= 	"alert-success";
	$class_icon 	= 	"fa-check";
	$successMessage = 	'';	
}
commonHead();
?>
<form action="" name="your_order_form" id=""  method="post" >
				<div class="popup" id="firstDiv">
					<div class="order_border" style="border:1px solid #ccc">
					<div class="form-group col-xs-12 no-padding" id="secondDiv" >
						<!-- <label class="col-xs-12">Category Name</label> -->
						<div class="col-xs-12 popup_title text-center"><h3>Your Order</h3></div>
						<div class="col-xs-12" style="text-align:center;">
								Order Placed on <?php 
								if(isset($OrderList) && !empty($OrderList)){
									if(isset($OrderList['OrderDate']) && $OrderList['OrderDate'] != '0000-00-00 00:00:00'){
										$gmt_current_start_time = convertIntocheckinGmtSite($OrderList['OrderDate']);
										$order_time	=  displayConversationDateTime($gmt_current_start_time,$_SESSION['tuplit_ses_from_timeZone']);
										echo $order_time; 
									}else echo ' ';
								} 
							?>
						</div>
						<div class="col-xs-12">&nbsp;</div>
						<div class="col-xs-12">
						<?php if(isset($OrderList) && !empty($OrderList)){?>
							<strong class="col-xs-4 no-padding HelveticaNeueBold">Order Number:</strong>
							<span>
								<?php echo (!empty($OrderList['TransactionId'])?$OrderList['TransactionId']:'&nbsp;');?>
							</span>
							<div class="clearfix">
								<strong class="col-xs-4 no-padding HelveticaNeueBold">User Name:</strong>
								<span>
									<?php
										echo (!empty($OrderList['FirstName'])?$OrderList['FirstName']:'').' ';
										echo (!empty($OrderList['LastName'])?$OrderList['LastName']:'');?>
								</span>
							</div>
							<div class="clearfix">
								<strong class="col-xs-4 no-padding HelveticaNeueBold">User ID:</strong>
								<span>
									<?php echo (!empty($OrderList['UniqueId'])?$OrderList['UniqueId']:'');} ?>
								</span>
							</div>
						</div>
						<div class="col-xs-12 no-padding" style="border-bottom:1.5px dotted #DFDFDF;">&nbsp;</div>
					</div>
					<div class="col-xs-12 no-padding" style="box-shadow:none;border-bottom:0px solid #fff;">
					<div class="col-xs-12 no-padding"  style="padding-top:15px;">
						<!-- <div class="clear"><br></div> -->
						<div class="col-xs-12 no-padding list_height no-margin" >
							<div class="col-xs-12 clear" style="margin-bottom:8px;">
                       			<?php if(!empty($OrderList['Products'])) {											
									foreach($OrderList['Products'] as $key1=>$pro_val) {
									?>
									<div class="col-xs-9 no-padding" style="margin-bottom:8px;">
									<span class="pull-left"><?php if(isset($pro_val["ProductsQuantity"]) && $pro_val["ProductsQuantity"] > 0){ echo $pro_val["ProductsQuantity"]; } ?>x&nbsp;&nbsp;</span>
										<span title="Item Name" class="pull-left">
											<?php echo $pro_val["ItemName"];  ?>
										</span>
									</div>
									<div class="col-xs-3 text-right no-padding" style="margin-bottom:8px;">
										<strong class="HelveticaNeueBold"><?php echo price_fomat($pro_val['TotalPrice']); ?></strong>
									</div>
									<!-- <div class="clear "><br></div> -->	
									<?php } ?>
								</div>
								<div class="col-xs-12" style="border-top:1px dotted #dbdbdb;padding:15px;">
									 	<div class="col-xs-9 no-padding">
											<strong class="HelveticaNeueBold">Sub Total</strong>
										</div>
										<div class="col-xs-3 text-right no-padding">
											<strong class="HelveticaNeueBold"><?php echo price_fomat($OrderList['SubTotal']); ?></strong>
										</div>
										<div class="col-xs-9 no-padding margin-bottom">
											<span class="vat">VAT</span>
										</div>
										<div class="col-xs-3 text-right no-padding margin-bottom">
											<span class="vat text-right"><?php echo price_fomat($OrderList['VAT']); ?></strong>
										</div>
										<div class="col-xs-9 no-padding">
											<strong class="HelveticaNeueBold">Total</strong>
										</div>
										<div class="col-xs-3 text-right no-padding">
											<strong class="HelveticaNeueBold"><?php echo price_fomat($OrderList['TotalPrice']); ?></strong>
										</div>
								</div>
						<?php } ?>
				</div><!-- /.box-body -->
			</div>
		</div><!-- /row -->		
		</div>
	</form>

<!-- print order new end -->

