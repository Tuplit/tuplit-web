<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();
$hide 		= 	0;
$Photo 		= 	$PhotoContent = $ProductId = '';
$adddeals	=	$addspecial	=	$updatepro	=	0;
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
	$delSpProducts	=	'';
	if(isset($_GET['Type']) && !empty($_GET['Type']) && isset($_GET['ProductIds']) && !empty($_GET['ProductIds'])) {
		$delType		=	 $_GET['Type'];
		$delSpProducts	=	 $_GET['ProductIds'];
	}
	if($delType == 3)
		$url					=	WEB_SERVICE.'v1/products/'.$_GET['delete'].'?Type=3&ProductIds='.$delSpProducts;
	else
		$url					=	WEB_SERVICE.'v1/products/'.$_GET['delete'];
	$curlDeleteResponse 	= 	curlRequest($url, 'DELETE', null,$_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlDeleteResponse) && is_array($curlDeleteResponse) && $curlDeleteResponse['meta']['code'] == 201) {
		$successMessage 	= 	"Product Deleted successfully";
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
						$DiscountPrice 	= 	$ProductDetail['Price'] - (($ProductDetail['Price']/100) * $merchantInfo['DiscountTier']);
						floatval($DiscountPrice); 
					}
					else
						$DiscountPrice 	= 	$ProductDetail['Price'];
					if($ProductDetail['ItemType'] == 3 && !empty($ProductDetail['SpecialProducts'])) {
						$editSpecialProduct			=	$ProductDetail['SpecialProducts'];
						$TotaleditSpecialProduct	=	count($editSpecialProduct);
						$RowSpecialIds				=	$SpecialProductIds	=	'';
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
							
							//Forming special products insert ids
							if(empty($SpecialProductIds))
								$SpecialProductIds	=	$sppro['SpecialId'];
							else
								$SpecialProductIds	=	$SpecialProductIds.','.$sppro['SpecialId'];
						}
					}				
				}
			}		
		}	
	}

	//Adding and updating Products
	if((isset($_POST['merchant_product_submit']) && $_POST['merchant_product_submit'] == 'Save') || (isset($_POST['merchant_product_update']) && $_POST['merchant_product_update'] == 'Update')){	
		if(isset($_POST['product_photo_upload']) && !empty($_POST['product_photo_upload'])) {
			$Photo 					= 	TEMP_IMAGE_PATH.$_POST['product_photo_upload'];
			$PhotoContent			= 	$_POST['product_photo_upload'];
		}	
		$Category 					= 	$_POST['Category'];
		$ItemName 					= 	$_POST['ItemName'];
		$Price 						= 	$_POST['Price'];
		$Status 					= 	$_POST['Status'];
		$Discount 					= 	$_POST['Discount'];
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
		//echo $url;
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
							$errorMessage	=	"Now you can add only discounted item or edit an item with discount";
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
			} else if(isset($curlResponse['meta']['errorMessage']) && $curlResponse['meta']['errorMessage'] != '') {
				$errorMessage	=	$curlResponse['meta']['errorMessage'];
			} else {
				$errorMessage 	= 	"Bad Request";
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
		$hide			=  1;
	}

commonHead();
?>

