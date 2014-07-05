<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();
$hide = 0;
$Photo = $PhotoContent = $ProductId = '';
$adddeals	=	$addspecial	=	0;
$ItemType	=	'1';
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
	$merchantInfo  =	$_SESSION['merchantDetailsInfo'];
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

//getting product categories
$url					=	WEB_SERVICE.'v1/categories/products';
$curlCategoryResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
if(isset($curlCategoryResponse) && is_array($curlCategoryResponse) && $curlCategoryResponse['meta']['code'] == 201 && is_array($curlCategoryResponse['productCategoryDetails']) ) {
	if(isset($curlCategoryResponse['productCategoryDetails']))
		$productCategories = $curlCategoryResponse['productCategoryDetails'];	
} 

if(isset($_GET['add']) && !empty($_GET['add']))
	$Category = $_GET['add'];

//getting product detail
if(isset($_GET['edit']) && !empty($_GET['edit'])) {	
	$url					=	WEB_SERVICE.'v1/products/'.$_GET['edit'];
	$curlCategoryResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlCategoryResponse) && is_array($curlCategoryResponse) && $curlCategoryResponse['meta']['code'] == 201 && is_array($curlCategoryResponse['ProductDetail']) ) {
		if(isset($curlCategoryResponse['ProductDetail'])) {
			$ProductDetail = $curlCategoryResponse['ProductDetail'];
			if(isset($ProductDetail) && count($ProductDetail) > 0) {
				$ProductId		= $ProductDetail[0]['id'];
				$Photo 			= $ProductDetail[0]['Photo'];
				$Category 		= $ProductDetail[0]['fkCategoryId'];
				$ItemName 		= $ProductDetail[0]['ItemName'];
				$ItemDescription= $ProductDetail[0]['ItemDescription'];
				$Price 			= $ProductDetail[0]['Price'];	
				$Status 		= $ProductDetail[0]['Status'];
				$Discount 		= $ProductDetail[0]['DiscountApplied'];
				if($ProductDetail[0]['DiscountApplied'] == 1) {
					$DiscountPrice = $ProductDetail[0]['Price'] - (($ProductDetail[0]['Price']/100) * $merchantInfo['DiscountTier']);
					floatval($DiscountPrice); 
				}
				else
					$DiscountPrice = $ProductDetail[0]['Price'];
			}
		}		
	}	
}

//Adding and updating Products
if((isset($_POST['merchant_product_submit']) && $_POST['merchant_product_submit'] == 'Save') || (isset($_POST['merchant_product_update']) && $_POST['merchant_product_update'] == 'Update')){	
	//echo "<pre>"; echo print_r($_POST); echo "</pre>";
	if(isset($_POST['product_photo_upload']) && !empty($_POST['product_photo_upload'])) {
		$Photo 			= TEMP_IMAGE_PATH.$_POST['product_photo_upload'];
		$PhotoContent	= $_POST['product_photo_upload'];
	}	
	
	$Category 			= $_POST['Category'];
	$ItemName 			= $_POST['ItemName'];
	$Price 				= $_POST['Price'];
	$Status 			= $_POST['Status'];
	$Discount 			= $_POST['Discount'];
	$DiscountPrice 		= $_POST['DiscountPrice'];
	$ItemDescription 	= '';
	
	if($_POST['productType'] == 1) {
		$ItemType		= '2';
		$Category 		= '0';
		$Discount 		= '0';
		$ItemDescription= $_POST['ItemDescription'];
	}
	//echo'<pre>';print_r($_POST);echo'</pre>';
	
	$data	=	array(
				'ProductId'				=> $ProductId,
				'Photo' 				=> $PhotoContent,
				'CategoryId'			=> $Category,
				'ItemName' 				=> $ItemName,	
				'ItemDescription'		=> $ItemDescription,	
				'Price' 				=> $Price,	
				'Status' 				=> $Status,
				'Discount' 				=> $Discount,
				'ItemType' 				=> $ItemType,
				'ImageAlreadyExists' 	=> $_POST['empty_product_photo'],
			);
	
	//echo "<pre>"; echo print_r($data); echo "</pre>";
	//die();
	
	if(isset($_GET['edit']) && !empty($_GET['edit'])) {
		$method	=	'PUT';
		$url	=	WEB_SERVICE.'v1/products/'.$ProductId;
		$curlResponse	=	curlRequest($url,$method,json_encode($data),$_SESSION['merchantInfo']['AccessToken']);	
	}
	else {
		$method	=	'POST';	
		$url	=	WEB_SERVICE.'v1/products/';
		$curlResponse	=	curlRequest($url,$method,$data, $_SESSION['merchantInfo']['AccessToken']);		
	}
	
	if(isset($curlResponse) && is_array($curlResponse) && $curlResponse['meta']['code'] == 201) {
		$successMessage = $curlResponse['notifications'][0];
		unset($_POST);
	} else if(isset($curlResponse['meta']['errorMessage']) && $curlResponse['meta']['errorMessage'] != '') {
		$errorMessage	=	$curlResponse['meta']['errorMessage'];
	} else {
		$errorMessage 	= 	"Bad Request";
	}
}

