<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();
global $discountTierArray;
$error = '';
$merchantInfo = $errorMessage = '';
$min_val	=	$max_val	= $prizeRange	= $maximumPrice = $minimumPrice = $imagePath = $iconPath	='';
$merchantCategory = array();
$date_now = date('Y-m-d H:i:s');
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
if(isset($merchantInfo['PriceRange']) && $merchantInfo['PriceRange'] != ''){
  $prizeArray		=	explode(',',$merchantInfo['PriceRange']);
  if(isset( $prizeArray[0] ) &&  $prizeArray[0] !='')
  	$min_val		=	$prizeArray[0];
  if(isset( $prizeArray[1] ) &&  $prizeArray[1] !='')
  	$max_val		=	$prizeArray[1];
}

$url					=	WEB_SERVICE.'v1/categories/';
$curlCategoryResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
if(isset($curlCategoryResponse) && is_array($curlCategoryResponse) && $curlCategoryResponse['meta']['code'] == 201 && is_array($curlCategoryResponse['categoryDetails']) ) {
	if(isset($curlCategoryResponse['categoryDetails']))
	$categories = $curlCategoryResponse['categoryDetails'];
	if(isset($_POST['categorySelected']))
		$newCategory	=	$_POST['categorySelected'];
	else{
		if(isset($merchantInfo['Category']) && is_array ($merchantInfo['Category']))
			$newCategory		=	$merchantInfo['Category'][0]['catId'];
	}
} else if(isset($curlCategoryResponse['meta']['errorMessage']) && $curlCategoryResponse['meta']['errorMessage'] != '') {
		$errorMessage	=	$curlCategoryResponse['meta']['errorMessage'];
} else {
		$errorMessage	= 	"Bad Request";
}


$merchantId		= 	$_SESSION['merchantInfo']['MerchantId'];
$url			=	WEB_SERVICE.'v1/products/'.$merchantId;	
$curlMerchantResponse  = 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201) {
	$ProductsArray   =	$curlMerchantResponse['ProductLists'];		
}	


