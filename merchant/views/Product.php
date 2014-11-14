<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();
$hide 		= 	0;
$Photo 		= 	$PhotoContent = $ProductId = '';
$adddeals	=	$addspecial	=	$updatepro	=	$notifi_hide = 0;
$ItemType	=	$allowProducts	=	'1';
if(isset($_GET['add']) && !empty($_GET['add'])) {
	if($_GET['add'] == 'deals')
		$adddeals	=	1;
	if($_GET['add'] == 'specials') {
		$addspecial	=	1;
		$adddeals	=	1;
	}
}

//getting merchant details
if(isset($_SESSION['merchantDetailsInfo']) && is_array($_SESSION['merchantDetailsInfo'])) {
	$merchantInfo  		=	$_SESSION['merchantDetailsInfo'];
	if(!empty($merchantInfo['DiscountTier']) || $merchantInfo['DiscountTier'] != 0) {
	}
	else {
		$msg			=	'Please update your discount tier.';
		$display 		= 	"block";
		$class   		= 	"alert-danger";
		$class_icon 	= 	"fa-warning";
		$errorMessage 	= 	'';
		$hide			= 	1;	
	}
}

//Delete Product
if(isset($_GET['delete']) && !empty($_GET['delete'])) {
	$delType		=	1;
	$successMessage = '';
	if(isset($_GET['Type']) && !empty($_GET['Type']))
		$delType		=	 $_GET['Type'];

	$url					=	WEB_SERVICE.'v1/products/'.$_GET['delete'].'?Type='.$delType;
	$curlDeleteResponse 	= 	curlRequest($url, 'DELETE', null,$_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlDeleteResponse) && is_array($curlDeleteResponse) && $curlDeleteResponse['meta']['code'] == 201) {
		$successMessage 	= 	"Product Deleted successfully";
		$notifi_hide = 1;
	}
	if(isset($_GET['from']) && !empty($_GET['from']) && $_GET['from']) {
		if(!empty($successMessage))
			echo "1";
		else
			echo "0";
		die();
	}
}

	//getting product categories
	$url					=	WEB_SERVICE.'v1/categories/products';
	$curlCategoryResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlCategoryResponse) && is_array($curlCategoryResponse) && $curlCategoryResponse['meta']['code'] == 201 && is_array($curlCategoryResponse['productCategoryDetails']) ) {
		if(isset($curlCategoryResponse['productCategoryDetails']))
			$productCategories = $curlCategoryResponse['productCategoryDetails'];	
	} 

	//Product List
	$merchantId				= 	$_SESSION['merchantInfo']['MerchantId'];
	$url					=	WEB_SERVICE.'v1/products/?Type=1';
	$curlMerchantResponse  	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201) {
		$ProductsArray   	=	$curlMerchantResponse['ProductList'];		
	}

	if(isset($_GET['add']) && !empty($_GET['add']))
		$Category 			= 	$_GET['add'];

	//getting product detail
	if(isset($_GET['edit']) && !empty($_GET['edit'])) {	
		$url							=	WEB_SERVICE.'v1/products/'.$_GET['edit'];
		$curlCategoryResponse 			= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
		if(isset($curlCategoryResponse) && is_array($curlCategoryResponse) && $curlCategoryResponse['meta']['code'] == 201 && is_array($curlCategoryResponse['ProductDetail']) ) {
			if(isset($curlCategoryResponse['ProductDetail'])) {
				$ProductDetail 			= 	$curlCategoryResponse['ProductDetail'];
				$ProductDetail			=	$ProductDetail[0];
				if(isset($ProductDetail) && count($ProductDetail) > 0) {
					$ProductId			= 	$ProductDetail['id'];
					$Photo 				= 	$ProductDetail['Photo'];
					$Category 			= 	$ProductDetail['fkCategoryId'];
					$ItemName 			= 	$ProductDetail['ItemName'];
					$ItemDescription	= 	$ProductDetail['ItemDescription'];
					$Price 				= 	$ProductDetail['Price'];	
					$Status 			= 	$ProductDetail['Status'];
					$Discount 			= 	$ProductDetail['DiscountApplied'];
					$updatepro			=	1;			
					if($ProductDetail['DiscountApplied'] == 1) {
						$DiscountPrice	= 	$ProductDetail['Price'] - (($merchantInfo['DiscountTier']/100) * $ProductDetail['Price']);
						floatval($DiscountPrice); 
					}
					else
						$DiscountPrice 	= 	$ProductDetail['Price'];
					if($ProductDetail['ItemType'] == 3 && !empty($ProductDetail['SpecialProducts'])) {
						$editSpecialProduct			=	$ProductDetail['SpecialProducts'];
						$TotaleditSpecialProduct	=	count($editSpecialProduct);
						$RowSpecialIds				=	'';
						$TotalSpecialAmount			=	0;
						foreach($editSpecialProduct as $spkey=>$sppro) {
							//Forming total rowids
							$pt					=	$spkey + 1;
							if(empty($RowSpecialIds))
								$RowSpecialIds	=	$pt;
							else
								$RowSpecialIds	=	$RowSpecialIds.','.$pt;
							
							//calculating total price
							if(!empty($sppro['Quantity']) && !empty($sppro['Price']))
								$TotalSpecialAmount	=	$TotalSpecialAmount	+ ($sppro['Quantity']*$sppro['Price']);
						}
					}				
				}
			}		
		}	
	}

	//Adding and updating Products
	//if((isset($_POST['merchant_product_submit']) && $_POST['merchant_product_submit'] == 'Save') || (isset($_POST['merchant_product_update']) && $_POST['merchant_product_update'] == 'Update')){	
	if(isset($_POST) && !empty($_POST)){		
		if(isset($_POST['product_photo_upload']) && !empty($_POST['product_photo_upload'])) {
			$Photo 					= 	TEMP_IMAGE_PATH.$_POST['product_photo_upload'];
			$PhotoContent			= 	$_POST['product_photo_upload'];
		}	
		$Category 					= 	$_POST['Category'];
		$ItemName 					= 	$_POST['ItemName'];
		$Price 						= 	$_POST['Price'];
		$Status 					= 	$_POST['Status'];
		if(isset($_POST['Discount']))
			$Discount 				= 	1;
		else
			$Discount 				= 	0;
		$DiscountPrice 				= 	$_POST['DiscountPrice'];
		$ItemDescription 			= 	'';
		$SpecialIds					= 	'';
		$SpecialQty					= 	'';
		$original_Price				=	'';
		
		if($_POST['productType'] == 2) {
			$ItemType				= 	'2';
			$Category 				= 	'0';
			$Discount 				= 	'0';
			$ItemDescription		= $_POST['ItemDescription'];
		}
		if($_POST['productType'] == 3) {
			$ItemType				= 	'3';
			$Category 				= 	'0';
			$Discount 				= 	'0';
			$original_Price			= 	$_POST['TotalPrice'];
			$rowids					= 	explode(',',$_POST['TotalRowIds']);
			foreach($rowids as $val) {			
				if(empty($SpecialIds)) {
					$SpecialIds		=	$_POST['Products'.$val];
					$SpecialQty		=	$_POST['quantity'.$val];
				}
				else {
					$SpecialIds		=	$SpecialIds.",".$_POST['Products'.$val];
					$SpecialQty		=	$SpecialQty.",".$_POST['quantity'.$val];
				}
			}
		}
		$data	=	array(
					'ProductId'				=> 	$ProductId,
					'SpecialIds'			=> 	$SpecialIds,
					'SpecialQty'			=> 	$SpecialQty,
					'Photo' 				=> 	$PhotoContent,
					'CategoryId'			=> 	$Category,
					'ItemName' 				=> 	$ItemName,	
					'ItemDescription'		=> 	$ItemDescription,	
					'Price' 				=> 	$Price,	
					'OriginalPrice'			=> 	$original_Price,	
					'Status' 				=> 	$Status,
					'Discount' 				=> 	$Discount,
					'ItemType' 				=> 	$ItemType,
					'ImageAlreadyExists' 	=> 	$_POST['empty_product_photo'],
				);
				
		//checking merchant discount status
		if($updatepro == 0)
			$url				=	WEB_SERVICE.'v1/merchants/discount/';
		else
			$url				=	WEB_SERVICE.'v1/merchants/discount/?Type=1&Discount='.$Discount;
		$curlResponse 			= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
		//echo "<pre>"; echo print_r($curlResponse); echo "</pre>";
		if(isset($curlResponse) && is_array($curlResponse) && $curlResponse['meta']['code'] == 201) {
			if(isset($curlResponse['ProductCounts']['Discounted']) && $curlResponse['ProductCounts']['Discounted'] == 1) {
				if(isset($curlResponse['ProductCounts']['ProductDifference']) && $curlResponse['ProductCounts']['ProductDifference'] >= 1) {				
					$allowProducts		=	1;
				}
				else if(isset($curlResponse['ProductCounts']['ProductDifference']) && $curlResponse['ProductCounts']['ProductDifference'] == 0) {						
					if($curlResponse['ProductCounts']['ProductPlusDiscount'] == $curlResponse['ProductCounts']['TotalDiscountApplied'])
						$allowProducts	=	1;
					else if(($Discount == 1 || $ItemType == 2 ||  $ItemType == 3) && $updatepro == 0)
						$allowProducts	=	1;
					else {
						$allowProducts  = 	0;
						if(isset($updatepro) && $updatepro	==	1)
							$errorMessage	=	"Please update an item with discount";
						else
							$errorMessage	=	"This product must have discount or add discount to existing product";
					}
				}					
			}
		} 
		else if(isset($curlResponse['meta']['errorMessage']) && $curlResponse['meta']['errorMessage'] != '' && $curlResponse['meta']['code'] == '2000') {
			$allowProducts		=	1;
		} else if(isset($curlResponse['meta']['errorMessage']) && $curlResponse['meta']['errorMessage'] != '' && $curlResponse['meta']['code'] == '2223') {
			if($Discount == 1 || $ItemType == 2 ||  $ItemType == 3)
				$allowProducts	=	1;
			else {
				$allowProducts	=	0;
				$errorMessage	=	$curlResponse['meta']['errorMessage'];
			}
		} else if(isset($curlResponse['meta']['errorMessage']) && $curlResponse['meta']['errorMessage'] != '') {
			$allowProducts		=	0;
			$errorMessage		=	$curlResponse['meta']['errorMessage'];
		} else {
			$allowProducts		=	0;
			$errorMessage 		= 	"Bad Request";
		}
		
		if($allowProducts == 1) {
			if(isset($_GET['edit']) && !empty($_GET['edit'])) {
				$method			=	'PUT';
				$url			=	WEB_SERVICE.'v1/products/'.$ProductId;
				//echo json_encode($data); die();
				$curlResponse	=	curlRequest($url,$method,json_encode($data),$_SESSION['merchantInfo']['AccessToken']);	
			}
			else {
				$method			=	'POST';	
				$url			=	WEB_SERVICE.'v1/products/';
				$curlResponse	=	curlRequest($url,$method,$data, $_SESSION['merchantInfo']['AccessToken']);		
			}
			
			if(isset($curlResponse) && is_array($curlResponse) && $curlResponse['meta']['code'] == 201) {
				$successMessage = 	$curlResponse['notifications'][0];
				unset($_POST);
				$notifi_hide = 1;
			} else if(isset($curlResponse['meta']['errorMessage']) && $curlResponse['meta']['errorMessage'] != '') {
				$errorMessage	=	$curlResponse['meta']['errorMessage'];
			} else {
				$errorMessage 	= 	"Bad Request";
			}
		}
	}	
	
	//getting product counts details
	$allowPro	=	1;	
	$url						=	WEB_SERVICE.'v1/merchants/discount/';
	$curlMerchantResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201 && isset($curlMerchantResponse['ProductCounts'])) 
	{
		if(isset($curlMerchantResponse['ProductCounts']['ProductDifference']) && $curlMerchantResponse['ProductCounts']['ProductDifference'] >= 1) {				
			$allowPro	=	1;
		}
		else if(isset($curlMerchantResponse['ProductCounts']['ProductDifference']) && $curlMerchantResponse['ProductCounts']['ProductDifference'] == 0) {						
			if($curlMerchantResponse['ProductCounts']['ProductPlusDiscount'] == $curlMerchantResponse['ProductCounts']['TotalDiscountApplied'])
				$allowPro	=	1;			
			else 
				$allowPro	=	0;
		}					
	}
	$class = '';
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
		$hide			=  1;
	}
