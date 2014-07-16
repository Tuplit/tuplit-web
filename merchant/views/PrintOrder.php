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
<body onload="window.print()"><!--  "  -->
		<div class="col-xs-12 no-padding">	 
				<section class="content-header">
	                <h1 class="no-margin space_bottom">Print Order</h1>
	            </section>
				<div class="box order_list" style="padding-left:0px">
					<div class="space_top" >
						<!-- Start New Orders List -->						
							<?php if(isset($OrderList) && !empty($OrderList)) {							
							?>
								<div class="col-md-3 col-sm-12 col-lg-2 col-xs-12 no-padding"  style="padding-top:15px;>
									<div class="">									
										<div class="col-xs-2 col-sm-2 col-md-2 col-lg-2 space_top">
												<img height="75" width="75" src="<?php echo $OrderList['Photo']?>" alt=""/>
										</div>					
										<div class="col-xs-5 col-sm-2 col-md-2 col-lg-2 space_top">
										<?php echo $OrderList['FirstName']."&nbsp;".$OrderList['LastName']; ?>	<br>
										<span class="help-block no-margin"><?php echo $OrderList['UniqueId']; ?></span>
										<span class="help-block no-margin"><?php echo $OrderList['Email']; ?></span>
										<span class="help-block no-margin"><?php echo time_ago($OrderList['OrderDate']); ?></span>
										</div>
										<div class="col-xs-12 table-responsive no-padding list_height no-margin">
										<table class="table table-hover" style="align:center" >
			                               <tr>
												<th align="center" width="5%" style="text-align:center">#</th>				
												<th width="20%" style="text-align:center">Item Name</th>					
												<th width="20%">Item Photo</th>
												<th width="25%">Price Details</th>	
												<th width="10%" style="text-align:center">Quantity</th>	
												<th width="25%"  style="text-align:center">Total Amount</th>		
											</tr>
											<?php if(!empty($OrderList['Products'])) {											
													foreach($OrderList['Products'] as $key1=>$pro_val) {
													?>
													<tr>
														<td align="center"><?php echo $key1+1;?></td>	
														<td align="center">
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
																	{ 
																	 	echo 'Price : $'.$pro_val["ProductsCost"];
																	} 
																	$discountedAmount	=	$pro_val["ProductsCost"] - $pro_val["DiscountPrice"];
																	/*echo '</br><b>Total Price</b> : $'.($pro_val["ProductsCost"]*$pro_val["ProductsQuantity"]); */
																	if($discountedAmount > 0)
																	{
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
														<td><div class="col-xs-8 no-padding text-right"><?php echo price_fomat($pro_val['TotalPrice']); ?></div></td>
													</tr>
											<?php } } 
												?>
												<tr>
												 	<td colspan="5"><div class="col-xs-8 no-padding"><strong>Total</strong> </div></td>
													<td><div class="col-xs-8 no-padding text-right"><strong><?php echo price_fomat($OrderList['TotalPrice']); ?></strong></div></td>
												</tr>
										</div>
										
										
											
												
									</div>
								</div> 				
							<?php }  else { ?>
								<div class="alert alert-danger alert-dismissable col-xs-10 col-sm-5 col-lg-3" align="center"><i class="fa fa-warning"></i>&nbsp;&nbsp;No products found.</div>
							<?php } ?>
						<!-- End New Orders List -->						
					</div><!-- /.box-body -->
				</div>					
		 </div>
		 
		 
	
</html>