if(isset($_POST['merchant_account_submit']) && $_POST['merchant_account_submit'] == 'SAVE'){
	if(isset($_POST['CompanyName']))
		$merchantInfo['CompanyName']	=	$_POST['CompanyName'];
	$merchantInfo['Email']	=	$_POST['Email'];
	if(isset($_POST['Address']))
		$merchantInfo['Address']	=	$_POST['Address'];
	if(isset($_POST['PhoneNumber']))
		$merchantInfo['PhoneNumber']	=	$_POST['PhoneNumber'];
	if(isset($_POST['Website']))
		$merchantInfo['WebsiteUrl']	=	$_POST['Website'];
	if(isset($_POST['Description']))
		$merchantInfo['Description']	=	$_POST['Description'];
	if(isset($_POST['ShortDescription']))
		$merchantInfo['ShortDescription']	=	$_POST['ShortDescription'];
	if(isset($_POST['OpeningHours']))
		$merchantInfo['OpeningHours']	=	$_POST['OpeningHours'];
	if(isset($_POST['categorySelected']))
		$newCategory	=	$_POST['categorySelected'];
	if(isset($_POST['DiscountTier']))
		$merchantInfo['DiscountTier']	=	$discountTierArray[$_POST['DiscountTier']].'%';
	if(isset($_POST['min_price']) && $_POST['min_price'] != '')
		$min_val		=	$_POST['min_price'];
	if(isset($_POST['max_price']) && $_POST['max_price'] != '')
		$max_val		=	$_POST['max_price'];
	if($min_val != '' && $max_val != '')
		$prizeRange		=	$min_val.','.$max_val;
	if (isset($_POST['icon_photo_upload']) && !empty($_POST['icon_photo_upload'])) {
		
		$iconPath		=	TEMP_IMAGE_PATH_REL.$_POST['icon_photo_upload'];
		if(isset($merchantInfo['Icon']) && $merchantInfo['Icon'] != ''){
			if(!SERVER){
				if(file_exists(MERCHANT_ICONS_IMAGE_PATH_REL.$merchantInfo['Icon']))
					unlink(MERCHANT_ICONS_IMAGE_PATH_REL .$merchantInfo['Icon']);
			}
			else{
				if(image_exists(6,$merchantInfo['Icon'])) 
					deleteImages(6,$merchantInfo['Icon']);
			}
		}
		$merchantInfo['Icon']	=	TEMP_IMAGE_PATH.$_POST['icon_photo_upload'];
	}
	if (isset($_POST['merchant_photo_upload']) && !empty($_POST['merchant_photo_upload'])) {
		$imagePath		=	TEMP_IMAGE_PATH_REL.$_POST['merchant_photo_upload'];
		if(isset($merchantInfo['Image']) && $merchantInfo['Image'] != ''){
			if(!SERVER){
				if(file_exists(MERCHANT_COVER_IMAGE_PATH_REL.$merchantInfo['Image']))
					unlink(MERCHANT_COVER_IMAGE_PATH_REL . $merchantInfo['Image']);
			}
			else{
				if(image_exists(7,$merchantInfo['Image'])) {
					deleteImages(7,$merchantInfo['Image']);
				}
			}
		}
		$merchantInfo['Image']	=	TEMP_IMAGE_PATH.$_POST['merchant_photo_upload'];
	}
	
	
	$data	=	array(
					'CompanyName' 		=> $_POST['CompanyName'],
					'Email' 			=> $_POST['Email'],
					'Address' 			=> $_POST['Address'],
					'PhoneNumber' 		=> $_POST['PhoneNumber'],
					'WebsiteUrl' 		=> $_POST['Website'],
					'ShortDescription' 	=> $_POST['ShortDescription'],
					'Description' 		=> $_POST['Description'],
					'OpeningHours' 		=> $_POST['OpeningHours'],
					'IconPhoto' 		=> $iconPath,
					'MerchantPhoto' 	=> $imagePath,
					'IconExist'			=> $_POST['old_icon_photo'],
					'MerchantExist'		=> $_POST['old_merchant_photo'],
					'DiscountTier' 		=> $_POST['DiscountTier'],
					'PriceRange' 		=> $prizeRange,
					'Categories' 		=> $_POST['categorySelected']
				);
	$url	=	WEB_SERVICE.'v1/merchants/';
	$method	=	'PUT';
	$curlResponse	=	curlRequest($url,$method,json_encode($data), $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlResponse) && is_array($curlResponse) && $curlResponse['meta']['code'] == 201) {
		unset($_SESSION['merchantDetailsInfo']);
		$successMessage	=	$curlResponse['notifications'][0];
		//header("location:Myaccount");
		//die();
	} else if(isset($curlResponse['meta']['errorMessage']) && $curlResponse['meta']['errorMessage'] != '') {
		$errorMessage		=	$curlResponse['meta']['errorMessage'];
	} else {
		$errorMessage		= 	"Bad Request";
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

<body class="skin-blue" onload="fieldfocus('Address');">
		<?php top_header(); ?>
		<section class="content">
		<div class="col-md-10" style="margin:auto;float:none">
		
			<section class="content-header">
                <h1>My Account</h1>
            </section>
			<?php if(isset($msg) && $msg != '') { ?>
					               <div align="center" class="alert <?php  echo $class;  ?> alert-dismissable col-sm-5"><i class="fa <?php  echo $class_icon;  ?>"></i>  <?php echo $msg; ?></div>
							<?php } ?>
			<form action="" name="add_account_form" id="add_account_form"  method="post">
				<div class="row">
				<div class="col-md-6">
					<div class="box box-primary no-padding">
						<div class="box-header no-padding">
							<h3 class="box-title">Business Info</h3>
						</div>
						<div class="form-group col-md-12">
							<label>Company Name</label>
							<input class="form-control" type="text" name="CompanyName"  id="CompanyName" value="<?php if(isset($merchantInfo['CompanyName']) && !empty($merchantInfo['CompanyName'])) echo $merchantInfo['CompanyName'];?>">
						</div>
						<div class="form-group col-md-12">
							<label>Email</label>
							<input class="form-control" type="text" name="Email"  id="Email" value="<?php if(isset($merchantInfo['Email']) && !empty($merchantInfo['Email'])) echo $merchantInfo['Email'];?>">
						</div>
						<div class="form-group col-md-12">
							<label>Password (<a href="ChangePassword" class="changePass" >Change Password</a>)</label>
							<input class="form-control" type="text" readonly id="Pass" value="**********">
						</div>
						<div class="form-group col-md-12">
							<label>Address</label>
							<textarea class="form-control" id="Address" name="Address" cols="5"><?php if(isset($merchantInfo['Address']) && !empty($merchantInfo['Address'])) echo $merchantInfo['Address'];?></textarea>
						</div>
						<div class="form-group col-md-12">
							<label>Phone Number</label>
							<input class="form-control" type="text" name="PhoneNumber"  id="PhoneNumber" onkeypress="return isNumberKey_Phone(event);" maxlength="15" value="<?php if(isset($merchantInfo['PhoneNumber']) && !empty($merchantInfo['PhoneNumber'])) echo $merchantInfo['PhoneNumber'];?>">
						</div>						    
						<div class="form-group col-md-12">
							<label>Website</label>
							<input type="url" name="Website" id="Website" class="form-control" value="<?php if(isset($merchantInfo['WebsiteUrl']) && !empty($merchantInfo['WebsiteUrl'])) echo $merchantInfo['WebsiteUrl'];?>"/>
						</div>						
						<div class="form-group col-md-12">
							<label>Short Description</label>
							<input type="text" name="ShortDescription" id="ShortDescription" class="form-control" value="<?php if(isset($merchantInfo['ShortDescription']) && !empty($merchantInfo['ShortDescription'])) echo $merchantInfo['ShortDescription'];?>"/>
						</div>
						<div class="form-group col-md-12">
							<label>Description</label>
							<textarea class="form-control" id="Description" name="Description" cols="5"><?php if(isset($merchantInfo['Description']) && !empty($merchantInfo['Description'])) echo $merchantInfo['Description'];?></textarea>
						</div>
						<div class="form-group col-md-12">
							<label>Opening Hours</label>
							<textarea class="form-control" id="OpeningHours" name="OpeningHours" cols="5"><?php if(isset($merchantInfo['OpeningHours']) && !empty($merchantInfo['OpeningHours'])) echo $merchantInfo['OpeningHours'];?></textarea>
						</div>
					</div>
				</div>
				<?php //echo "<pre>";   print_r($categories);   echo "</pre>"; ?>
				<div class="col-md-6">
					<div class="box box-primary no-padding">
						<div class="box-header ">
							<h3 class="box-title">Category</h3>
						</div>
						<div class="form-group col-md-12">
						<select name="Category" id="Category" class="form-control col-sm-6" onchange="showCategory(this.value)">
							<option value="">Select</option>	
							<?php if(isset($categories) && !empty($categories)) {
								foreach($categories as $key=>$val) {
								if($key != 'totalCount') {
							?>
							<option value="<?php echo $val['CategoryId'];?>"  style="background-image:url(<?php echo $val['CategoryIcon']; ?>);"><?php echo ucfirst($val['CategoryName']);?></option>
							<?php } } } ?>
						</select><span id="njkj"></span>				
						</div>
						<div class="form-group cats col-md-12">
							<?php if(isset($categories) && !empty($categories)) {
								foreach($categories as $key=>$val) {
							?>
								<span id="cat_id_<?php echo $val['CategoryId']; ?>" <?php if(in_array($val['CategoryId'],$merchantCategory )){ ?> class="cat_box" <?php } else {?> style="display:none;" class="cat_box" <?php } ?>>
									<img width="30" src="<?php echo $val['CategoryIcon']; ?>"/>
									<span class="cname"><?php echo ucfirst($val['CategoryName']);?></span>
									<a class="delete" title="Remove" href="javascript:void(0)" onclick="removeCategory(<?php echo $val['CategoryId']; ?>)">
										<i class="fa fa-trash-o "></i>
									</a>
								</span>
							<?php  } } ?>
							<input type="Hidden" id="categorySelected" name="categorySelected" value="<?php //if(isset($newCategory) && $newCategory>0) echo $newCategory;?>"/>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="box box-primary no-padding">						
						<div class="col-md-6  no-padding">
						
						<div class="box-header ">
							<h3 class="box-title">Icon Image</h3>
						</div>
						<div  class="box-body no-padding">
						<div class="form-group col-md-12">
							<div class="col-md-12 no-padding"> 
								<input type="file"  name="icon_photo" id="icon_photo" onchange="return ajaxAdminFileUploadProcess('icon_photo');"  /> 
								<p class="help-block">(Minimum dimension 100x100)</p>
								<span class="error" for="empty_icon_photo" generated="true" style="display: none">Icon is required</span>
							</div>
							<div class="col-sm-4 no-padding text-center" >
						      <div id="icon_photo_img" class="text-left">
								 <?php 
								 if(!empty($merchantInfo['Icon'])) { 
									 $image_path = $merchantInfo['Icon'];
								 ?>
								 <a href="<?php echo $image_path;?>" class="icon_fancybox" title="">
								  <img class="img_border" src="<?php echo $image_path;?>" width="75" height="75" alt="Image"/>
								  </a>
							 	<?php } ?>
							  </div>								
								<input type="Hidden" name="old_icon_photo" id="old_icon_photo" value="<?php if(!empty($merchantInfo['Icon'])) { echo $merchantInfo['Icon']; }?>" />
								<?php  if(isset($_POST['icon_photo_upload']) && $_POST['icon_photo_upload'] != ''){  ?><input type="Hidden" name="icon_photo_upload" id="icon_photo_upload" value="<?php  echo $_POST['icon_photo_upload'];  ?>"><?php  }  ?>
										<input type="Hidden" name="empty_icon_photo" id="empty_icon_photo" value="<?php  if(isset($image_path) && $image_path != '') { echo $image_path; }  ?>" />
										<input type="Hidden" name="name_icon_photo" id="name_icon_photo" value="<?php  if(isset($image_path) && $image_path != '') { echo $image_path; }  ?>" />				
							</div>
						</div>
						</div>
						</div>
						
						<div class="col-md-6  no-padding">
						<div class="box-header clear ">
							<h3 class="box-title">Merchant Image</h3>
						</div>
						<div  class="box-body no-padding">
						<div class="form-group col-md-12">
							<div class="col-md-12 no-padding"> 
								<input type="file"  name="merchant_photo" id="merchant_photo" onclick="" onchange="return ajaxAdminFileUploadProcess('merchant_photo');"   /> 
								<p class="help-block">(Minimum dimension 640x240)</p>
								<span class="error" for="empty_merchant_photo" generated="true" style="display: none">Image is required</span>
							</div>	
							<div class="col-sm-10 no-padding text-center"> 
							  <div id="merchant_photo_img" class="text-left">
								 <?php 
								 if(!empty($merchantInfo['Image'])) { 
								 	$cimage_path = $merchantInfo['Image'];
								 ?>
								  <a href="<?php echo $cimage_path;?>" class="image_fancybox" title="">
								  <img class="img_border" src="<?php echo $cimage_path;?>" width="200" height="100" alt="Image"/>
								  </a>
								<?php } ?>
							  </div>
								<input type="Hidden" name="old_merchant_photo" id="old_merchant_photo" value="<?php if(!empty($merchantInfo['Image'])) { echo $merchantInfo['Image']; } ?>" />
							<?php  if(isset($_POST['merchant_photo_upload']) && $_POST['merchant_photo_upload'] != ''){  ?>
							<input type="Hidden" name="merchant_photo_upload" id="icon_photo_upload" value="<?php  echo $_POST['merchant_photo_upload'];  ?>"><?php  }  ?>
							<input type="Hidden" name="empty_merchant_photo" id="empty_merchant_photo" value="<?php  if(isset($cimage_path) && $cimage_path != '') { echo $cimage_path; }  ?>" />
							<input type="Hidden" name="name_merchant_photo" id="name_merchant_photo" value="<?php  if(isset($cimage_path) && $cimage_path != '') { echo $cimage_path; }  ?>" />				

							</div>
						</div>	
						</div>	
						</div>	
					</div>
				</div>
							
				
				<div class="col-md-3">
					<div class="box box-primary no-padding">
						<div class="box-header ">
							<h3 class="box-title">Price Range</h3>
						</div>
						<div class="form-group col-md-12 error_msg_align">							
								<div class="col-sm-5 no-padding">
									<div class="col-sm-2 no-padding LH30">$</div>
									<div class="col-sm-10 no-padding"><input type="Text" onchange="price_val(this.value);" maxlength="7" name="min_price" value="<?php echo $min_val;?>" id="min_price" onkeypress="return isNumberKey(event);" class="form-control"></div>
								</div>
								<div class="col-sm-2 no-padding LH30" align="center"><strong>to</strong></div>
								<div class="col-sm-5 no-padding">
									<div class="col-sm-2 no-padding LH30">$</div>
									<div class="col-sm-10 no-padding"><input type="Text" onchange="price_val(this.value);" maxlength="7" name="max_price" value="<?php echo $max_val;?>" id="max_price" onkeypress="return isNumberKey(event);" class="form-control"></div>
								</div>
								<input  type="hidden" id="priceValidation" name="priceValidation" value="">
							
						</div>
					</div>
				</div>	
				
				<div class="col-md-3">
					<div class="box box-primary no-padding">
						<div class="box-header ">
							<h3 class="box-title">Payment Account</h3>
						</div>
						<div class="form-group col-md-12 error_msg_align ">
							<label class="pad5"></label>
							<button type="button" name="MangoPay" id="MangoPay" value="" class="btn bg-olive btn-md ">
								<i class="fa fa-plus"></i><strong> Add Mango Pay Account </strong>
							</button>
						</div>
	
					</div>
				</div>
				<div class="col-md-6">
					<div class="box box-primary no-padding">
						<div class="box-header ">
							<h3 class="box-title">Price Scheme</h3>
						</div>
						<div class="form-group col-md-12 ">
							<label class="col-sm-7 no-padding">Select Price Scheme</label>
							<div class="col-sm-5 no-padding""> 
							<select class="form-control" id="DiscountTier" name="DiscountTier">
								<option value="" >Select
								<?php if(isset($discountTierArray) && is_array($discountTierArray) && count($discountTierArray) > 0) {
										foreach($discountTierArray as $key=>$value){
								 ?>
								<option value="<?php echo $key; ?>" <?php if(isset($merchantInfo['DiscountTier']) &&  $merchantInfo['DiscountTier'] == $value.'%' ) echo 'selected';?>><?php echo $value.'%'; ?>
								<?php } } ?>
							</select>
							</div>
						</div>
						<?php if(isset($ProductsArray) && is_array($ProductsArray) && count($ProductsArray) > 0) { ?>
							<div class="form-group col-md-12 text-center">OR</div>
							<div class="form-group col-md-12 ">
								<label class="col-sm-7 no-padding">Select the product list or menu to be discounted (30% and the whole menu)</label>
								<div class="col-sm-5 no-padding"> 
									<select multiple class="form-control" id="Products_List" name="Products_List" onchange=""><!-- return getPrice(this);selectProduct(this.value); -->
										<option value="" >Select</option>
										<option value="all">Select All</option>
										<?php
												foreach($ProductsArray as $key=>$value){
										 ?>										
										<option value="<?php echo $value['id']; ?>"><?php echo $value['ItemName']; ?></option>
										<?php }  ?>
									</select>	
								</div>
							</div>
						<?php } ?>
					</div>
				</div>
				
				
		</div>
				<div class="footer col-md-12" align="center"> 
						<input type="submit" name="merchant_account_submit" id="merchant_account_submit" value="SAVE" class="btn btn-success ">
				</div>
			</form>
		 </div>
		</section>
		<?php footerLogin(); ?>
	<?php commonFooter(); ?>
</html>
<script type="text/javascript">
function price_val(val){
	$("#priceValidation").val(val);
}
  //document.ready
showCategory('<?php if(isset($newCategory) && $newCategory>0) echo $newCategory;?>');
$(document).ready(function() {
	$('.icon_fancybox').fancybox();	
	$('.image_fancybox').fancybox();	
	if($("#min_price").val() > 0)
		price_val($("#min_price").val());
	else
		price_val($("#max_price").val());
});


</script>
<script type="text/javascript">
$(document).ready(function() {
	$(".changePass").fancybox({
			scrolling: 'auto',			
			type: 'iframe',
			fitToView: true,
			autoSize: true
	});

});
</script>