//$goDiscounted = 0;
popup_head();
?>
<body class="skin-blue fixed popup_bg" onload="fieldfocus('ItemName');"  onclick="return updatePin();">
<div class="popup_white <?php if($allowPro == 0){ ?>discount_alert<?php } ?>">
				
		<?php if(isset($_GET['show']) && $_GET['show'] != 0) 	top_header();  if($allowPro == 0) { ?>
			<div class="item_discount" <?php if($notifi_hide == 1) echo 'style="display:none;"'; ?> ><span class="comment_txt"> <em class="discount_alert_icon"></em> Your business isn't going to be published unless 1/3 off added items to be discounted</span></div>				
		<?php } if(isset($msg) && $msg != '') { 	?>
		<div class="upadate_sucess_msg">
			<div align="center" class="alert <?php  echo $class;  ?> alert-dismissable col-xs-10 box-center">
				<i class="fa <?php  echo $class_icon;  ?>"></i>  <?php echo $msg; ?>
			</div>
		</div>
		<?php } if(isset($hide) & $hide == 1) {  } else { ?>			
			<form action="" name="<?php if(isset($_GET['edit']) && !empty($_GET['edit'])) echo "edit"; else echo "add"; ?>_product_form" id="<?php if(isset($_GET['edit']) && !empty($_GET['edit'])) echo "edit"; else echo "add"; ?>_product_form"  method="post" <?php if($addspecial != 0) echo 'onsubmit="return validateSpecialdata()"'; ?> style="margin-bottom:0">
				<div class="popup">
					<div class="form-group col-xs-12 no-padding" style="min-height:85px;">	
						<div class="col-xs-12 popup_title text-center"><h1><?php if(isset($_GET['edit']) && !empty($_GET['edit'])) echo "Edit"; else echo "Add"; ?> <?php if($addspecial == 0) echo "item"; else echo "Special"; ?></h1></div>
						<div class="col-xs-12 col-sm-12">
							<div class="upload_img" id="product_photo_img">
								<span class="upload_info" <?php if(isset($Photo) && !empty($Photo)) echo 'style="display:none"'; ?>><?php if($addspecial == 0) echo 'Tap here to upload image'; else echo 'Tap here to upload a file';?></span>
								<input type="file"  name="product_photo" id="product_photo" />
								<img  id="product_photo_image_upload" width="165" height="140" <?php if(isset($Photo) && !empty($Photo)) echo 'src="'.$Photo.'"'; else echo ' style="display:none"'; ?>>
								<input type="hidden" name="product_photo_upload" id="product_photo_upload" value="<?php  if(isset($_POST['product_photo_upload']) && $_POST['product_photo_upload'] != '') echo $_POST['product_photo_upload']; else if(isset($PhotoContent) && !empty($PhotoContent)) echo $PhotoContent; ?>" />
							</div>							
							<p class="help-block clear txt_center">Please upload only JPG or PNG files.<br/> The best resolution is 300X300 pixels.</p>						
							<span class="error col-xs-10 col-xs-offset-1 no-padding" for="empty_product_photo" generated="true" style="display: none;text-align:center">Product Image is required</span>										
							<input type="Hidden" name="empty_product_photo" id="empty_product_photo" value="<?php  if(isset($Photo) && !empty($Photo)) echo "1";  ?>" />
							<input type="Hidden" name="name_product_photo" id="name_product_photo" value="<?php echo $PhotoContent; ?>" />
							<input type="hidden" name="old_product_photo" id="old_product_photo" value="<?php  if(isset($PhotoContent) && $PhotoContent != '') { echo $PhotoContent; }  ?>" />
						</div>				
					</div>
					<div class="form-group col-xs-6 error_5msg_hgt" <?php if($adddeals == 1) echo 'style="display:none;"'; ?>>
						<div class="col-xs-12">
							<select class="form-control selectpicker " name="Category">								
								<?php if($adddeals == 1) { ?>
								<option value="deals" selected>Category</option>
								<?php } else {?>
									<option value="" >Category</option>
									<?php if(isset($productCategories) && !empty($productCategories)) {
										foreach($productCategories as $key=>$val) {								
									?>
									<option value="<?php echo $val['CategoryId'];?>" <?php if(isset($Category) && $Category == $val['CategoryId']) echo "selected";?>><?php echo ucfirst($val['CategoryName']);?></option>
								<?php } } } ?>								
							</select>
						</div>
						<p class="help-block clear text-left col-xs-12">Item can be re-arranged into other category anytime</p>
					</div>
					<div class="form-group error_5msg_hgt <?php if($adddeals == 1) echo 'col-xs-12'; else echo 'col-xs-6'; ?>">
						<div class="col-sm-12">
							<input class="form-control" type="text" maxlength="30" name="ItemName" id="ItemName" placeholder="Item Name" value="<?php if(isset($ItemName)) echo $ItemName; ?>" />
						</div>
						<p class="help-block clear col-sm-12">Max. 30 characters</p>
					</div>
					<!-- <div class="form-group col-xs-12 error_5msg_hgt ">							
						<div class="col-xs-10 col-sm-10 col-xs-offset-1 col-sm-offset-1 no-padding">
							<select class="form-control selectpicker "><option>Applicable Tax</option></select>
						</div>
						<p class="help-block text-center clear">Item can be re-arrange into other category anytime</p>
					</div> -->
					<div class="form-group col-xs-12 " style="<?php if($adddeals == 0 || $addspecial == 1) echo "display:none;"; ?>">							
						
						<div class="col-xs-10 col-sm-10  col-xs-offset-1 col-sm-offset-1 no-padding">
							<textarea class="form-control" placeholder="Short Description" name="ItemDescription" id="ItemDescription" rows="2"><?php if(isset($ItemDescription)) echo $ItemDescription; ?></textarea>
						</div>
					</div>
					<div class="form-group col-xs-12 no-margin border_top" style="<?php if($addspecial == 0) echo "display:none;"; ?>margin-bottom:15px">							
						<h4 class="col-xs-12">Special Products</h4>
						<div class="col-xs-12">
							<div class="specialProducts" id="specialProducts" name="specialProducts">								
								<div class="row" id="copydiv" style="display:none;">
									<div class="col-xs-5">
										<select class="form-control selectpicker" name="Products" id="Products" onchange="return calculateSpecialPrice('1',this);">							
											<option value="">Select</option>
											<?php foreach($ProductsArray as $s_key=>$s_value){ 
													if(!empty($s_value['ProductId'])) { ?>
														<option value="<?php echo $s_value['ProductId']; ?>"><?php echo $s_value['ItemName']; ?></option>
											<?php } } ?>
										</select>
									</div>
									<div class="col-xs-2 no-padding pad-product">
										<input type="text"  class="form-control" name="quantity" id="quantity" onkeypress="return isNumberKey(event);" maxlength="3" value="" onkeyup="return calculateSpecialPrice('2',this);">
									</div>
									<div class="col-xs-3">
										<input type="text"  class="form-control" name="quantityTotalPrice" id="quantityTotalPrice" onkeypress="return isNumberKey(event);" value="" onkeyup="" readonly>				
									</div>
									<div class="col-xs-2  text-right no-padding" style="padding-top:5px !important;margin:9px 0">
										<i class="fa fa-plus fa-plus-bgcolor" id="plus" name="plus" onclick="return newSpecialRow(1,this,'');"></i>
										&nbsp;&nbsp;<i class="fa fa-minus fa-minus-bgcolor" id="minus" name="minus" onclick="return newSpecialRow(2,this,'');"></i>
									</div>
								</div>
								
								<div class="row title_row">
									<div class="col-xs-5 Product"><strong>Product</strong></div>
									<div class="col-xs-2 Quantity no-padding"><strong>Quantity</strong></div>
									<div class="col-xs-3 Price"><strong>Price</strong></div>
									<div class="col-xs-2"></div>
								</div>
								
								<?php if(isset($editSpecialProduct) && !empty($editSpecialProduct) && count($editSpecialProduct)>0) {
										$editspecialcount = count($editSpecialProduct);
									foreach($editSpecialProduct as $spkey=>$spval) { $sprow	=	$spkey + 1;
								?>
								<div class="row" id='Row_<?php echo $sprow;?>'>
										<div class="col-xs-5 Product_val">
										<select class="form-control" name="Products<?php echo $sprow;?>" id="Products<?php echo $sprow;?>" onchange="return calculateSpecialPrice('1',this);">							
											<option value="">Select</option>
											<?php 
													foreach($ProductsArray as $s_key=>$s_value){ if(!empty($s_value['ProductId'])) { ?>
														<option value="<?php echo $s_value['ProductId']; ?>" <?php if($spval['fkProductsId'] == $s_value['ProductId']) echo "selected"; ?>><?php echo $s_value['ItemName']; ?></option>
											<?php } } ?>
										</select>
										</div>
										<div class="col-xs-2  no-padding pad-product">
											<input type="text"  class="form-control" name="quantity<?php echo $sprow;?>" id="quantity<?php echo $sprow;?>" onkeypress="return isNumberKey(event);" maxlength="3" value="<?php if(!empty($spval['Quantity']))  echo $spval['Quantity']; ?>" onkeyup="return calculateSpecialPrice('2',this);">
										</div>
										<div class="col-xs-3 ">
										<input type="text"  class="form-control " name="quantityTotalPrice<?php echo $sprow;?>" id="quantityTotalPrice<?php echo $sprow;?>" onkeypress="return isNumberKey(event);" value="<?php if(!empty($spval['Quantity']) && !empty($spval['Price']))  echo ($spval['Quantity']*$spval['Price']); ?>" onkeyup="" readonly>
										</div>
										<div class="col-xs-2 text-right no-padding" style="padding-top:5px !important">
											<i class="fa fa-plus fa-plus-bgcolor" id="plus<?php echo $sprow;?>" name="plus<?php echo $sprow;?>" onclick="return newSpecialRow(1,this,<?php echo $sprow; ?>);" style="<?php if($TotaleditSpecialProduct != $sprow) echo "display:none"; ?>"></i>
											<i class="fa fa-minus fa-minus-bgcolor" id="minus<?php echo $sprow;?>" name="minus<?php echo $sprow;?>" onclick="return newSpecialRow(2,this,'');" <?php if($editspecialcount == $sprow) echo 'style="display:none;"'; ?>></i>
											
										</div>
										<!-- <div class="col-xs-12 pad"></div> -->
								</div>								
								<?php } } else { ?>
								<div class="row" id='Row_1'>
										<div class="col-xs-5 Product_val">
											<select class="form-control" name="Products1" id="Products1" onchange="return calculateSpecialPrice('1',this);">							
												<option value="">Select</option>
												<?php if(isset($ProductsArray) && count($ProductsArray) > 0)  { 
														foreach($ProductsArray as $s_key=>$s_value){ if(!empty($s_value['ProductId'])) { ?>
															<option value="<?php echo $s_value['ProductId']; ?>" ><?php echo $s_value['ItemName']; ?></option>
												<?php } } } ?>
											</select>
										</div>
										
										<div class="col-xs-2 no-padding Quantity_val">
										<input type="text"  class="form-control " name="quantity1" id="quantity1" onkeypress="return isNumberKey(event);" maxlength="3" value="" onkeyup="return calculateSpecialPrice('2',this);">
										</div>
										
										<div class="col-xs-3 Price_val">
										<input type="text"  class="form-control " name="quantityTotalPrice1" id="quantityTotalPrice1" onkeypress="return isNumberKey(event);" value="" onkeyup="" readonly>
										</div>
										<div class="col-xs-2 no-padding text-right count_val" style="padding-top:5px !important;margin:9px 0">
											<!-- <i class="fa fa-plus  " id="plus1" name="plus1" onclick="return newSpecialRow(1,this);""></i>
											&nbsp;&nbsp;<i class="fa fa-minus fa-minus-bgcolor" id="minus1" name="minus1" onclick="return newSpecialRow(2,this);" style="color:#f56954;cursor:pointer;display:none;"></i> -->
											
											<i class="fa fa-plus fa-plus-bgcolor" id="plus1" name="plus1" onclick="return newSpecialRow(1,this,1);" ></i>
											<i class="fa fa-minus fa-minus-bgcolor"  id="minus1" name="minus1" onclick="return newSpecialRow(2,this,'');"  style="display:none;"></i>
										</div>	
										<!-- <div class="col-xs-12 pad"></div> -->
								</div>
								
								<?php } ?>									
							</div>
							<span id="specialerror" style="clear: both;color: red;display:none;font-size: 11px; font-weight: normal;"></span>
						</div>
						<?php if(isset($ProductsArray) && count($ProductsArray) > 0)  { foreach($ProductsArray as $s_key=>$s_value){ if(!empty($s_value['ProductId'])) { 
							 ?>
									<input type="hidden" name="price<?php echo $s_value['ProductId']; ?>" id="price<?php echo $s_value['ProductId']; ?>" value="<?php echo $s_value['Price']; ?>">
						<?php } } } ?>
						<input type="hidden" name="Totalrows" id="Totalrows" value="<?php if(isset($TotaleditSpecialProduct)) echo  $TotaleditSpecialProduct; else echo "1"; ?>" readonly>
						<input type="hidden" name="TotalRowIds" id="TotalRowIds" value="<?php if(isset($RowSpecialIds) && !empty($RowSpecialIds)) echo $RowSpecialIds; else echo "1"; ?>" readonly>						
						<input type="hidden" name="ProductIDs" id="ProductIDs" value="" readonly>						
					</div>
						
					<div class="clear">
					<div class="form-group col-xs-6 error_ms5g_hgt60 border_top border_bottom" style="<?php if($addspecial == 0) echo "display:none;"; ?>">							
							<label class="col-xs-6 LH45"><strong>Total Price</strong></label>
							<div class="col-xs-6 col-sm-12">
								<label class="col-xs-2 col-sm-2 LH45 padding-rht8"><?php echo "&pound;&nbsp;"?></label>
								<span class="col-xs-10 col-sm-10 no-padding">
									<input type="text"  class="form-control text-right" name="TotalPrice" id="TotalPrice" value="<?php if(isset($TotalSpecialAmount) && !empty($TotalSpecialAmount)) echo $TotalSpecialAmount; ?>" readonly>
								</span>	
							</div>
						</div>
						
						<div class="form-group col-xs-6 error_ms5g_hgt60 border_top border_bottom <?php if($addspecial == 0){ echo '';} ?>">							
						<label class="col-xs-6 error_ms5g_hgt60 LH45"><?php if($addspecial == 0) echo "Item"; else echo "Special" ?> Price</label>
						<div class="col-xs-6 col-sm-12 price_err">
							<label class="col-xs-2 col-sm-2 LH45 padding-rht8"><?php echo "&pound;&nbsp;"?></label>
							<span class="col-xs-10 col-sm-10 no-padding">
								<input type="text"  class="form-control text-right" name="Price" id="Price" onkeypress="return isNumberKey_price(event);" maxlength="10" value="<?php if(isset($Price)) echo $Price; ?>" onkeyup="return calculateDiscountPrice('0');">
							</span>	
						</div>
						<span id="specialpriceerror" class="col-xs-12" style="clear: both;color: red;display:none;font-size: 11px; font-weight: normal;margin-top:-5px">Special Price must be less then total price</span>
						
					</div>	
					<div class="form-group col-xs-6 border_top border_bottom" <?php if($adddeals == 1) echo 'style="display:none;"'; ?>>
						<div class="col-xs-7">
							<label class="col-xs-12 col-sm-12 no-padding no-margin">Discounted Item</label>
							<p class="help-block col-xs-12 no-padding no-margin">You are in "<?php if(isset($merchantInfo['DiscountTier'])) echo "<span id='discounttier'>".$merchantInfo['DiscountTier']."</span>"; ?>" Tier</p>
							
						</div>						
						<div class="col-xs-5  text-right" > 
								<span class="email_notification">
								<input checked="checked" style="display: none;" id="Discount" name="Discount" type="checkbox">
								<input type="hidden" id="Discount_val" name="Discount_val" value="<?php if(isset($Discount) && $Discount == '1') echo "1"; else if(isset($Discount) && $Discount == '0') echo "0"; else echo "1"; ?>"></span>
								<!--<select class="Discount" name="Discount" id="Discount">
									<option value="1" <?php if(isset($Discount) && $Discount == '1') echo "selected"; else echo "selected"; ?>>Active</option>
									<option value="0" <?php if(isset($Discount) && $Discount == '0') echo "selected"; ?>>Inactive</option>
								</select> -->
						</div>
					</div>
					</div>
					<div class="clear"></div>
					<div class="form-group col-xs-6">
						<label class="col-xs-4">Status</label>
						<div class="col-xs-8 col-sm-12 radio des_label no-padding no-margin"> 
							<!-- <div class="col-xs-5 col-sm-5 left text-right no-padding"><input type="radio" name="Status" id="Active" value="1" <?php if(isset($Status) && $Status == '1') echo "checked"; else echo "checked"; ?>>&nbsp;<label for="Active" class="no_bold">Active</label></div>
							<div class="col-xs-7 col-sm-7 left padding-left text-right"><input type="radio" name="Status"  id="Inactive" value="2" <?php if(isset($Status) && $Status == '2') echo "checked";?>>&nbsp;<label for="Inactive" class="no_bold">Inactive</label></div> -->
								<div class="col-xs-5 col-sm-5 left text-right pad-product no-padding"><input type="radio" name="Status" id="Active" value="1" <?php if(isset($Status) && $Status == '1') echo "checked"; else echo "checked"; ?>>&nbsp;<label for="Active" class="no_bold"><span class="fleft active">Active</span></label></div>
								<div class="col-xs-7 col-sm-7 left padding-left text-right"><input type="radio" name="Status"  id="Inactive" value="2" <?php if(isset($Status) && $Status == '2') echo "checked";?>>&nbsp;<label for="Inactive" class="no_bold"><span class="fleft inactive">Inactive</span></label></div>
						</div>
					</div>
					
					<div class="form-group col-xs-6" <?php if($adddeals == 1) echo 'style="display:none;"'; ?>>
					<div class="col-xs-7">	
						<label class="col-xs-12  no-padding  no-margin">Discounted Price</label>
						<p class="help-block col-xs-12 no-padding  no-margin">Calculated automatically</p>
					</div> 
						<div class="col-xs-4 col-sm-12  text-right  no-padding">
							<span class="no-padding" id="discount_price">
							<?php if(isset($DiscountPrice)){
									echo price_fomat($DiscountPrice);
								}
								else
									echo price_fomat('0');
							?>
							</span> 
							<input type="Hidden"  class="form-control text-right" name="DiscountPrice" id="DiscountPrice" value="<?php if(isset($DiscountPrice)) echo $DiscountPrice; else echo "0"; ?>" readonly>
						</div>
					</div>
					<input type="Hidden"  class="form-control text-right" name="productType" id="productType" value="<?php if($adddeals == 1 && $addspecial == 0) echo '2'; else if($adddeals == 1 && $addspecial == 1) echo '3'; else echo "1"; ?>" readonly>
					<div class="footer col-xs-12 text-center clear no-padding" style="padding-bottom:0px;"> 
					
						
						<?php if(isset($_GET['edit']) && !empty($_GET['edit'])) { ?>
							<input type="Hidden" name="editId" id="editId" value="<?php echo $_GET['edit']; ?>" />
								<a href="Product?show=0&delete=<?php echo $_GET['edit']; if($addspecial == 1) echo "&Type=3"; ?>" class="link btn box-primary col-xs-4" onclick="return confirm('Are you sure to delete?')">DELETE</a>
								<a href="#" class="link col-xs-4 cancel" onclick="parent.jQuery.fancybox.close();">CANCEL</a>
							<input type="submit" name="merchant_product_update" id="merchant_product_update" value="Save" class="btn btn-success col-xs-4">		
						<?php }  else {  ?>
							<a href="#" class="link col-xs-3 cancel" onclick="parent.jQuery.fancybox.close();">CANCEL</a>
							<input type="submit" name="merchant_product_submit" id="merchant_product_submit" value="ADD" class="btn btn-success col-xs-9">
						<?php } ?>
					</div>
				</div><!-- /row -->		
			</form>
			</div>
		<?php } 
		if(isset($_GET['show']) && $_GET['show'] !=0) 	footerLogin(); 	commonFooter(); ?>
