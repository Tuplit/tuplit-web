<?php
require_once('includes/CommonIncludes.php');	
if(isset($_GET['reset']) && $_GET['reset'] == 1){
	$_SESSION['MerchantPortalAskPin']   =	0;
	$focus  = 'Pincode';
}
else{
	merchant_login_check();
	$focus  = 'ShopName';
}
if(isset($_GET['ajax']) && $_GET['ajax'] == 1) {
	echo $_POST['img'];
	echo $_POST['img'];
	die();
}

if(isset($_GET['imgid']) && !empty($_GET['imgid']) && isset($_GET['ext']) && !empty($_GET['ext'])) {
	$unpath	=	TEMP_IMAGE_PATH_REL.$_SESSION['merchantInfo']['MerchantId']."_".$_GET['imgid']."_myStore.".$_GET['ext'];
	if(unlink($unpath))
		echo 1;
	die();
}

$merchantCategory = array();
global $days_array;
$slideshowcount	=	0;
$remaining      = 	10;
$merchantId					= 	$_SESSION['merchantInfo']['MerchantId'];
$url						=	WEB_SERVICE.'v1/merchants/'.$merchantId."?From=0";
$curlMerchantResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201 && $curlMerchantResponse['merchant']['MerchantId'] != '' ) {
	$merchantInfo  			= 	$_SESSION['merchantDetailsInfo']   =	$curlMerchantResponse['merchant'];
	$newCategory			=	$merchantInfo['Category'];
}
if(isset($merchantInfo['PriceRange']) && $merchantInfo['PriceRange'] != ''){
  $prizeArray		=	explode(',',$merchantInfo['PriceRange']);
  if(isset( $prizeArray[0] ) &&  $prizeArray[0] !='')
  	$min_val		=	$prizeArray[0];
  if(isset( $prizeArray[1] ) &&  $prizeArray[1] !='')
  	$max_val		=	$prizeArray[1];
}
$url							=	WEB_SERVICE.'v1/categories/?From=1';
$curlCategoryResponse 			= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
if(isset($curlCategoryResponse) && is_array($curlCategoryResponse) && $curlCategoryResponse['meta']['code'] == 201 && is_array($curlCategoryResponse['categoryDetails']) ) {
	if(isset($curlCategoryResponse['categoryDetails']))
	$categories = $curlCategoryResponse['categoryDetails'];
	if(isset($_POST['categorySelected']))
		$newCategory			=	$_POST['categorySelected'];
	
} else if(isset($curlCategoryResponse['meta']['errorMessage']) && $curlCategoryResponse['meta']['errorMessage'] != '') {
		$errorMessage			=	$curlCategoryResponse['meta']['errorMessage'];
} else {
		$errorMessage			= 	"Bad Request";
}
if(isset($_POST) && !empty($_POST)){
	//echo'<pre>';print_r($_FILES);echo'</pre>';//die();
	//echo'<pre>';print_r($_POST);echo'</pre>';die();
	if(isset($_POST['ShopName']))
		$merchantInfo['CompanyName']		=	$_POST['ShopName'];
	if(isset($_POST['Email']))
		$merchantInfo['Email']				=	$_POST['Email'];
	if(isset($_POST['Street']))
		$merchantInfo['Address']			=	$_POST['Street'];
	if(isset($_POST['Phone']))
		$merchantInfo['PhoneNumber']		=	$_POST['Phone'];
	if(isset($_POST['Website']))
		$merchantInfo['WebsiteUrl']			=	$_POST['Website'];
	if(isset($_POST['MoreInfo']))
		$merchantInfo['Description']		=	$_POST['MoreInfo'];
	if(isset($_POST['ShopDescription']))
		$merchantInfo['ShortDescription']	=	$_POST['ShopDescription'];
	if(isset($_POST['categorySelected']))
		$newCategory						=	$_POST['categorySelected'];
	if(isset($_POST['City']))
		$merchantInfo['City']				=	$_POST['City'];
	if(isset($_POST['ZipCode']))
		$merchantInfo['PostCode']			=	$_POST['ZipCode'];
	if(isset($_POST['State']))
		$merchantInfo['State']				=	$_POST['State'];
	if(isset($_POST['Country']))
		$merchantInfo['Country']	=	$_POST['Country'];
	if(isset($_POST['Facebook']))
		$merchantInfo['FBId']				=	$_POST['Facebook'];
	if(isset($_POST['Twitter']))
		$merchantInfo['TwitterId']			=	$_POST['Twitter'];
	if(isset($_POST['DiscountTier']))
		$merchantInfo['DiscountTier']		=	$discountTierArray[$_POST['DiscountTier']].'%';
	if(isset($_POST['AutoLock']))
		$merchantInfo['AutoLock']			=	$_POST['AutoLock'];
	if(isset($_POST['PanelFeatures']))
		$merchantInfo['PanelFeatures']		=	$_POST['PanelFeatures'];
	if(isset($_POST['ProductVAT']))
		$merchantInfo['ProductVAT']			=	$_POST['ProductVAT'];
	if(isset($_POST['Emails']))
		$merchantInfo['Emails']				=	$_POST['Emails'];
	if(isset($_POST['Security']))
		$merchantInfo['Security']			=	$_POST['Security'];
	if(isset($_POST['EmailNotification']))
		$Notification			= 	1;
	else
		$Notification 			= 	0;
	//Opening Hours
	$openTiming = array();	
	if(isset($_POST['samehours']) && $_POST['samehours'] == 'on'){
		$openTiming[0]['id'] 				= $_POST['id_0'];
		$openTiming[0]['OpeningDay'] 		= 0;
		$openTiming[0]['DateCreated'] 		= $merchantInfo['OpeningHours'][0]['DateCreated'];
		$openTiming[0]['fkMerchantId'] 		= $merchantInfo['OpeningHours'][0]['fkMerchantId'];
		$openTiming[0]['Start'] 			= $_POST['from1_0'];
		$openTiming[0]['End'] 				= $_POST['to1_0'];
		$openTiming[0]['DateType'] 			= '1';
		for($t=1;$t<=6;$t++) {
			$openTiming[$t]['id'] 			= $_POST['id_'.$t];
			$openTiming[$t]['OpeningDay'] 	= $t;
			$openTiming[$t]['DateCreated'] 	= $merchantInfo['OpeningHours'][$t]['DateCreated'];
			$openTiming[$t]['fkMerchantId'] = $merchantInfo['OpeningHours'][$t]['fkMerchantId'];
			$openTiming[$t]['id'] 			= $_POST['id_'.$t];
			$openTiming[$t]['Start'] 		= $_POST['from1_'.$t];
			$openTiming[$t]['End'] 			= $_POST['to1_'.$t];
			$openTiming[$t]['DateType'] 	= '0';
		}
	}
	else {
		for($t=0;$t<=6;$t++) {
			$openTiming[$t]['id'] 			= $_POST['id_'.$t];
			$openTiming[$t]['OpeningDay'] 	= $t;
			$openTiming[$t]['DateCreated'] 	= $merchantInfo['OpeningHours'][$t]['DateCreated'];
			$openTiming[$t]['fkMerchantId'] = $merchantInfo['OpeningHours'][$t]['fkMerchantId'];
			$openTiming[$t]['Start'] 		= $_POST['from1_'.$t];
			$openTiming[$t]['End'] 			= $_POST['to1_'.$t];
			$openTiming[$t]['DateType'] 	= '0';
		}
	}
	$merchantInfo['OpeningHours']	=	$openTiming;	
	if(isset($_POST['min_price']) && $_POST['min_price'] != '')
		$min_val		=	$_POST['min_price'];
	if(isset($_POST['max_price']) && $_POST['max_price'] != '')
		$max_val		=	$_POST['max_price'];
	if($min_val != '' && $max_val != '')
		$prizeRange		=	$min_val.','.$max_val;
	$iconPath	= $imagePath	= $bimagePath = '';
	if (isset($_POST['icon_photo_upload']) && !empty($_POST['icon_photo_upload'])) {
		$iconPath		=	TEMP_IMAGE_PATH_REL.$_POST['icon_photo_upload'];
		if(isset($merchantInfo['Icon']) && $merchantInfo['Icon'] != ''){
			if(!SERVER){
				if(file_exists(MERCHANT_ICONS_IMAGE_PATH_REL.$merchantInfo['Icon']))
					unlink(MERCHANT_ICONS_IMAGE_PATH_REL .$merchantInfo['Icon']);
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
		}
		$merchantInfo['Image']	=	TEMP_IMAGE_PATH.$_POST['merchant_photo_upload'];
	}
	if (isset($_POST['background_photo_upload']) && !empty($_POST['background_photo_upload'])) {
		$bimagePath		=	TEMP_IMAGE_PATH_REL.$_POST['background_photo_upload'];
		if(isset($merchantInfo['Background']) && $merchantInfo['Background'] != ''){
			if(!SERVER){
				if(file_exists(MERCHANT_BACKGROUND_IMAGE_PATH_REL.basename($merchantInfo['Background'])))
					unlink(MERCHANT_BACKGROUND_IMAGE_PATH_REL . basename($merchantInfo['Background']));
			}
		}
		$merchantInfo['Background']	=	TEMP_IMAGE_PATH.$_POST['background_photo_upload'];
	}
	$merchantInfo['OpeningHours']	=	$openTiming;
	$data	=	array(
					'CompanyName' 		=> 	$_POST['ShopName'],
					'Email' 			=> 	$_POST['Email'],					
					'PhoneNumber' 		=> 	$_POST['Phone'],
					'WebsiteUrl' 		=> 	$_POST['Website'],
					'ShortDescription' 	=> 	$_POST['ShopDescription'],
					'Description' 		=> 	$_POST['MoreInfo'],
					'OpeningHours' 		=> 	$openTiming,
					'IconPhoto' 		=> 	$iconPath,
					'MerchantPhoto' 	=> 	$imagePath,
					'BackgroundPhoto' 	=> 	$bimagePath,
					'IconExist'			=> 	$_POST['old_icon_photo'],
					'MerchantExist'		=> 	$_POST['old_merchant_photo'],
					'BackgroundExist'	=> 	$_POST['old_background_photo'],
					'PriceRange' 		=> 	$prizeRange,
					'Categories' 		=> 	$_POST['categorySelected'],
					'DiscountTier' 		=> 	$_POST['PriceScheme'],
					'Street' 			=> 	$_POST['Street'],
					'City'				=> 	$_POST['City'],
					'State'				=> 	$_POST['State'],
					'ZipCode'			=> 	$_POST['ZipCode'],
					'Country'			=> 	$_POST['Country'],
					'FBId'				=> 	$_POST['Facebook'],
					'TwitterId'			=> 	$_POST['Twitter'],
					'uploadimage'		=>	$_POST['uploadimage'],
					'deleteimage'		=>	$_POST['deleteimage'],
					'image_data'		=>	$_POST['image_data'],
					'AutoLock'			=>	$_POST['AutoLock'],
					'ProductVAT'		=>	$_POST['ProductVAT'],
					'PanelFeatures'		=>	$_POST['PanelFeatures'],
					'Emails'			=>	$_POST['Emails'],
					'Security'			=>	$_POST['Security'],
					'Pincode'			=>	$_POST['Pincode'],
					'Password'			=>	$_POST['Password'],
					'OrderMail'			=>  $Notification
				);
	$url			=	WEB_SERVICE.'v1/merchants/';
	$method			=	'PUT';
	/*echo $url."<br>";
	echo json_encode($data); die();*/
	$curlResponse	=	curlRequest($url,$method,json_encode($data), $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlResponse) && is_array($curlResponse) && $curlResponse['meta']['code'] == 201) {
		$merchantId					= 	$_SESSION['merchantInfo']['MerchantId'];
		$url						=	WEB_SERVICE.'v1/merchants/'.$merchantId.'?From=0';
		$curlMerchantResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
		if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201 && $curlMerchantResponse['merchant']['MerchantId'] != '' ) 
		{
			$merchantInfo  						= 	$curlMerchantResponse['merchant'];
			$_SESSION['merchantDetailsInfo']	=	$merchantInfo;
			$newCategory						=	$merchantInfo['Category'];
		}
		$_SESSION['MerchantPortalAccessTime']   =	time();
		$successMessage	=	$curlResponse['notifications'][0];
	} else if(isset($curlResponse['meta']['errorMessage']) && $curlResponse['meta']['errorMessage'] != '') {
		$errorMessage		=	$curlResponse['meta']['errorMessage'];
	} else {
		$errorMessage		= 	"Bad Request";
	}
}
if(isset($merchantInfo['OpeningHours']) && !empty($merchantInfo['OpeningHours'])) {
	$merchantInfo['OpeningHours']	=	formOpeningHours($merchantInfo['OpeningHours']);
}