<body class="skin-blue fixed" onload="fieldfocus('ItemName');">
		<?php 
				if(isset($_GET['show']) && $_GET['show'] !=0) 	top_header(); 
				if(isset($msg) && $msg != '') {
		?><br><br>
		<div align="center" class="alert <?php  echo $class;  ?> alert-dismissable col-xs-10">
			<i class="fa <?php  echo $class_icon;  ?>"></i>  <?php echo $msg; ?>
		</div>
		<?php } if(isset($hide) & $hide == 1) {  } else { ?>			
			<form action="" name="<?php if(isset($_GET['edit']) && !empty($_GET['edit'])) echo "edit"; else echo "add"; ?>_product_form" id="<?php if(isset($_GET['edit']) && !empty($_GET['edit'])) echo "edit"; else echo "add"; ?>_product_form"  method="post" <?php if($addspecial != 0) echo 'onsubmit="return validateSpecialdata()"'; ?>>
				<div class="row popup">
					<div class="form-group col-xs-12 no-padding" style="min-height:85px;">						   
						<div class="col-sm-3 no-padding no-margin" style="margin-left:16px;">
							<label class="col-xs-3 " id="product_photo_img">
							<img height="75" width="75" src="<?php if(isset($Photo) && !empty($Photo)) echo $Photo; else echo MERCHANT_SITE_IMAGE_PATH."no_photo_burger.jpg"; ?>" />
							<?php if(isset($PhotoContent) && !empty($PhotoContent)) { ?>
								<input id="product_photo_upload" type="hidden" value="<?php echo $PhotoContent; ?>" name="product_photo_upload" />
							<?php } ?>
						</label>
						</div>
						<div class="col-xs-8 col-sm-12">
							<input type="file"  name="product_photo" id="product_photo" onchange="return ajaxAdminFileUploadProcess('product_photo');"  /> 
							<p class="help-block">Pls upload JPG or PNG files. The best resolution is 300X300 pixels.</p>						
							<span class="error" for="empty_product_photo" generated="true" style="display: none">Product Image is required</span>										
							<input type="Hidden" name="empty_product_photo" id="empty_product_photo" value="<?php if(isset($Photo) && !empty($Photo)) echo "1"; ?>" />
							<input type="Hidden" name="name_product_photo" id="name_product_photo" value="<?php echo $PhotoContent; ?>" />		
						</div>				
					</div>
					<div class="form-group col-xs-12 error_msg_hgt" <?php if($adddeals == 1) echo 'style="display:none;"'; ?>>
						<label class="col-xs-3 no-padding">Category</label>
						<p class="help-block">Item can be re-arranged into other category anytime</p>
						<div class="col-xs-12 no-padding">
							<select class="form-control " name="Category">								
								<?php if($adddeals == 1) { ?>
								<option value="deals" selected>Deals</option>
								<?php } else {?>
									<option value="" >Select</option>
									<?php if(isset($productCategories) && !empty($productCategories)) {
										foreach($productCategories as $key=>$val) {								
									?>
									<option value="<?php echo $val['CategoryId'];?>" <?php if(isset($Category) && $Category == $val['CategoryId']) echo "selected";?>><?php echo ucfirst($val['CategoryName']);?></option>
								<?php } } } ?>								
							</select>
						</div>
					</div>
					<div class="form-group col-xs-12 error_msg_hgt ">							
						<label class="col-xs-3 no-padding">Item Name</label>
						<p class="help-block">Max. 30 characters</p>
						<div class="col-xs-12 no-padding">
							<input class="form-control" type="text" maxlength="30" name="ItemName" id="ItemName" value="<?php if(isset($ItemName)) echo $ItemName; ?>" />
						</div>
					</div>
					<div class="form-group col-xs-12 " style="<?php if($adddeals == 0 || $addspecial == 1) echo "display:none;"; ?>">							
						<label class="col-xs-12 no-padding">Short Description</label>
						<div class="col-xs-12 no-padding">
							<textarea class="form-control" name="ItemDescription" id="ItemDescription" rows="2"><?php if(isset($ItemDescription)) echo $ItemDescription; ?></textarea>
						</div>
					</div>
					<div class="form-group col-xs-12 no-margin " style="<?php if($addspecial == 0) echo "display:none;"; ?>">							
						<label class="col-xs-12 no-padding">Special Products</label>
						<div class="col-xs-12 no-padding">
							<div class="specialProducts" id="specialProducts" name="specialProducts">								
								<div class="row" id="copydiv" style="display:none;">
									<div class="col-xs-5">
										<select class="form-control" name="Products" id="Products" onchange="return calculateSpecialPrice('1',this);">							
											<option value="">Select</option>
											<?php foreach($ProductsArray as $s_key=>$s_value){ 
													if(!empty($s_value['ProductId'])) { ?>
														<option value="<?php echo $s_value['ProductId']; ?>"><?php echo $s_value['ItemName']; ?></option>
											<?php } } ?>
										</select>
									</div>
									<div class="col-xs-2  no-padding">
										<input type="text"  class="form-control" name="quantity" id="quantity" onkeypress="return isNumberKey(event);" maxlength="3" value="" onkeyup="return calculateSpecialPrice('2',this);">
									</div>
									<div class="col-xs-3">
										<input type="text"  class="form-control" name="quantityTotalPrice" id="quantityTotalPrice" onkeypress="return isNumberKey(event);" value="" onkeyup="" readonly>				
									</div>
									<div class="col-xs-1 no-padding text-right" style="padding-top:5px !important">
										<i class="fa fa-plus-circle fa-lg" id="plus" name="plus" onclick="return newSpecialRow(1,this);" style="color:#01b3a5;cursor:pointer;"></i>
										&nbsp;&nbsp;<i class="fa fa-minus-circle fa-lg" id="minus" name="minus" onclick="return newSpecialRow(2,this);" style="color:#f56954;cursor:pointer;display:none;"></i>
									</div>
									<div class="col-xs-12 pad"></div>
								</div>
								
								<div class="row">
									<div class="col-xs-5"><strong>Product</strong></div>
									<div class="col-xs-2 no-padding"><strong>Quantity</strong></div>
									<div class="col-xs-3"><strong>Price</strong></div>
									<div class="col-xs-2"></div>
								</div>
								
								<?php if(isset($editSpecialProduct) && !empty($editSpecialProduct) && count($editSpecialProduct)>0) { 
									foreach($editSpecialProduct as $spkey=>$spval) { $sprow	=	$spkey + 1;
								?>
								<div class="row" id='Row_<?php echo $sprow;?>'>
										<div class="col-xs-5 ">
										<select class="form-control" name="Products<?php echo $sprow;?>" id="Products<?php echo $sprow;?>" onchange="return calculateSpecialPrice('1',this);">							
											<option value="">Select</option>
											<?php 
													foreach($ProductsArray as $s_key=>$s_value){ if(!empty($s_value['ProductId'])) { ?>
														<option value="<?php echo $s_value['ProductId']; ?>" <?php if($spval['fkProductsId'] == $s_value['ProductId']) echo "selected"; ?>><?php echo $s_value['ItemName']; ?></option>
											<?php } } ?>
										</select>
										</div>
										<div class="col-xs-2  no-padding">
											<input type="text"  class="form-control" name="quantity<?php echo $sprow;?>" id="quantity<?php echo $sprow;?>" onkeypress="return isNumberKey(event);" maxlength="3" value="<?php if(!empty($spval['Quantity']))  echo $spval['Quantity']; ?>" onkeyup="return calculateSpecialPrice('2',this);">
										</div>
										<div class="col-xs-3 ">
										<input type="text"  class="form-control " name="quantityTotalPrice<?php echo $sprow;?>" id="quantityTotalPrice<?php echo $sprow;?>" onkeypress="return isNumberKey(event);" value="<?php if(!empty($spval['Quantity']) && !empty($spval['Price']))  echo ($spval['Quantity']*$spval['Price']); ?>" onkeyup="" readonly>
										</div>
										<div class="col-xs-1 no-padding text-right" style="padding-top:5px !important">
											<i class="fa fa-plus-circle fa-lg" id="plus<?php echo $sprow;?>" name="plus<?php echo $sprow;?>" onclick="return newSpecialRow(1,this);" style="color:#01b3a5;cursor:pointer;<?php if($TotaleditSpecialProduct != $sprow) echo "display:none"; ?>"></i>
											&nbsp;&nbsp;<i class="fa fa-minus-circle fa-lg" id="minus<?php echo $sprow;?>" name="minus<?php echo $sprow;?>" onclick="return newSpecialRow(2,this);" style="color:#f56954;cursor:pointer;"></i>
										</div>
										<div class="col-xs-12 pad"></div>
								</div>								
								<?php } } else { ?>
								<div class="row" id='Row_1'>
										<div class="col-xs-5 ">
											<select class="form-control" name="Products1" id="Products1" onchange="return calculateSpecialPrice('1',this);">							
												<option value="">Select</option>
												<?php if(isset($ProductsArray) && count($ProductsArray) > 0)  { 
														foreach($ProductsArray as $s_key=>$s_value){ if(!empty($s_value['ProductId'])) { ?>
															<option value="<?php echo $s_value['ProductId']; ?>" ><?php echo $s_value['ItemName']; ?></option>
												<?php } } } ?>
											</select>
										</div>
										
										<div class="col-xs-2 no-padding">
										<input type="text"  class="form-control " name="quantity1" id="quantity1" onkeypress="return isNumberKey(event);" maxlength="3" value="" onkeyup="return calculateSpecialPrice('2',this);">
										</div>
										
										<div class="col-xs-3 ">
										<input type="text"  class="form-control " name="quantityTotalPrice1" id="quantityTotalPrice1" onkeypress="return isNumberKey(event);" value="" onkeyup="" readonly>
										</div>
										<div class="col-xs-1 no-padding text-right" style="padding-top:5px !important">
											<i class="fa fa-plus-circle fa-lg" id="plus1" name="plus1" onclick="return newSpecialRow(1,this);" style="color:#01b3a5;cursor:pointer;"></i>
											&nbsp;&nbsp;<i class="fa fa-minus-circle fa-lg" id="minus1" name="minus1" onclick="return newSpecialRow(2,this);" style="color:#f56954;cursor:pointer;display:none;"></i>
										</div>	
										<div class="col-xs-12 pad"></div>
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
					<div class="form-group col-xs-12 error_msg_hgt60" style="<?php if($addspecial == 0) echo "display:none;"; ?>">							
							<label class="col-xs-9 col-sm-3 no-padding">Total Price</label>
							<div class="col-xs-3 col-sm-3 no-padding">
								<span class="col-xs-1 LH30 no-padding">$</span>
								<span class="col-xs-11 col-sm-5 no-padding">
									<input type="text"  class="form-control text-right" name="TotalPrice" id="TotalPrice" value="<?php if(isset($TotalSpecialAmount) && !empty($TotalSpecialAmount)) echo $TotalSpecialAmount; ?>" readonly>
								</span>	
							</div>
						</div>	
					<div class="form-group col-xs-12 error_msg_hgt60">							
						<label class="col-xs-9 col-sm-3 no-padding"><?php if($addspecial == 0) echo "Item"; else echo "Special" ?> Price</label>
						<div class="col-xs-3 col-sm-3 no-padding">
							<span class="col-xs-1 LH30 no-padding">$</span>
							<span class="col-xs-11 col-sm-5 no-padding">
								<input type="text"  class="form-control text-right" name="Price" id="Price" onkeypress="return isNumberKey_price(event);" maxlength="10" value="<?php if(isset($Price)) echo $Price; ?>" onkeyup="return calculateDiscountPrice();">
							</span>	
							<span id="specialpriceerror" style="clear: both;color: red;display:none;font-size: 11px; font-weight: normal;">Special Price must be less then total price</span>
						</div>
					</div>	
					<div class="form-group col-xs-12">
						<label class="col-xs-6 no-padding">Status</label>
						<div class="col-xs-6 col-sm-3 no-padding"> 
							<div class="col-xs-6 left text-right no-padding"><input type="radio" name="Status" id="Active" value="1" <?php if(isset($Status) && $Status == '1') echo "checked"; else echo "checked"; ?>>&nbsp;<label for="Active" class="no_bold">Active</label></div>
							<div class="col-xs-6 left no-padding text-right"><input type="radio" name="Status"  id="Inactive" value="2" <?php if(isset($Status) && $Status == '2') echo "checked";?>>&nbsp;<label for="Inactive" class="no_bold">Inactive</label></div>
						</div>
					</div>
					<div class="form-group col-xs-12" <?php if($adddeals == 1) echo 'style="display:none;"'; ?>>
						<div class="col-xs-8 no-padding">
							<label class="col-xs-6 col-sm-3 no-padding">Discounted Item</label>
							<p class="help-block col-xs-6">You are in <?php if(isset($merchantInfo['DiscountTier'])) echo "<span id='discounttier'>".$merchantInfo['DiscountTier']."</span>"; ?> Tier</p>
							
						</div>						
						<div class="col-xs-4 col-sm-12 no-padding text-right"> 
								<select class="Discount" name="Discount" id="Discount">
									<option value="1" <?php if(isset($Discount) && $Discount == '1') echo "selected"; else echo "selected"; ?>>On</option>
									<option value="0" <?php if(isset($Discount) && $Discount == '0') echo "selected"; ?>>Off</option>
								</select>
						</div>
					</div>
					<div class="form-group col-xs-12" <?php if($adddeals == 1) echo 'style="display:none;"'; ?>>
					<div class="col-xs-8 no-padding">	
						<label class="col-xs-6 col-sm-3 no-padding">Discounted Price</label>
						<p class="help-block col-xs-6">Calculated automatically</p>
					</div> 
						<div class="col-xs-3 col-sm-12 no-padding text-right">
							<span class="no-padding" id="discount_price">
							<?php if(isset($DiscountPrice))
									echo "$".$DiscountPrice;
								else
									echo "$0";
							?>
							</span> 
							<input type="Hidden"  class="form-control text-right" name="DiscountPrice" id="DiscountPrice" value="<?php if(isset($DiscountPrice)) echo $DiscountPrice; else echo "0"; ?>" readonly>
						</div>
					</div>
					<input type="Hidden"  class="form-control text-right" name="productType" id="productType" value="<?php if($adddeals == 1 && $addspecial == 0) echo '2'; else if($adddeals == 1 && $addspecial == 1) echo '3'; else echo "1"; ?>" readonly>
					<div class="footer col-xs-12 text-center clear"> 
						<a href="#" class="link" onclick="parent.jQuery.fancybox.close();">Cancel</a>&nbsp;&nbsp;&nbsp;
						<?php if(isset($_GET['edit']) && !empty($_GET['edit'])) { ?>
							<input type="Hidden" name="editId" id="editId" value="<?php echo $_GET['edit']; ?>" />
							<input type="submit" name="merchant_product_update" id="merchant_product_update" value="Update" class="btn btn-success ">&nbsp;&nbsp;&nbsp;
							<a href="Product?show=0&delete=<?php echo $_GET['edit']; if($addspecial == 1) echo "&Type=3&ProductIds=".$SpecialProductIds; ?>" class="link text-red" onclick="return confirm('Are you sure to delete?')">Delete</a>
						<?php }  else {  ?>
							<input type="submit" name="merchant_product_submit" id="merchant_product_submit" value="Save" class="btn btn-success ">
						<?php } ?>
					</div>
				</div><!-- /row -->		
			</form>
		<?php } 
		if(isset($_GET['show']) && $_GET['show'] !=0) 	footerLogin(); 	commonFooter(); ?>
</html>
<script type="text/javascript">
	$(document).ready(function() {
		$('.icon_fancybox').fancybox();	
		$('.Discount').switchify();
	});	
</script>