</html>
<script type="text/javascript">
	/*$(document).ready(function() {
		$('.icon_fancybox').fancybox();	
		//$('.Discount').switchify();
	});*/	
	$(function() {
		var data = {name: 'product_photo', type : '1'};
		$('#product_photo').change(data,uploadFiles);
		
		/* Discount calculation */
		$(".tog").click(function(){
			calculateDiscountPrice('1');
		});
		
		if($('#Discount_val').val() == 1)
			$(".tog").addClass('on');
		else 
			$(".tog").removeClass('on');
		/* Discount calculation */
	});	
</script>
<style>

@media only screen and (max-width : 480px) {
	#add_product_form  .form-group.col-xs-6 {width:100%}
}
@media only screen and (max-width : 360px) {
	#specialProducts .col-xs-3 ,#specialProducts .col-xs-2 ,#specialProducts .col-xs-5 {clear:both;width:100%}
	#specialProducts .Product_val:before{content: "Product"}
	#specialProducts .Quantity_val:before{content: "Quantity"}
	#specialProducts .Price_val:before{content: "Price"}
	.title_row {display:none}
	.Quantity_val {padding:0 15px !important}
	.Quantity_val,.Price_val,.count_val {clear:none;float:left;width:40%}
	.count_val {width:20% !important}
}
</style>