//print_r($merchantInfo['OpeningHours'][0]['DateType']);
//slideshow images
$url							=	WEB_SERVICE.'v1/merchants/slideshows/';
$curlSlideshowsResponse 			= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
if(isset($curlSlideshowsResponse) && is_array($curlSlideshowsResponse) && $curlSlideshowsResponse['meta']['code'] == 201 && is_array($curlSlideshowsResponse['slideshows']) ) {
	$slideshows					=	$curlSlideshowsResponse['slideshows'];	
	$slideshowcount				=	count($slideshows);
} 

if(isset($merchantInfo['Category']) && !empty($merchantInfo['Category'])) {
	$merchantCategory			= 	explode(',',$merchantInfo['Category']);
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
$show_cat = 0;
if(isset($newCategory) && $newCategory>0) 
$show_cat = $newCategory;
?>
<?php top_header(); ?>
<body class="skin-blue fixed body_height" onload="fieldfocus('<?php echo $focus;?>');">
		<div height="100%" width="100%" id="fancybox-loading" style="display:none;"><img src="./webresources/images/fetch_loader.gif"></img></div>
		<section class="content mystore">
			<div class="col-xs-12">
				<section class="content-header">
					<h1 style="margin-top:0px;margin-bottom:20px;">My Store</h1>
				</section>
				<?php if(isset($msg) && $msg != '') { ?>
					<div align="center" class="alert <?php  echo $class;  ?> alert-dismissable col-xs-12 col-sm-5 "><i class="fa <?php  echo $class_icon;  ?>"></i>  <?php echo $msg; ?></div>
				<?php } ?>
				<form action="" name="Settings_form" id="Settings_form"  method="post" onSubmit="return validateOpenHours();">
					<div class="col-md-12 box box-center">
						<div class=" box-primary my_store_form no-padding">
							<div class="col-md-6" style="padding-top:20px;padding-bottom:20px;">
								<div class="col-sm-6">
									<label class="control-label" ><h3 class="no-bottom">Shop Name</h3></label>
									<p class="help-block col-sm-12 no-padding">Name is visible on your card in mobile app</p>
								</div>
								<div class="col-sm-6 mystore_pad"><input type="text" name="ShopName" class="form-control valid"  id="ShopName" value="<?php if(isset($merchantInfo['CompanyName']) && !empty($merchantInfo['CompanyName'])) echo $merchantInfo['CompanyName'];?>"></div>
								
								<div class="col-sm-12">
									<label class="col-sm-12 col-md-12 control-label no-padding border-right"><h3 class="no-bottom"><span>Shop Description / Slogan</span></h3></label>
									<p class="help-block col-sm-12 no-padding">Slogan is shown under the shop name (max. 80 characters)</p>
								</div>
								<div class="col-sm-12">
									<input type="text" name="ShopDescription"  id="ShopDescription" maxlength="80" style="margin-bottom:20px;" placeholder="e.g: The best burger in town!" class="form-control valid" value="<?php if(isset($merchantInfo['ShortDescription']) && !empty($merchantInfo['ShortDescription'])) echo $merchantInfo['ShortDescription'];?>">
								</div>
								<div class="col-sm-6"><label class="col-sm-8 control-label no-padding"><h3>Category</h3></label></div>
								<div class="col-sm-6 mystore_pad">
									<div class="custom-select">
										<select name="Category" id="Category" class="form-control" onchange="showCategory(this.value)">
											<option value="">Select</option>	
											<?php if(isset($categories) && !empty($categories)) {
												foreach($categories as $key=>$val) {
											?>
											<option value="<?php echo $val['CategoryId'];?>"  style="background-image:url(<?php echo $val['CategoryIcon']; ?>);"><?php echo trim(ucfirst($val['CategoryName']));?></option>
											<?php } }  ?>
										</select>
										<span id="njkj"></span>
									</div>
								</div>
								<div class="col-xs-12">
									<?php if(isset($categories) && !empty($categories)) {
											foreach($categories as $key=>$val) {
									?>
									<span id="cat_id_<?php echo $val['CategoryId']; ?>" <?php if(in_array($val['CategoryId'],$merchantCategory )){ ?> class="cat_box" <?php } else {?> style="display:none;" class="cat_box" <?php } ?>>
										<img width="30" src="<?php echo $val['CategoryIcon']; ?>"/>
										<span class="cname"><?php echo ucfirst($val['CategoryName']);?></span>
										<a class="delete" title="Remove" href="javascript:void(0)" onclick="removeCategory(<?php echo $val['CategoryId']; ?>,'<?php echo $val['CategoryIcon']; ?>')">
											<i class="fa fa-trash-o "></i>
										</a>
									</span>
									<?php  } } ?>
									<input type="Hidden" id="categorySelected" name="categorySelected" value="<?php if(isset($newCategory) && $newCategory>0) echo $newCategory;?>"/>
								</div>
								<div class="col-sm-6"><label class="col-sm-8 col-xs-12  control-label no-padding"><h3>Price Range</h3></label></div>
 								<div class="col-sm-6 mystore_pad">
									<span class="col-sm-12 col-xs-12  control-label no-padding">
										<div class="col-xs-5 col-md-5 no-padding">
											<div class="col-xs-2 col-md-2 no-padding LH30"><?php echo '&pound;';?></div>
											<div class="col-xs-9 col-md-10 no-padding"><input type="Text" onchange="price_val(this.value);" maxlength="7" name="min_price" value="<?php if(isset($min_val)) echo $min_val;?>" id="min_price" onkeypress="return isNumberKey_price(event);" class="form-control"></div>
										</div>
										<div class="col-xs-1 col-md-2 no-padding LH30" align="center"><strong>to</strong></div>
										<div class="col-xs-5 col-md-5 no-padding">
											<div class="col-xs-2 col-md-2 no-padding LH30"><?php echo '&pound;';?></div>
											<div class="col-xs-9 col-md-10 no-padding"><input type="Text" onchange="price_val(this.value);" maxlength="7" name="max_price" value="<?php if(isset($min_val)) echo $max_val;?>" id="max_price" onkeypress="return isNumberKey_price(event);" class="form-control"></div>
										</div>
										<input  type="hidden" id="priceValidation" name="priceValidation" value="">
									</span>
								</div>								
								<!-- <div class="col-sm-6"><label class="col-sm-8 control-label no-padding"><h3>Discount Scheme</h3></label></div> -->
								<!-- <div class="col-sm-6 mystore_pad">
									<select class="form-control" id="DiscountTier" name="DiscountTier" onclick="selectPrice(this.value,'<?php if(isset($ProductsArray) && count($ProductsArray) > 0) echo "1"; else echo "0"; ?>');">
													<option value="" >Select
													<?php if(isset($discountTierArray) && is_array($discountTierArray) && count($discountTierArray) > 0) {
															foreach($discountTierArray as $key=>$value){
													 ?>
													<option value="<?php echo $key; ?>" <?php if(isset($merchantInfo['DiscountTier']) &&  $merchantInfo['DiscountTier'] == $value.'%' ) echo 'selected';?>><?php echo $value.'%'; ?>
													<?php } } ?>
												</select>
								</div> -->
								
								<div class="col-sm-6 clear">
									<label class="col-sm-12 col-md-12 col-lg-8 col-xs-12 control-label no-padding"><h3 class="no-bottom">Logo</h3></label>
									<p class="help-block col-sm-12 no-padding">Please upload only JPG or PNG files.<br>The best resolution is 100X100 pixels.</p>
								</div>
								<div class="col-sm-6 mystore_pad">
									<div class="col-xs-12 no-padding text-left" >
										 <?php 
										 if(!empty($merchantInfo['Icon'])) { 
											 $image_path = $merchantInfo['Icon'];
										 ?>
										 <div id="icon_photo_img" class="col-xs-6 col-sm-6 col-md-6 col-lg-6 no-padding image-border">
											 <a onclick="return loaded;" href="<?php echo $image_path;?>" class="icon_fancybox" title="">
											  <img class="photo_img_border" src="<?php echo $image_path;?>" width="86" height="86" alt="Image"/>
											  </a>
										  </div>
										<?php } else { ?>
											<div id="icon_photo_img" class="col-xs-6 col-sm-6 col-md-6 col-lg-6 no-padding image-border upload_img"  style="float:left;"></div>
										<?php } ?>
									  <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 no-padding drag_pos LH80">UPLOAD <input type="file"  name="icon_photo" id="icon_photo" title="Upload"/><br>
										<!--<span class="error" for="empty_merchant_photo" generated="true" style="display: none">Logo is required</span>-->
									</div>								
										<input type="Hidden" name="old_icon_photo" id="old_icon_photo" value="<?php if(!empty($merchantInfo['Icon'])) { echo $merchantInfo['Icon']; }?>" />
										<?php  if(isset($_POST['icon_photo_upload']) && $_POST['icon_photo_upload'] != ''){  ?><input type="Hidden" name="icon_photo_upload" id="icon_photo_upload" value="<?php  echo $_POST['icon_photo_upload'];  ?>"><?php  }  ?>
												<input type="Hidden" name="empty_icon_photo" id="empty_icon_photo" value="<?php  if(isset($image_path) && $image_path != '') { echo $image_path; }  ?>" />
												<input type="Hidden" name="name_icon_photo" id="name_icon_photo" value="<?php  if(isset($image_path) && $image_path != '') { echo $image_path; }  ?>" />				
									</div>
								</div>
								<div class="col-sm-6">
									<label class="col-sm-12 col-md-12 col-lg-8 col-xs-12 control-label no-padding"><h3 class="no-bottom">Image</h3></label>
									<p class="help-block col-sm-12 no-padding">Please upload only JPG or PNG files.<br>The best resolution is 260X640 pixels.</p>
								</div>
								<div class="col-sm-6 mystore_pad">
									<div class="col-xs-12 no-padding text-left" >
										<?php 
										if(!empty($merchantInfo['Image'])) { 
											$cimage_path = $merchantInfo['Image'];
										?>
										<div id="merchant_photo_img" class="col-xs-6 col-sm-6 col-md-6 col-lg-6 no-padding image-border">
											<a onclick="return loaded;" href="<?php echo $cimage_path;?>" class="image_fancybox" title="">
												<img class="" src="<?php echo $cimage_path;?>" width="100" height="100" alt="Image"/>
											</a>
										</div>
										<?php } else { ?>
											<div id="merchant_photo_img" class="col-xs-6 col-sm-6 col-md-6 col-lg-6 no-padding image-border upload_img" style="float:left;"></div>
										<?php } ?>										  
										<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 no-padding drag_pos LH80">UPLOAD<input type="file"  name="merchant_photo" id="merchant_photo"/><br>
											<!--<span class="error" for="empty_merchant_photo" generated="true" style="display: none">Image is required</span>-->
										</div>
										<input type="Hidden" name="old_merchant_photo" id="old_merchant_photo" value="<?php if(!empty($merchantInfo['Image'])) { echo $merchantInfo['Image']; } ?>" />
										<?php  if(isset($_POST['merchant_photo_upload']) && $_POST['merchant_photo_upload'] != ''){  ?>
										<input type="Hidden" name="merchant_photo_upload" id="icon_photo_upload" value="<?php  echo $_POST['merchant_photo_upload'];  ?>"><?php  }  ?>
										<input type="Hidden" name="empty_merchant_photo" id="empty_merchant_photo" value="<?php  if(isset($cimage_path) && $cimage_path != '') { echo $cimage_path; }  ?>" />
										<input type="Hidden" name="name_merchant_photo" id="name_merchant_photo" value="<?php  if(isset($cimage_path) && $cimage_path != '') { echo $cimage_path; }  ?>" />				
									</div>
								</div>
								
								<div class="col-sm-6">
									<label class="col-sm-12 col-md-12 col-lg-8 col-xs-12  control-label no-padding"><h3 class="no-bottom">Background Image</h3></label>
									<p class="help-block col-sm-12 no-padding">Please upload only JPG or PNG files.<br>The best resolution is 100X100 pixels.</p>
								</div>
								<div class="col-xs-12 col-sm-6 mystore_pad">
									<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 no-padding text-left image-border" >
										  <div id="background_photo_img" class="upload_img">
											 <?php 
											 if(!empty($merchantInfo['Background'])) { 
												$bimage_path = $merchantInfo['Background'];
												?>
											  <a onclick="return loaded;" href="<?php echo $bimage_path;?>" class="image_fancybox" title="">
											  <img class="" src="<?php echo $bimage_path;?>" width="100" height="100" alt="Image"/>
											  </a>
											<?php } ?>
										  </div>
										<input type="Hidden" name="old_background_photo" id="old_background_photo" value="<?php if(!empty($merchantInfo['Background'])) { echo $merchantInfo['Background']; } ?>" />
										<?php  if(isset($_POST['background_photo_upload']) && $_POST['background_photo_upload'] != ''){  ?>
										<input type="Hidden" name="background_photo_upload" id="background_photo_upload" value="<?php  echo $_POST['background_photo_upload'];  ?>"><?php  }  ?>
										<input type="Hidden" name="empty_background_photo" id="empty_background_photo" value="<?php  if(isset($bimage_path) && $bimage_path != '') { echo $bimage_path; }  ?>" />
										<input type="Hidden" name="name_background_photo" id="name_background_photo" value="<?php  if(isset($bimage_path) && $bimage_path != '') { echo $bimage_path; }  ?>" />				
									</div>
									<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 no-padding drag_pos LH80">UPLOAD<input type="file"  name="background_photo" id="background_photo"/><br>
										<!--<span class="error" for="empty_merchant_photo" generated="true" style="display: none">Image is required</span>-->
									</div>
									
								</div>
														
								<div class="col-xs-12 col-sm-12">
									<label class="col-sm-12 control-label no-padding "><h3 class="no-bottom"><span>Slideshow pictures</span></h3></label>
									<p class="help-block no-padding">Upload up to 10 pictures (optimum resolution is 260x640 pixels, bigger images will be scaled down
										automatically. Please upload only JPG or PNG files.)</p>
								</div>
								<div class="col-sm-12">
									<div class="row">
								<?php $i = 0;	if(isset($slideshows) && count($slideshows) > 0) { 
											$slideshowcount	=	count($slideshows);
											//echo "---------";
											//print_r($slideshows);
											foreach($slideshows as $val) { 
											$i = $i + 1;	
										?>
										<div class="col-sm-6 col-xs-12 form-group">
											<div class="col-xs-12 no-padding">
												<div class="col-xs-6 col-md-6 no-padding image-border" align="left" id="image_<?php echo $i; ?>">
													<div  class="photo_gray_bg" style="background-color:#fff;"> 
														<a onclick="return loaded;" href="<?php echo $val['imagePath']; ?>" class="image_fancybox" title="">
														<img style="vertical-align:top" class="" src="<?php echo $val['imagePath']; ?>" width="76" height="40" alt="">
														</a>
													</div>
												</div>
												<div class="col-xs-6 col-md-6 no-padding" align="left" id="default_<?php echo $i; ?>"  style="display:none;">
													<img style="vertical-align:top" class="resize" id="imgdrag" src="<?php SITE_PATH;?>webresources/images/no_photo.png" alt="">
												</div>
												<div class="col-lg-6 col-sm-6 col-xs-6 no-padding" align="left" id="upload_<?php echo $i; ?>"  style="display:none;">
													<div class="drag_pos pull-left" id="holder" style="font-size:11px !important;">
														UPLOAD
														<input type="file" name="myStore_<?php echo $i; ?>" id="myStore_<?php echo $i; ?>" title="upload">
													</div>
												</div>
												<div class="col-lg-6 col-sm-6 col-xs-6" align="left" id="delete_<?php echo $i; ?>">
													<a  href="javascript:void(0);" title="Delete" class="delete_image col-xs-12 no-padding" onclick="return deleteMyStoreImage('<?php echo $i;?>')"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;DELETE</a>
												</div>
											</div>
											<input type="hidden" id="uploadimage_<?php echo $i; ?>" name="uploadimage[]" value="" />
											<input type="hidden" id="deleteimage_<?php echo $i; ?>" name="deleteimage[]" value="" />
											<input type="hidden" id="image_Id_<?php echo $i; ?>" name="image_Id[]" value="<?php echo $val['id']; ?>" />
											<input type="hidden" id="image_data_<?php echo $i; ?>" name="image_data[]" value="<?php echo $val['SlideshowName']; ?>" />
										</div>
										<?php }	}?>
										<?php for($n=$slideshowcount;$n<=9;$n++){ $i = $i + 1 ?>
										<div class="col-sm-6 col-xs-12 form-group">
											<div class="col-xs-12 no-padding">
												<div class="col-xs-6 col-md-6 no-padding" align="left" id="image_<?php echo $i; ?>"   style="display:none;">
													<div  class="photo_gray_bg" style="background-color:#fff;"> 
														<a onclick="return loaded;" href="<?php if(isset($val['imagePath']) && !empty($val['imagePath']))echo $val['imagePath']; else echo '';  ?>" class="image_fancybox" title="">
														<img style="vertical-align:top" class="" src="" width="76" height="40" alt="">
														</a>
													</div>
												</div>
												<div class="col-xs-6 col-md-6 no-padding" align="left" id="default_<?php echo $i; ?>">
													<img style="vertical-align:top" class="resize" id="imgdrag" src="<?php SITE_PATH;?>webresources/images/no_photo.png" alt="">
												</div>
												<div class="col-lg-6 col-sm-6 col-xs-6 no-padding" align="left" id="upload_<?php echo $i; ?>">
													<div class="drag_pos pull-left" id="holder" style="font-size:11px !important;">
														UPLOAD
														<input type="file" name="myStore_<?php echo $i; ?>" id="myStore_<?php echo $i; ?>" title="upload">
													</div>
												</div>
												<div class="col-lg-6 col-sm-6 col-xs-6" align="left" id="delete_<?php echo $i; ?>"   style="display:none;">
													<a  href="javascript:void(0);" title="Delete" class="delete_image col-xs-12 no-padding" onclick="return deleteMyStoreImage('<?php echo $i; ?>')"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;DELETE</a>
												</div>
											</div>
											<input type="hidden" id="uploadimage_<?php echo $i; ?>" name="uploadimage[]" value="" />
											<input type="hidden" id="deleteimage_<?php echo $i; ?>" name="deleteimage[]" value="" />
											<input type="hidden" id="image_Id_<?php echo $i; ?>" name="image_Id[]" value="" />
											<input type="hidden" id="image_data_<?php echo $i; ?>" name="image_data[]" value="" />
										</div>		
										<?php } ?>						
									</div>
								</div>
								
								<!-- Price Scheme -->
								<div class="col-sm-9">
									<label class="control-label"><h3 class="no-bottom">Discount Scheme</h3></label>
									<p class="help-block col-sm-12 no-padding">Discount Scheme defines how much will all product be discounted</p>
								</div>
								<div class="col-sm-3">
									<div class="custom-select top-margin24">
										<select name="PriceScheme" id="PriceScheme" class="form-control">
											<option value="">Select</option>	
											<?php if(isset($discountTierArray) && is_array($discountTierArray) && count($discountTierArray) > 0) {
												foreach($discountTierArray as $key=>$val) {
											?>
											<option value="<?php echo $key; ?>" <?php if(isset($merchantInfo['DiscountTier']) &&  $merchantInfo['DiscountTier'] == $val.'%' ) echo 'selected';?>><?php echo $val.'%'; ?>
											<?php } } ?>
										</select>
										<span id="njkj"></span>
									</div>
								</div>
								<!-- Price Scheme -->
								
								<!-- Product VAT% -->
								<div class="col-sm-9">
									<label class="control-label"><h3 class="no-bottom">Product VAT %</h3></label>
									<p class="help-block no-padding">Taxes percent included to each product price</p>
								</div>
								<div class="col-sm-3">
									<div class="custom-select top-margin24">
										<select name="ProductVAT" id="ProductVAT" class="form-control">
											<option value="">Select</option>	
											<?php if(isset($ProductVAT) && is_array($ProductVAT) && count($ProductVAT) > 0) {
												foreach($ProductVAT as $key=>$val) {
											?>
											<option value="<?php echo $key;?>" <?php if(isset($merchantInfo['ProductVAT']) &&  $merchantInfo['ProductVAT'] == $key ) echo 'selected';?> ><?php echo $val.'%'; ?></option>
											
											<?php } } ?>
										</select>
										<span id="njkj"></span>
									</div>
								</div>
								<!-- Product VAT% -->
								
								<!-- Panel Features -->
								<div class="col-sm-12">
									<label class="col-sm-12 col-md-12 control-label no-padding"><h3 class="no-bottom">Panel Features and Info</h3></label>
									<p class="help-block col-sm-12 no-padding">Information about this part of the settings page(only if needed)</p>
								</div>
								<div class="col-sm-12">
									<textarea type="text" name="PanelFeatures"  id="PanelFeatures" rows="4" cols="3" maxlength="80" placeholder="Please specify the content" class="form-control valid"><?php  if(isset($merchantInfo['PanelFeatures']) &&  $merchantInfo['PanelFeatures'] != '' ) echo  $merchantInfo['PanelFeatures'];?></textarea>
								</div>
								<!-- Panel Features -->
								
								<!-- Communications -->
								<div class="col-sm-12">
									<label class="col-sm-12 col-md-12 control-label no-padding border-right"><h3><span>Communication</span></h3></label>
								</div>
								<div class="col-sm-12">
									<div class="col-sm-6 col-xs-12 form-group no-padding" style="">
									<div class="col-xs-12 no-padding">
										<div class="col-xs-8 col-sm-6 col-md-6 no-padding" style="line-height:28px;">
											<b>Order Email Notification</b>
										</div>
										<div class="col-xs-4 col-sm-6 col-md-6 no-padding">
												<span class="email_notification">
												<input checked="checked" style="display: none;" id="EmailNotification" name="EmailNotification" type="checkbox">
												<input type="hidden" id="Notification_val" name="Notification_val" value="<?php if(isset($Notification) && $Notification == '1') echo "1"; else if(isset($Notification) && $Notification == '0') echo "0"; else echo "1"; ?>"></span>
										</div>
									</div>
									</div>
								</div>
								<!-- Communications -->
								
								<!-- Emails -->
								<div class="col-sm-12">
									<label class="col-sm-12 col-md-12 control-label no-padding border-right"><h3><span>Emails</span></h3></label>
								</div>
								<div class="col-sm-12">
									<input type="text" name="Emails" class="form-control valid"  id="Emails" placeholder="Please type in all email addresses dividing tem by coma(,)" value="<?php if(isset($merchantInfo['Emails']) &&  $merchantInfo['Emails'] != '' ) echo  $merchantInfo['Emails'];?>">
								</div>
								<!-- Emails -->
								<div class="col-sm-12">
									<div class=" box-primary no-padding">
										<label class="col-sm-12 col-md-12 control-label no-padding border-right"><h3><span>Payment Account</span></h3></label>
										<?php if(isset($merchantInfo['MangoPayUniqueId']) && $merchantInfo['MangoPayUniqueId']!= ''){?>
										<div class="form-group col-md-12  no-padding" style="">
											<h4 class="box-title text-teal" style="margin-bottom:0px;"><strong>Connected with Mangopay</strong></h4>
										</div>
										<div class="form-group col-md-12  no-padding error_msg_align LH34 Mango_Pay">
											<label class="pad5"></label><a onclick="return loaded;" href="MangoPayAccount?MId=<?php echo base64_encode($merchantInfo['MangoPayUniqueId']);?>" class="MangoPayEdit">
											<button type="button" name="MangoPay" id="MangoPay" value="" class="btn bg-olive btn-md " title="Edit Mangopay Account">
												Edit Mangopay Account
											</button></a> 
										</div>
										<?php } else {?>
										<div class="form-group col-md-12 no-padding error_msg_align Mango_Pay">
											<label class="pad5"></label><a onclick="return loaded;" href="MangoPayAccount" class="MangoPay">
											<button type="button" name="MangoPay" id="MangoPay" value="" class="btn bg-olive btn-md ">
												<i class="fa fa-plus"></i> Add Mangopay Account
											</button></a> 
										</div>
										<?php } ?>
										<?php if( isset($merchantInfo['MangoPayUniqueId']) && $merchantInfo['MangoPayUniqueId']!= ''){?>
										<div class="form-group col-md-12  no-padding error_msg_align LH34 Mango_Pay">
											<label class="pad5"></label><a onclick="return loaded;" href="MangoPayBankAccount?MId=<?php echo base64_encode($merchantInfo['MangoPayUniqueId']);?>&Type=1" class="MangoPayEdit">
											<button type="button" name="MangoPay" id="MangoPay" value="" class="btn bg-olive btn-md " title="Add Bank Account">
												<i class="fa fa-plus"></i> Add Bank Account
											</button></a> 
										</div>
										<div class="form-group col-md-12  no-padding error_msg_align LH34 Mango_Pay">
											<label class="pad5"></label><a onclick="return loaded;" href="MangoPayBankAccount?MId=<?php echo base64_encode($merchantInfo['MangoPayUniqueId']);?>&WalletId=<?php echo base64_encode($merchantInfo['WalletId']);?>&Type=2" id="MangoPayEdit">
											<button type="button" name="MangoPay" id="MangoPay" value="" class="btn bg-olive btn-md " title="Transfer to Bank Account">
												<i class="fa fa-long-arrow-right"></i> Transfer to Bank Account
											</button></a> 
										</div>
										<div class="form-group col-md-12  no-padding error_msg_align LH34 Mango_Pay">
											<label class="pad5"></label><a onclick="return loaded;" href="MerchantBalance?MId=<?php echo base64_encode($merchantInfo['MangoPayUniqueId']);?>&WalletId=<?php echo base64_encode($merchantInfo['WalletId']);?>" class="MangoPayEdit">
											<button type="button" name="MangoPay" id="MangoPay" value="" class="btn bg-olive btn-md" title="Current Wallet Balance">
												<i class="fa fa-money"></i> Current Wallet Balance
											</button></a> 
										</div>
										<?php } ?>
									</div>	
								</div>
							</div>
							<div class="left_border">&nbsp;</div>
							<div class="col-md-6" style="padding-top:20px;padding-bottom:20px;">
								<div class="col-sm-12">
									<label class="col-sm-12 control-label no-padding"><h3 class="no-bottom"><span>More Info</span></h3></label>
									<p class="help-block no-padding">More info is text shown on your details page. Tell more about your venue and why should your 
										potential customers visit and shop in your venue. Up to 300 characters.</p>
								</div>
								<div class="col-sm-12"><textarea type="text" name="MoreInfo" id="MoreInfo" maxlength="300" style="margin-bottom:20px;" placeholder="e.g. Family-owned restaurant since 1965. We prepare food only from fresh local ingredients." class="form-control valid more_info" ><?php if(isset($merchantInfo['Description']) && !empty($merchantInfo['Description'])) echo $merchantInfo['Description'];?></textarea></div>
								
								<div class="form-group col-xs-12 col-md-12">
									<label class="col-sm-12 col-md-12 control-label no-padding border-right"><h3 style="margin-bottom:15px;"><span>Business hours</span></h3><!-- <em></em> --></label>
									<!-- <p class="help-block col-sm-12 no-padding">Open Hours leave as empty for not service</p> -->
									<?php 
									if(isset($days_array) && count($days_array)>0) {
									foreach($days_array as $key=>$val){ ?>
									<div class="col-xs-12 no-padding form-group change_pwd_code <?php if($key != 0) echo "rowHide";?>"  <?php if(isset($merchantInfo['OpeningHours'][0]['DateType']) && $merchantInfo['OpeningHours'][0]['DateType'] == '1' && $key != 0) echo 'style="display:none;"'; ?>>
										<?php if($key == 0) { ?>
											<!-- <div class="col-xs-6 col-sm-4 col-xs-6 no-padding LH30">From :</div> -->
											<!-- <div class="col-xs-5 col-sm-4 col-xs-6 no-padding LH30">To :</div> -->
										<?php } ?>
										<div class="col-sm-3  col-lg-4 col-xs-12  no-padding LH30"><span class="<?php if($key == 0) echo "rowshow";?>"><?php if(isset($merchantInfo['OpeningHours'][0]['DateType']) && $merchantInfo['OpeningHours'][0]['DateType'] == '1' && $key == 0) echo "Monday - Sunday"; else echo $val.""; ?></span></div>
										<div class="col-sm-3 col-lg-3 col-xs-6 select_sm no-left-pad">
											<input type="text" rowid="<?php echo $key; ?>" class="form-control timepicker col-xs-6" id="from1_<?php echo $key; ?>" name="from1_<?php echo $key; ?>" value="<?php if(isset($merchantInfo['OpeningHours'][$key]['Start']['fromTime'])) echo $merchantInfo['OpeningHours'][$key]['Start']['fromTime']; ?>" readonly>
											<input type="hidden" rowid="<?php echo $key; ?>" class="timepicker" id="valid_<?php echo $key; ?>" value="">
											<input type="hidden" id="row_<?php echo $key; ?>" name="row_<?php echo $key; ?>" value="<?php if(!empty($merchantInfo['OpeningHours'][$key]['Start']['fromTime']) || !empty($merchantInfo['OpeningHours'][$key]['End']['toTime'])) echo "1"; ?>" />
											<span id="error_<?php echo $key; ?>" style="color:red;"></span>
											<span id="error_frm_<?php echo $key; ?>" style="color:red;"></span>
										</div>
										
										<div class="col-sm-3 col-lg-3 col-xs-6 select_sm no-left-pad">
											<input type="text" rowid="<?php echo $key; ?>" class="form-control timepicker col-xs-6" id="to1_<?php echo $key; ?>" name="to1_<?php echo $key; ?>" value="<?php if(isset($merchantInfo['OpeningHours'][$key]['End']['toTime'])) echo $merchantInfo['OpeningHours'][$key]['End']['toTime']; ?>" readonly>
											<input type="hidden" rowid="<?php echo $key; ?>" id="to2_<?php echo $key; ?>" value="<?php if(isset($merchantInfo['OpeningHours'][$key]['End']['toTime'])) echo date('H',strtotime($merchantInfo['OpeningHours'][$key]['End']['toTime'])); ?>">
											<span id="error_to_<?php echo $key; ?>" style="color:red;"></span>
										</div>
										<?php if($key == 0) { ?>
										<div class="col-sm-3 col-lg-2 col-xs-12 no-padding des_label checkbox_day">
											<div class=""><input type="checkbox" class="business_hours <?php if(isset($merchantInfo['OpeningHours'][0]['DateType']) && $merchantInfo['OpeningHours'][0]['DateType'] == '1') echo 'active'; ?>" name="samehours" id="samehours"  onclick="return hideAllDays();" <?php if(isset($merchantInfo['OpeningHours'][0]['DateType']) && $merchantInfo['OpeningHours'][0]['DateType'] == '1') echo 'checked'; ?>>&nbsp;<label for="samehours"><span style="vertical-align:middle;">Every day</span></label>
											<input type="hidden" id="showdays" name="showdays" value="<?php if(isset($merchantInfo['OpeningHours'][0]['DateType']) && $merchantInfo['OpeningHours'][0]['DateType'] == '1') echo 1; ?>"/>
											<!-- <label>&nbsp;</label> -->
											</div>
										</div>
										<?php } ?>

										<input type="hidden" id="id_<?php echo $key; ?>" name="id_<?php echo $key; ?>" value="<?php if(isset($merchantInfo['OpeningHours'][$key]['id'])) echo $merchantInfo['OpeningHours'][$key]['id']; ?>" >
									</div>
									<?php } } ?>
								</div>
								
								
								<div class="form-group col-xs-12 col-md-12"><label class="col-sm-12  control-label no-padding"><h3 style="margin-bottom:10px;"><span>Contact Info</span></h3></label>
								<div class="form-group col-xs-12 col-xs-12 contact-info no-padding">
									<!--<div class="form-group col-sm-3 col-md-2 no-padding"><input type="button" title="Use my location" value="Use my location" class="btn bg-olive btn-md " onclick="return geolocation(1);"/></div>-->
									<div class="col-sm-12 col-md-12 no-padding">
										<div class="show-grid form-group col-sm-12 no-padding">
											<div class="form-group col-xs-12 col-sm-5 no-padding" ><input type="text"  id="Street" name="Street" value="<?php if(isset($merchantInfo['Street']) && !empty($merchantInfo['Street'])) echo $merchantInfo['Street'];?>" placeholder="Street" class="form-control"></div>
											<div class="form-group col-xs-12 col-sm-4 resp-no-pad"><input type="text"  id="City" name="City" value="<?php if(isset($merchantInfo['City']) && !empty($merchantInfo['City'])) echo $merchantInfo['City'];?>" placeholder="City" class="form-control"></div>
											<div class="form-group col-xs-12 col-sm-3 no-padding"><input type="text"  id="ZipCode" maxlength="8" onpaste="return false;" onkeypress="return isNumberKey_postal(event);" name="ZipCode" value="<?php if(isset($merchantInfo['PostCode']) && !empty($merchantInfo['PostCode'])) echo $merchantInfo['PostCode'];?>" placeholder="ZIP Code" class="form-control"></div>
											<div class="form-group col-xs-12 col-sm-5 no-padding"><input type="text"  id="State" name="State" value="<?php if(isset($merchantInfo['State']) && !empty($merchantInfo['State'])) echo $merchantInfo['State'];?>" placeholder="State" class="form-control"></div>	
											<div class="form-group col-xs-12 col-sm-7 no-padding-right"><input type="text"  id="Country" name="Country" value="<?php if(isset($merchantInfo['Country']) && !empty($merchantInfo['Country'])) echo $merchantInfo['Country'];?>" placeholder="Country" class="form-control"></div>	
										</div>
										<div class="show-grid form-group col-sm-12 no-padding">
											<div class="form-group col-xs-12 col-sm-5 no-padding"><input type="text"  id="Phone" maxlength="15" onkeypress="return isNumberKey_Phone(event);" name="Phone" value="<?php if(isset($merchantInfo['PhoneNumber']) && !empty($merchantInfo['PhoneNumber'])) echo $merchantInfo['PhoneNumber'];?>" placeholder="Phone" class="form-control"></div>	
											<div class="form-group col-xs-12 col-sm-7 no-padding-right"><input type="text"  id="Email" name="Email" value="<?php if(isset($merchantInfo['Email']) && !empty($merchantInfo['Email'])) echo $merchantInfo['Email'];?>" placeholder="Email" class="form-control"></div>	
											<div class="form-group col-xs-12 col-sm-5 no-padding clear"><input type="text"  id="Website" name="Website" value="<?php if(isset($merchantInfo['WebsiteUrl']) && !empty($merchantInfo['WebsiteUrl'])) echo $merchantInfo['WebsiteUrl'];?>" placeholder="Website" class="form-control"></div>
										</div>
										<div class="form-group col-xs-12 col-sm-5 col-md-5 col-lg-5 no-padding">
											<input type="text"  id="Facebook" name="Facebook" value="<?php if(isset($merchantInfo['FBId']) && !empty($merchantInfo['FBId'])) echo $merchantInfo['FBId'];?>" placeholder="Facebook" class="form-control">
											<!-- <p class="help-block col-sm-12 no-padding">eg: http://www.facebook.com/example</p> -->
										</div>	
										<div class="form-group col-xs-12 col-sm-7 col-md-7 col-lg-7 no-padding-right">
											<input type="text"  id="Twitter" name="Twitter" value="<?php if(isset($merchantInfo['TwitterId']) && !empty($merchantInfo['TwitterId'])) echo $merchantInfo['TwitterId'];?>" placeholder="Twitter" class="form-control">
											<!-- <p class="help-block col-sm-12 no-padding">eg: http://www.twitter.com/example</p> -->
										</div>													
										<input type="hidden" name="Latitude" id="Latitude" value="">
										<input type="hidden" name="Longitude" id="Longitude" value="">
									</div>
								</div>	
								</div>
								<!-- Security -->
								<div class="col-sm-12 col-md-12 no-padding">
									<div class="col-xs-12 col-sm-5 col-md-5 col-lg-5"><label class="col-sm-8 col-xs-12  control-label no-padding"><h3 style="padding:4px 0px;">Security</h3></label></div>
									<div class="col-xs-12 col-sm-7 col-md-7 col-lg-7 mystore_security"><input type="text" name="Security" class="form-control valid"  id="Security" placeholder="Email: tuplit@mcdonalds.com" value="<?php if(isset($merchantInfo['Security']) &&  $merchantInfo['Security'] != '' ) echo  $merchantInfo['Security'];?>"></div>
								</div>
								<!-- Security -->
								
								<!-- Change Password & PIN code -->
								<div class="col-sm-12"><label class="col-sm-12  control-label no-padding"><h4 style="margin-top:30px;margin-bottom:10px;"><span>Change Password & PIN code</span></h4></label></div>
								<div class="form-group col-sm-12 col-xs-12">	
									<div class="col-sm-12 col-md-12 no-padding change_pwd_code">
										<div class="show-grid form-group col-sm-12 no-padding">
											<div class="form-group col-xs-12 col-sm-5 no-padding"><input type="text" onkeypress="return isNumberKey(event);"  id="Pincode" name="Pincode" value="" placeholder="New PIN code" class="form-control" maxlength="4"></div>	
											<div class="form-group col-xs-12 col-sm-7 no-padding-right"><input type="text" onkeypress="return isNumberKey(event);"  id="CPincode" name="CPincode" value="" placeholder="Confirm new PIN code" maxlength="4" class="form-control"></div>	
										</div>
										<div class="show-grid form-group col-sm-12 no-padding">
											<div class="form-group col-xs-12 col-sm-5 no-padding"><input type="text"  id="Password" name="Password" value="" placeholder="New password" class="form-control"></div>	
											<div class="form-group col-xs-12 col-sm-7 no-padding-right"><input type="text"  id="CPassword" name="CPassword" value="" placeholder="Confirm new password" class="form-control"></div>
											<input type="hidden" name="hidden_pin" id="hidden_pin" value="<?php if(isset($merchantInfo['Pincode']) &&  $merchantInfo['Pincode'] != '' ) echo  $merchantInfo['Pincode'];?>">
										</div>
									</div>
								</div>
								
								<!-- Permissions -->
								<?php if(isset($merchantInfo['SalespersonList']) && isset($merchantInfo['SalespersonList']['salesperson']) && count($merchantInfo['SalespersonList']['salesperson']) > 0) {  ?>
								<!--<div class="col-sm-12">
									<label class="col-sm-12 control-label no-padding "><h3><span>Permissions</span></h3></label>
								</div>
								<div class="col-sm-12 col-xs-12 form-group" style="margin-bottom:10px;">
									<div class="col-xs-6 no-padding">&nbsp;</div>
									<div class="col-xs-2 no-padding" align="center">Basic</div>
									<div class="col-xs-2 no-padding" align="center">Pro</div>
									<div class="col-xs-2 no-padding" align="center">Admin</div>
								</div> 
										<?php foreach($merchantInfo['SalespersonList']['salesperson'] as $val) { ?>
											<div class="col-sm-12 col-xs-12 form-group permission">
												<div class="col-xs-6 no-padding"><strong><?php echo $val['Name']; ?></strong></div>
												<div class="col-xs-2 no-padding radio des_label text-center">
													<span class="checkbox"><input type="radio" id="Basic_<?php echo $val['id'];?>" checked = "checked" name="Basic_<?php echo $val['id'];?>" value="1">
													<label>&nbsp;</label>
													</span>
												</div>
												<div class="col-xs-2 no-padding radio des_label" align="center"><span class="checkbox"><input disabled type="radio" name="Pro" value="2"><label>&nbsp;</label></span></div>
												<div class="col-xs-2 no-padding radio des_label" align="center"><span class="checkbox"><input disabled type="radio" name="Admin" value="3"><label>&nbsp;</label></span></div>
											</div> 
										<?php } ?>
										-->
										<?php } ?>
								<!--Permissions -->
								
								<!-- Security -->
								<div class="col-sm-9 col-md-8 col-lg-9">
									<label class="col-sm-12 control-label no-padding"><h3 class="no-bottom">Auto-Lock</h3></label>
									<p class="help-block no-padding">When no activity is done, Tuplit portal will lock itself and ask for PIN code to reactivate.</p>
								</div>
								<div class="col-sm-3 col-md-4 col-lg-3 mystore_pad no-left-pad">
									<select name="AutoLock" id="AutoLock" class="form-control">
										<option value="">No AutoLock</option>	
										<?php if(isset($AutoLock) && !empty($AutoLock)) {
											foreach($AutoLock as $key=>$val) {
										?>
										<option value="<?php echo $key;?>" <?php if(isset($merchantInfo['AutoLock']) &&  $merchantInfo['AutoLock'] == $key ) echo 'selected';?> ><?php echo $val;?> minutes</option>
										<?php } } ?>
									</select>
								</div>
								<!-- Security -->
								
								<!-- Sales Persons List -->
								<div class="col-sm-12"><label class="col-sm-12  control-label no-padding"><h3 style="margin-bottom:10px;"><span>Sales People</span></h3></label></div>
								<div class="form-group col-xs-12 col-xs-12 sales_people">
									<?php if(isset($merchantInfo['SalespersonList']) && isset($merchantInfo['SalespersonList']['totalCount'])) {
										foreach($merchantInfo['SalespersonList']['salesperson'] as $val) { ?>
										<div class="form-group col-sm-6 col-xs-12 col-lg-6 no-padding height80">
											<div class="col-xs-12 no-padding">
											<div class="col-xs-2 col-sm-2 col-md-3 col-lg-2 no-padding">
												<?php 	if(!empty($val['Image'])) {	?><a onclick="return loaded;" href="<?php echo $val['Image']; ?>" class="icon_fancybox" title=""> <?php } ?>
												<img class="img_border" src="<?php if(!empty($val['Image'])) echo $val['Image']; else echo MERCHANT_IMAGE_PATH."no_user.jpeg"; ?>" width="50" height="50" alt="Image"/>
												<?php if(!empty($image_path)) { ?></a> <?php } ?>
											</div>
											<div class="col-xs-8 col-sm-10 col-md-9 col-lg-10">
												<h5 class="no-margin"><b><?php echo $val['Name']; ?></b></h5>
												<p class="help-block col-sm-12 no-padding"><?php echo $val['Email']; ?></p>
												<p>
													<a onclick="return loaded;" class="edit salesperson" href="SalesPerson?editId=<?php echo $val['id']; ?>" title="Edit"><i class="fa fa-edit "></i></a>&nbsp;&nbsp;
													<!--<a class="delete" onclick="javascript:return confirm('Are you sure to delete?')" href="SalesPersonList?delId=<?php echo $val['id']; ?>" title="Delete"><i class="fa fa-trash-o "></i></a>-->
												</p>
											</div>
											</div>
										</div>								
									<?php } }?>
									<div class="form-group col-sm-6 col-xs-12 col-lg-6 no-padding height80">
										<div class="col-xs-12 no-padding">
										<div class="col-xs-2 col-sm3 col-md-3 col-lg-2 no-padding">
											<a onclick="return loaded;" class="add_people salesperson" href="<?php echo SITE_PATH; ?>/SalesPerson" title="Add More People"><span><i class="fa fa-lg fa-plus"></i></span></a>
										</div>
										<div class="col-xs-10 col-sm9 col-md-9 col-lg-10 add_more">
											<div class="more_people col-sm-12 no-padding">
												<a onclick="return loaded;" class="add_people salesperson" href="<?php echo SITE_PATH; ?>/SalesPerson" title="Add More People"><strong>Add More People</strong></a>
											</div>
										</div>
										</div>
									</div>		
								</div>	
							</div>
						</div>
					</div>
					<div class="footer col-xs-12 no-padding" align="center">
						<div class="col-xs-12 col-sm-1 Rejected_class btn btn-default col-lg-1">
								<a href="Dashboard" name="cancel" class="btn btn-default  col-xs-12  cancel_button" id="cancel">Cancel</a>
						</div>
						<div class="col-xs-12 col-sm-11 col-lg-11 no-padding approve_class"> 
								<input type="submit" name="mystore_submit" id="mystore_submit" value="SAVE CHANGES" title="Save Changes" class="btn btn-success cancel_button">
						</div>
					</div>
				</form>
			</div>
		</section>
<?php footerLogin(); ?>
<input type="hidden" name="TuplitShareLocationSession" id="TuplitShareLocationSession" value="
<?php if(isset($_SESSION['TuplitShareLocationSession']) && !empty($_SESSION['TuplitShareLocationSession']))
		echo $_SESSION['TuplitShareLocationSession'];
	else
		echo '0'; 
?>		
"/>
<?php	commonFooter();	?>
		
	<script type="text/javascript">	
		<?php if(!isset($_SESSION['TuplitShareLocationSession']))  { ?>
			geolocation();	
		<?php } ?>
	</script>
</html>

<script type="text/javascript">


function setSessionDeny() {
	<?php $_SESSION['TuplitShareLocationSession'] = 1; ?>
}
function unsetSessionDeny() {
	<?php unset($_SESSION['TuplitShareLocationSession']); ?>
}

function price_val(val){
	$("#priceValidation").val(val);
}
$(function() {
	if($('#Notification_val').val() == 1)
		$(".tog").addClass('on');
	else 
		$(".tog").removeClass('on');
	/* Discount calculation */
});
  //document.ready
/*jQuery(document).ready(function() {
	showCategory(<?php echo $show_cat;?>);
});*/
$(document).ready(function() {
	//hideAllDayss();
	$('.icon_fancybox').fancybox();	
	$('.image_fancybox').fancybox();
	
	if($("#min_price").val() > 0)
		price_val($("#min_price").val());
	else
		price_val($("#max_price").val());

	
	// find the input fields and apply the time select to them.
	$('.timepicker').ptTimeSelect({
		onBeforeShow: function(i){
			$('#sample2-data')
				.append(
					'onBeforeShow(event) Input field: [' + 
					$(i).attr('name') + 
					"], value: [" +
					$(i).val() +
					"]<br>");
			val	=	$(i).val();	
			if(val == '')
				$('#ptTimeSelectUserSelHr').html('01');
			else {
				var timesplit 		= 	val.split(" ");
				var timearray 		= 	timesplit[0].split(":").map(Number);
				var time1			=	timearray[0].toString();	
				var time2			=	timearray[1].toString();
				if(time1.length == 1)
					time1	=	'0'+time1;
				if(time2.length == 1)
					time2	=	'0'+time2;
				$('#ptTimeSelectUserSelHr').html(time1);
				$('#ptTimeSelectUserSelMin').html(time2);
				$('#ptTimeSelectUserSelAmPm').html(timesplit[1]);	
			}
		},
		onClose: function(i) {
			$('#sample2-data')
				.append(
					'onClose(event)Time selected: ' + 
					$(i).val() + 
					"<br>");
		}
		
	}); //end ptTimeSelect()
	
		
	//Slideshow image
	/*var myStore = {name: 'myStore', type : '2'};
	$('#myStore').change(myStore,uploadFiles);*/
	
	//Logo image
	var icon_photo = {name: 'icon_photo', type : '0', id : ''};
	$('#icon_photo').change(icon_photo,uploadFiles);
	
	//merchant image
	var merchant_photo = {name: 'merchant_photo', type : '0', id : ''};
	$('#merchant_photo').change(merchant_photo,uploadFiles);
	
	//background image
	var background_photo = {name: 'background_photo', type : '0', id : ''};
	$('#background_photo').change(background_photo,uploadFiles);
	
	//add salesperson
	$(".salesperson").fancybox({
				width: '320',
				maxWidth: '100%',
				scrolling: 'auto',			
				type: 'iframe',
				autoSize: true,
				afterClose : function() {
										location.reload();
										return;
									}
		});
	$(".MangoPay").fancybox({
				width: '400',
				height:'500',
				maxWidth: '100%',
				scrolling: 'auto',			
				type: 'iframe',
				fitToView: true,
				autoSize: true,
				afterClose : function() {
										location.reload();
										return;
									}
				
		});
	$(".MangoPayEdit").fancybox({
				width: '400',
				height:'500',
				maxWidth: '100%',
				scrolling: 'auto',			
				type: 'iframe',
				fitToView: true,
				autoSize: true
		});
	$("#MangoPayEdit").fancybox({
				width: '400',
				height:'500',
				maxWidth: '100%',
				scrolling: 'auto',			
				type: 'iframe',
				fitToView: true,
				autoSize: true,
				beforeLoad : function() {
										if(confirm("Mangopay charges a fee for each withdrawal, for more info see https://www.mangopay.com/pricing/"))
											return true;
										else
											return false;
									}
				
		});
	
	$(".MangoPayBalance").fancybox({
				width: '320',
				height:'300',
				maxWidth: '100%',
				scrolling: 'auto',			
				type: 'iframe',
				fitToView: true,
				autoSize: true
		});
	<?php for($i=1;$i<=10;$i++) { ?>
		var myStore_<?php echo $i; ?> = {name: 'myStore_<?php echo $i; ?>', type : '0' , id : '<?php echo $i; ?>'};
		$('#myStore_<?php echo $i; ?>').change(myStore_<?php echo $i; ?>,uploadFiles);
	<?php } ?>	
	
});

</script>