//Delete Product
if(isset($_GET['delete']) && !empty($_GET['delete'])) {
	$url					=	WEB_SERVICE.'v1/products/'.$_GET['delete'];
	$curlCategoryResponse 	= 	curlRequest($url, 'DELETE', null,$_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlCategoryResponse) && is_array($curlCategoryResponse) && $curlCategoryResponse['meta']['code'] == 201) {
		$successMessage = "Product Deleted successfully";
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
		<?php if(isset($_GET['show']) && $_GET['show'] ==0) {	
				} 
				else 
					top_header(); 
			if(isset($msg) && $msg != '') {
		?>
		<div align="center" class="alert <?php  echo $class;  ?> alert-dismissable col-xs-10">
			<i class="fa <?php  echo $class_icon;  ?>"></i>  <?php echo $msg; ?>
		</div>
		<?php } if(isset($hide) & $hide == 1) {  } else { ?>			
			<form action="" name="<?php if(isset($_GET['edit']) && !empty($_GET['edit'])) echo "edit"; else echo "add"; ?>_product_form" id="<?php if(isset($_GET['edit']) && !empty($_GET['edit'])) echo "edit"; else echo "add"; ?>_product_form"  method="post">
				<div class="row popup">
					<div class="form-group col-xs-12 no-padding" style="min-height:85px;">						   
						<div class="col-xs-3 col-sm-3 no-padding" style="margin-left:16px;">
							<label class="col-xs-3 " id="product_photo_img">
							<img height="75" width="75" src="<?php if(isset($Photo) && !empty($Photo)) echo $Photo; else echo MERCHANT_SITE_IMAGE_PATH."no_photo_burger.jpg"; ?>">
							<?php if(isset($PhotoContent) && !empty($PhotoContent)) { ?>
								<input id="product_photo_upload" type="hidden" value="<?php echo $PhotoContent; ?>" name="product_photo_upload">
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
						<p class="help-block col-xs-9">Max. 30 characters</p>
						<div class="col-xs-12 no-padding">
							<input class="form-control" type="text" maxlength="30" name="ItemName" id="ItemName" value="<?php if(isset($ItemName)) echo $ItemName; ?>" />
						</div>
					</div>
					<div class="form-group col-xs-12 error_msg_hgt " style="min-height:108px;<?php if($adddeals == 0 || $addspecial == 1) echo "display:none;"; ?>">							
						<label class="col-xs-12 no-padding">Short Description</label>
						<div class="col-xs-12 no-padding">
							<textarea class="form-control" name="ItemDescription" id="ItemDescription"><?php if(isset($ItemDescription)) echo $ItemDescription; ?></textarea>
						</div>
					</div>
					<div class="form-group col-xs-12 error_msg_hgt " style="min-height:108px;<?php if($addspecial == 0) echo "display:none;"; ?>">							
						<label class="col-xs-12 no-padding">Products</label>
						<div class="col-xs-12 no-padding">
							<table class="specialProducts" id="specialProducts" name="specialProducts">
								<tr id='row1'>
									<td>
										<select class="" name="Products" id="Products">								
											<option value="">Select</option>
											<option value="">11111111</option>
											<option value="">22222222</option>
										</select>
									</td>
									<td><input type="text"  class="" placeholder="Quantity" name="quantity" id="quantity" onkeypress="return isNumberKey(event);" maxlength="3" value="" onkeyup=""></td>
									<td><input type="text"  class="" placeholder="Price" name="quantityTotalPrice" id="quantityTotalPrice" onkeypress="return isNumberKey(event);" value="" onkeyup="" readonly></td>
									<td><i class="fa fa-plus" id="plusminus" name="plusminus" onclick="return newSpecialRow();" style="cursor:pointer"></i></td>
								</tr>
							</table>
						</div>
						<input type="text"	class="" placeholder="Totalrows" name="Totalrows" id="Totalrows" value="1" readonly>
						<input type="text"  class="" placeholder="TotalPrice" name="TotalPrice" id="TotalPrice" onkeypress="return isNumberKey(event);" value="" onkeyup="" readonly>
					</div>
					<div class="form-group col-xs-12 error_msg_hgt60">							
						<label class="col-xs-9 no-padding">Item Price</label>
						<div class="col-xs-3 no-padding">
							<span class="col-xs-1 LH30 no-padding">$</span>
							<span class="col-xs-11 no-padding">
								<input type="text"  class="form-control text-right" name="Price" id="Price" onkeypress="return isNumberKey_price(event);" maxlength="10" value="<?php if(isset($Price)) echo $Price; ?>" onkeyup="return calculateDiscountPrice();">
							</span>							
						</div>
					</div>	
					<div class="form-group col-xs-12">
						<label class="col-xs-6 col-sm-3 no-padding">Status</label>
						<div class="col-xs-6 col-sm-12 no-padding"> 
							<div class="col-xs-6 text-right no-padding"><input type="radio" name="Status" id="Active" value="1" <?php if(isset($Status) && $Status == '1') echo "checked"; else echo "checked"; ?>>&nbsp;<label for="Active" class="no_bold">Active</label></div>
							<div class="col-xs-6 no-padding text-right"><input type="radio" name="Status"  id="Inactive" value="2" <?php if(isset($Status) && $Status == '2') echo "checked";?>>&nbsp;<label for="Inactive" class="no_bold">Inactive</label></div>
						</div>
					</div>
					<div class="form-group col-xs-12" <?php if($adddeals == 1) echo 'style="display:none;"'; ?>>
						<div class="col-xs-9 no-padding">
							<label class="col-xs-6 col-sm-3 no-padding">Discounted Item</label>
							<p class="help-block col-xs-6">You are in <?php if(isset($merchantInfo['DiscountTier'])) echo "<span id='discounttier'>".$merchantInfo['DiscountTier']."</span>"; ?> Tier</p>
							
						</div>						
						<div class="col-xs-3 col-sm-12 no-padding text-right"> 
								<select class="Discount" name="Discount" id="Discount">
									<option value="1" <?php if(isset($Discount) && $Discount == '1') echo "selected"; else echo "selected"; ?>>On</option>
									<option value="0" <?php if(isset($Discount) && $Discount == '0') echo "selected"; ?>>Off</option>
								</select>
						</div>
					</div>
					<div class="form-group col-xs-12" <?php if($adddeals == 1) echo 'style="display:none;"'; ?>>
					<div class="col-xs-9 no-padding">	
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
					<input type="Hidden"  class="form-control text-right" name="productType" id="productType" value="<?php if(isset($adddeals)) echo $adddeals; else echo "0"; ?>" readonly>
					<div class="footer col-xs-12 text-center clear"> 
						<a href="#" class="link" onclick="parent.jQuery.fancybox.close();">Cancel</a>&nbsp;&nbsp;&nbsp;
						<?php if(isset($_GET['edit']) && !empty($_GET['edit'])) { ?>
							<input type="Hidden" name="editId" id="editId" value="<?php echo $_GET['edit']; ?>" />
							<input type="submit" name="merchant_product_update" id="merchant_product_update" value="Update" class="btn btn-success ">&nbsp;&nbsp;&nbsp;
							<a href="Product?show=0&delete=<?php echo $_GET['edit']; ?>" class="link text-red" onclick="return confirm('Are you sure to delete?')">Delete</a>
						<?php }  else {  ?>
							<input type="submit" name="merchant_product_submit" id="merchant_product_submit" value="Save" class="btn btn-success ">
						<?php } ?>
					</div>
				</div><!-- /row -->		
			</form>
		<?php } if(isset($_GET['show']) && $_GET['show'] ==0) {
				}
				else footerLogin(); 
		?>
	<?php commonFooter(); ?>
</html>
<script type="text/javascript">
	$(document).ready(function() {
		$('.icon_fancybox').fancybox();	
		$('.Discount').switchify();
	});	
</script>