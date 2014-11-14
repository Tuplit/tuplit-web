<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();

if(isset($_GET['ajax']) && $_GET['ajax'] == 1) {
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
$url							=	WEB_SERVICE.'v1/categories/';
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
		$merchantInfo['LocationCountry']	=	$_POST['Country'];
	if(isset($_POST['Facebook']))
		$merchantInfo['FBId']				=	$_POST['Facebook'];
	if(isset($_POST['Twitter']))
		$merchantInfo['TwitterId']			=	$_POST['Twitter'];
	if(isset($_POST['DiscountTier']))
		$merchantInfo['DiscountTier']		=	$discountTierArray[$_POST['DiscountTier']].'%';
	
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
	$iconPath	= $imagePath	='';
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
	$merchantInfo['OpeningHours']	=	$openTiming;
	$data	=	array(
					'CompanyName' 		=> 	$_POST['ShopName'],
					'Email' 			=> 	$_POST['Email'],
					'Street' 			=> 	$_POST['Street'],
					'PhoneNumber' 		=> 	$_POST['Phone'],
					'WebsiteUrl' 		=> 	$_POST['Website'],
					'ShortDescription' 	=> 	$_POST['ShopDescription'],
					'Description' 		=> 	$_POST['MoreInfo'],
					'OpeningHours' 		=> 	$openTiming,
					'IconPhoto' 		=> 	$iconPath,
					'MerchantPhoto' 	=> 	$imagePath,
					'IconExist'			=> 	$_POST['old_icon_photo'],
					'MerchantExist'		=> 	$_POST['old_merchant_photo'],
					'PriceRange' 		=> 	$prizeRange,
					'Categories' 		=> 	$_POST['categorySelected'],
					//'DiscountTier' 		=> 	$_POST['DiscountTier'],
					'City'				=> 	$_POST['City'],
					'State'				=> 	$_POST['State'],
					'ZipCode'			=> 	$_POST['ZipCode'],
					'Country'			=> 	$_POST['Country'],
					'FBId'				=> 	$_POST['Facebook'],
					'TwitterId'			=> 	$_POST['Twitter'],
					'TotalImageExt'		=>	$_POST['ImageExt'],
					'ImageCount'		=>	$_POST['imagecount'],
					'DeleteIds'			=>	$_POST['DeleteIds'],
					'DeleteImg'			=>	$_POST['DeleteImg']
				);
	$url			=	WEB_SERVICE.'v1/merchants/';
	$method			=	'PUT';
	//echo $url."<br>";
	//echo json_encode($data); die();
	$curlResponse	=	curlRequest($url,$method,json_encode($data), $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlResponse) && is_array($curlResponse) && $curlResponse['meta']['code'] == 201) {
		$merchantId					= 	$_SESSION['merchantInfo']['MerchantId'];
		$url						=	WEB_SERVICE.'v1/merchants/'.$merchantId;
		$curlMerchantResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
		if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201 && $curlMerchantResponse['merchant']['MerchantId'] != '' ) 
		 {
			$merchantInfo  						= 	$curlMerchantResponse['merchant'];
			$_SESSION['merchantDetailsInfo']	=	$merchantInfo;
			$newCategory						=	$merchantInfo['Category'];
		}
	
		$successMessage	=	$curlResponse['notifications'][0];
	} else if(isset($curlResponse['meta']['errorMessage']) && $curlResponse['meta']['errorMessage'] != '') {
		$errorMessage		=	$curlResponse['meta']['errorMessage'];
	} else {
		$errorMessage		= 	"Bad Request";
	}
}

$merchantInfo['OpeningHours']	=	formOpeningHours($merchantInfo['OpeningHours']);

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
<body class="skin-blue fixed body_height" onload="fieldfocus('Address');">
		
		<div height="100%" width="100%" id="fancybox-loading" style="display:none;"><img src="./webresources/images/fetch_loader.gif"></img></div>
		<section class="content mystore">
			<div class="col-xs-12">
				<section class="content-header">
					<h1 style="margin-top:0px;margin-bottom:20px;">Shop settings</h1>
				</section>
				<?php if(isset($msg) && $msg != '') { ?>
					<div align="center" class="alert <?php  echo $class;  ?> alert-dismissable col-xs-12 col-sm-5"><i class="fa <?php  echo $class_icon;  ?>"></i>  <?php echo $msg; ?></div>
				<?php } ?>
				<form action="" name="mystore_form" id="mystore_form"  method="post">
					<div class="col-md-12 box  box-center ">
						<div class=" box-primary my_store_form no-padding">
							<div class="col-md-6 right_border">
								<div class="col-sm-6">
									<label class="control-label" ><h3>Shop Name</h3></label>
									<p class="help-block col-sm-12 no-padding">Name is visible on your card in mobile app</p>
								</div>
								<div class="col-sm-6 mystore_pad"><input type="text" name="ShopName" class="form-control valid"  id="ShopName" value="<?php if(isset($merchantInfo['CompanyName']) && !empty($merchantInfo['CompanyName'])) echo $merchantInfo['CompanyName'];?>"></div>
								
								<div class="col-sm-12">
									<label class="col-sm-12 col-md-12 control-label no-padding border-right"><h3><span>Shop Description / Slogan</span></h3></label>
									<p class="help-block col-sm-12 no-padding">Slogan is shown under the shop name (max. 80 characters)</p>
								</div>
								<div class="col-sm-12">
									<input type="text" name="ShopDescription"  id="ShopDescription" maxlength="80" placeholder="e.g: The best burger in town!" class="form-control valid" value="<?php if(isset($merchantInfo['ShortDescription']) && !empty($merchantInfo['ShortDescription'])) echo $merchantInfo['ShortDescription'];?>">
								</div>
								<div class="col-sm-6"><label class="col-sm-8 control-label no-padding"><h3 style="margin-bottom:20px;">Category</h3></label></div>
								<div class="col-sm-6 mystore_pad">
								<div class="custom-select">
									<select name="Category" id="Category" class="form-control" onchange="showCategory(this.value)">
										<option value="">Select</option>	
										<?php if(isset($categories) && !empty($categories)) {
											foreach($categories as $key=>$val) {
											if($key != 'totalCount') {
										?>
										<option value="<?php echo $val['CategoryId'];?>"  style="background-image:url(<?php echo $val['CategoryIcon']; ?>);"><?php echo ucfirst($val['CategoryName']);?></option>
										<?php } } } ?>
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
								
								<div class="col-sm-6">
									<label class="col-sm-8 col-xs-12  control-label no-padding"><h3>Logo</h3></label>
									<p class="help-block col-sm-12 no-padding">(dimension 100x100)</p>
								</div>
								<div class="col-sm-6 mystore_pad">
									<div class="col-xs-12 no-padding"><input type="file"  name="icon_photo" id="icon_photo" /><br>
										<!--<span class="error" for="empty_merchant_photo" generated="true" style="display: none">Logo is required</span>-->
									</div>
									<div class="col-xs-12 no-padding text-left" >
									  <div id="icon_photo_img" class="">
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
								<div class="col-sm-6">
									<label class="col-sm-8 col-xs-12  control-label no-padding"><h3>Image</h3></label>
									<p class="help-block col-sm-12 no-padding">(dimension 100x100)</p>
								</div>
								<div class="col-sm-6 mystore_pad">
									<div class="col-xs-12 no-padding"><input type="file"  name="merchant_photo" id="merchant_photo"/><br>
										<!--<span class="error" for="empty_merchant_photo" generated="true" style="display: none">Image is required</span>-->
									</div>
										
									<div class="col-xs-12 no-padding text-left" >
										  <div id="merchant_photo_img" class="">
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
														
								<div class="col-sm-12">
									<label class="col-sm-12 control-label no-padding "><h3><span>Slideshow pictures</span></h3></label>
									<p class="help-block no-padding">Upload up to 10 pictures (optimum resolution is 1000x300 pixels, bigger images will be scaled down
										automatically)</p>
								</div>
								<div class="col-sm-12">
									<div class="row">
										<?php if(isset($slideshows) && count($slideshows) > 0) { $mystoreidstot	=	''; 
											$i = 0;	foreach($slideshows as $val) { $i = $i + 1;	
											if(!empty($mystoreidstot))
												$mystoreidstot	.=	','.$val['id'];
											else
												$mystoreidstot	=	$val['id'];
										?>
										<div class="col-sm-6 col-xs-12 form-group" id="myStore_<?php echo $val['id']; ?>">
											<!-- <div class="col-xs-1 no-padding" id="imgStoreCount_<?php echo $val['id']; ?>"><?php echo $i; ?>.</div> -->
											<div class="col-xs-12" align="center">
												<div  class="photo_gray_bg" style="background-color:#fff;"> 
													<a href="<?php echo $val['imagePath']; ?>" class="image_fancybox" title="">
													<img style="vertical-align:top" class="slidershow_image" src="<?php echo $val['imagePath']; ?>" width="200" height="115" alt="">
													</a>
												</div>
											</div>
											<!-- <div class="col-xs-12">&nbsp;</div> -->										
											<!-- <div class="col-xs-2 col-md-1 clear">&nbsp;</div> -->
											<div class="col-xs-12 col-md-12" align="center">
											<a  href="javascript:void(0);" title="Delete" class="delete_image col-xs-12" onclick="return deleteMyStoreImage('<?php echo $val['id']; ?>','<?php echo $val['SlideshowName']; ?>')"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;DELETE IMAGE</a>
												<!-- <input type="button" name="" id="" value="Delete" title="Delete" class="box-center btn btn-danger  col-xs-10" onclick="return deleteMyStoreImage('<?php echo $val['id']; ?>','<?php echo $val['SlideshowName']; ?>')"> -->
											</div>
										</div>
										<?php }	}?>									
										<div class="col-sm-6 col-xs-12 form-group" id="temp0" style="<?php  if($slideshowcount >= 10) echo 'display:none;'; ?>" >
											<!-- <div class="col-xs-1 no-padding" id="imgcount"><?php echo $slideshowcount + 1; ?>.</div> -->
											<div class="col-xs-11 no-padding" align="center" id="drop-files" ondragover="return false">
												<div  class="" >
													<img style="vertical-align:top" class="resize" id="imgdrag" src="<?php SITE_PATH;?>webresources/images/upload_mystore.png" width="330" height="160" alt="">
												</div>											
												<div class="drag_pos" id="holder">
													Tap here to upload image
													<input type="file" name="myStore" id="myStore" >
												</div>
											</div>
											<div class="col-xs-12">&nbsp;</div>
											<div class="col-xs-2 col-md-1 clear">&nbsp;</div>
											<div class="col-xs-10 col-md-11" align="center">
												<!--<input type="button" name="Upload" id="Upload" value="Upload" title="Upload" class="box-center btn btn-success  col-xs-10">-->
											</div>
										</div>									
									</div>
									<input type="hidden" id="totalImageNow" name="totalImageNow" value="<?php if(isset($slideshowcount) && !empty($slideshowcount)) echo $slideshowcount; else echo "0"; ?>" />
									<input type="hidden" id="totalImage" name="totalImage" value="<?php if(isset($slideshowcount) && !empty($slideshowcount)) echo $slideshowcount; else echo "0"; ?>" />
									<input type="hidden" id="oldtotalImage" name="oldtotalImage" value="<?php if(isset($slideshowcount) && !empty($slideshowcount)) echo $slideshowcount; else echo "0"; ?>" />
									<input type="hidden" id="imagecount" name="imagecount" value="" />
									<input type="hidden" id="ImageExt" name="ImageExt" value="" />
									<input type="hidden" id="DeleteIds" name="DeleteIds" value="" />
									<input type="hidden" id="DeleteImg" name="DeleteImg" value="" />
									<input type="hidden" id="mystoreidstot" name="mystoreidstot" value="<?php if(isset($mystoreidstot) && !empty($mystoreidstot)) echo $mystoreidstot; ?>" />
								</div>
							</div>
							<div class="col-md-6">
								<div class="col-sm-12">
									<label class="col-sm-12 control-label no-padding"><h3><span>More Info</span></h3></label>
									<p class="help-block no-padding">More info is text shown on your details page. Tell more about your venue and why should your 
										potential customers visit and shop in your venue. Up to 300 characters.</p>
								</div>
								<div class="col-sm-12"><textarea type="text" name="MoreInfo" id="MoreInfo" maxlength="300" placeholder="More Info" class="form-control valid" ><?php if(isset($merchantInfo['Description']) && !empty($merchantInfo['Description'])) echo $merchantInfo['Description'];?></textarea></div>
								
								<div class="form-group col-xs-12 col-md-12">
									<label class="col-sm-12 col-md-12 control-label no-padding border-right"><h3 style="margin-bottom:15px;"><span>Business hours</span></h3><!-- <em></em> --></label>
									<!-- <p class="help-block col-sm-12 no-padding">Open Hours leave as empty for not service</p> -->
									<?php 
									if(isset($days_array) && count($days_array)>0) {
									foreach($days_array as $key=>$val){ ?>
									<div class="col-xs-12 no-padding form-group <?php if($key != 0) echo "rowHide";?>"  <?php if(isset($merchantInfo['OpeningHours'][0]['DateType']) && $merchantInfo['OpeningHours'][0]['DateType'] == '1' && $key != 0) echo 'style="display:none;"'; ?>>
										<?php if($key == 0) { ?>
											<!-- <div class="col-xs-6 col-sm-4 col-xs-6 no-padding LH30">From :</div> -->
											<!-- <div class="col-xs-5 col-sm-4 col-xs-6 no-padding LH30">To :</div> -->
										<?php } ?>
										<div class="col-sm-3  col-lg-2 col-xs-12  no-padding LH30"><span class="<?php if($key == 0) echo "rowshow";?>"><?php if(isset($merchantInfo['OpeningHours'][0]['DateType']) && $merchantInfo['OpeningHours'][0]['DateType'] == '1' && $key == 0) echo "Monday - Friday"; else echo $val.""; ?></span></div>
										<div class="col-sm-3 col-lg-4 col-xs-12 no-padding select_sm">
											<input type="text" rowid="<?php echo $key; ?>" class="form-control timepicker" style="width:80%;text-align:center;" id="from1_<?php echo $key; ?>" name="from1_<?php echo $key; ?>" value="<?php if(isset($merchantInfo['OpeningHours'][$key]['Start']['fromTime'])) echo $merchantInfo['OpeningHours'][$key]['Start']['fromTime']; ?>" readonly>
											<input type="hidden" id="row_<?php echo $key; ?>" name="row_<?php echo $key; ?>" value="<?php if(!empty($merchantInfo['OpeningHours'][$key]['Start']['fromTime']) || !empty($merchantInfo['OpeningHours'][$key]['End']['toTime'])) echo "1"; ?>" />
											<span id="error_<?php echo $key; ?>" style="color:red;"></span>
										</div>
										
										<div class="col-sm-3 col-lg-4 col-xs-12 no-padding select_sm">
											<input type="text" rowid="<?php echo $key; ?>" class="form-control timepicker" style="width:80%;text-align:center;" id="to1_<?php echo $key; ?>" name="to1_<?php echo $key; ?>" value="<?php if(isset($merchantInfo['OpeningHours'][$key]['End']['toTime'])) echo $merchantInfo['OpeningHours'][$key]['End']['toTime']; ?>" readonly>
										</div>
										<?php if($key == 0) { ?>
										<div class="col-sm-3 col-lg-2 col-xs-12 no-padding">
											<input type="checkbox" class="business_hours" name="samehours" id="samehours"  onclick="return hideAllDays();" <?php if(isset($merchantInfo['OpeningHours'][0]['DateType']) && $merchantInfo['OpeningHours'][0]['DateType'] == '1') echo "checked"; ?>>&nbsp;Every day 
											<input type="hidden" id="showdays" name="showdays" value="<?php if(isset($merchantInfo['OpeningHours'][0]['DateType']) && $merchantInfo['OpeningHours'][0]['DateType'] == '1') echo 'checked'; ?>"/>
										</div>
										<?php } ?>

										<input type="hidden" id="id_<?php echo $key; ?>" name="id_<?php echo $key; ?>" value="<?php if(isset($merchantInfo['OpeningHours'][$key]['id'])) echo $merchantInfo['OpeningHours'][$key]['id']; ?>" >
									</div>
									<?php } } ?>
								</div>
								
								
								<div class="col-sm-12"><label class="col-sm-12  control-label no-padding"><h3 style="margin-bottom:10px;"><span>Contact Info</span></h3></label></div>
								<div class="form-group col-xs-12 col-xs-12">
									<!--<div class="form-group col-sm-3 col-md-2 no-padding"><input type="button" title="Use my location" value="Use my location" class="btn bg-olive btn-md " onclick="return geolocation(1);"/></div>-->
									<div class="col-sm-12 col-md-12 no-padding">
										<div class="show-grid form-group col-sm-12 no-padding">
											<div class="form-group col-sm-5 no-padding"><input type="text"  id="Street" name="Street" value="<?php if(isset($merchantInfo['Address']) && !empty($merchantInfo['Address'])) echo $merchantInfo['Address'];?>" placeholder="Street Address" class="form-control"></div>
											<div class="form-group col-sm-4"><input type="text"  id="City" name="City" value="<?php if(isset($merchantInfo['City']) && !empty($merchantInfo['City'])) echo $merchantInfo['City'];?>" placeholder="City" class="form-control"></div>
											<div class="form-group col-sm-3 no-padding"><input type="text"  id="ZipCode" name="ZipCode" value="<?php if(isset($merchantInfo['PostCode']) && !empty($merchantInfo['PostCode'])) echo $merchantInfo['PostCode'];?>" placeholder="ZIP" class="form-control"></div>
											<div class="form-group col-sm-5 no-padding"><input type="text"  id="State" name="State" value="<?php if(isset($merchantInfo['State']) && !empty($merchantInfo['State'])) echo $merchantInfo['State'];?>" placeholder="State" class="form-control"></div>	
											<div class="form-group col-sm-7 no-padding-right"><input type="text"  id="Country" name="Country" value="<?php if(isset($merchantInfo['LocationCountry']) && !empty($merchantInfo['LocationCountry'])) echo $merchantInfo['LocationCountry'];?>" placeholder="Country" class="form-control"></div>	
										</div>
										<div class="show-grid form-group col-sm-12 no-padding">
											<div class="form-group col-sm-5 no-padding"><input type="text"  id="Phone" name="Phone" value="<?php if(isset($merchantInfo['PhoneNumber']) && !empty($merchantInfo['PhoneNumber'])) echo $merchantInfo['PhoneNumber'];?>" placeholder="Phone" class="form-control"></div>	
											<div class="form-group col-sm-7 no-padding-right"><input type="text"  id="Email" name="Email" value="<?php if(isset($merchantInfo['Email']) && !empty($merchantInfo['Email'])) echo $merchantInfo['Email'];?>" placeholder="Email" class="form-control"></div>	
											<div class="form-group col-sm-5 no-padding clear"><input type="text"  id="Website" name="Website" value="<?php if(isset($merchantInfo['WebsiteUrl']) && !empty($merchantInfo['WebsiteUrl'])) echo $merchantInfo['WebsiteUrl'];?>" placeholder="Website" class="form-control"></div>
										</div>
										<div class="form-group col-sm-5 no-padding">
											<input type="text"  id="Facebook" name="Facebook" value="<?php if(isset($merchantInfo['FBId']) && !empty($merchantInfo['FBId'])) echo $merchantInfo['FBId'];?>" placeholder="Facebook" class="form-control">
											<!-- <p class="help-block col-sm-12 no-padding">eg: http://www.facebook.com/example</p> -->
										</div>	
										<div class="form-group col-sm-7 no-padding-right">
											<input type="text"  id="Twitter" name="Twitter" value="<?php if(isset($merchantInfo['TwitterId']) && !empty($merchantInfo['TwitterId'])) echo $merchantInfo['TwitterId'];?>" placeholder="Twitter" class="form-control">
											<!-- <p class="help-block col-sm-12 no-padding">eg: http://www.twitter.com/example</p> -->
										</div>													
										<input type="hidden" name="Latitude" id="Latitude" value="">
										<input type="hidden" name="Longitude" id="Longitude" value="">
									</div>
								</div>	
								
								<!-- Security -->
								<div class="col-sm-6"><label class="col-sm-8 col-xs-12  control-label no-padding"><h3>Security</h3></label></div>
								<div class="col-sm-6 mystore_pad"><input type="text" name="Security" class="form-control valid"  id="Security" placeholder="Email: tuplit@mcdonalds.com" value="<?php if(isset($merchantInfo['Security']) && !empty($merchantInfo['Security'])) echo $merchantInfo['Security'];?>"></div>
								
								<!-- Change Password & PIN code -->
								<div class="col-sm-12"><label class="col-sm-12  control-label no-padding"><h3 style="margin-bottom:10px;"><span>Change Password & PIN code</span></h3></label></div>
								<div class="form-group col-xs-12 col-xs-12">	
									<div class="col-sm-12 col-md-12 no-padding">
										<div class="show-grid form-group col-sm-12 no-padding">
											<div class="form-group col-sm-5 no-padding"><input type="text"  id="Pincode" name="Pincode" value="" placeholder="New PIN code" class="form-control"></div>	
											<div class="form-group col-sm-7 no-padding-right"><input type="text"  id="CPincode" name="CPincode" value="" placeholder="Confirm new PIN code" class="form-control"></div>	
										</div>
										<div class="show-grid form-group col-sm-12 no-padding">
											<div class="form-group col-sm-5 no-padding"><input type="text"  id="Password" name="Password" value="" placeholder="New password" class="form-control"></div>	
											<div class="form-group col-sm-7 no-padding-right"><input type="text"  id="CPassword" name="CPassword" value="" placeholder="Confirm new Password" class="form-control"></div>	
										</div>
									</div>
								</div>
								
								<!-- Sales Persons List -->
								
								<div class="col-sm-12"><label class="col-sm-12  control-label no-padding"><h3 style="margin-bottom:10px;"><span>Sales People</span></h3></label></div>
								<div class="form-group col-xs-12 col-xs-12">
									<?php if(isset($merchantInfo['SalespersonList']) && isset($merchantInfo['SalespersonList']['totalCount'])) {
										foreach($merchantInfo['SalespersonList']['salesperson'] as $val) { ?>
										<div class="form-group col-sm-6 no-padding">
											<div style="width:35%;float:left;" >
												<?php 	if(!empty($val['Image'])) {	?><a href="<?php echo $val['Image']; ?>" class="icon_fancybox" title=""> <?php } ?>
												<img class="img_border" src="<?php if(!empty($val['Image'])) echo $val['Image']; else echo MERCHANT_IMAGE_PATH."no_user.jpeg"; ?>" width="75" height="75" alt="Image"/>
												<?php if(!empty($image_path)) { ?></a> <?php } ?>
											</div>
											<div style="width:65%;float:left;">
												<b><?php echo $val['Name']; ?></b>
												<p class="help-block col-sm-12 no-padding"><?php echo $val['Email']; ?></p>
											</div>
										</div>								
									<?php } }?>
									<div class="form-group col-sm-6 no-padding">
										<div style="width:35%;float:left;" >
											<a href="<?php echo SITE_PATH; ?>/SalesPerson" class="salesperson" title="">
												<img class="img_border" src="<?php echo MERCHANT_IMAGE_PATH."no_user.jpeg"; ?>" width="75" height="75" alt="Image"/>
											</a>
										</div>
										<div style="width:65%;float:left;">
											<p class="help-block col-sm-12 no-padding">Add More People</p>
										</div>
									</div>		
								</div>	
							</div>
						</div>
					</div>
					<div class="footer col-xs-12 no-padding" align="center">
						<div class="col-sm-6 Rejected_class btn btn-default col-lg-3">
								<a href="Dashboard" name="cancel" class="btn btn-default  col-xs-12  cancel_button" id="cancel">Cancel</a>
						</div>
						<div class="col-sm-6 col-lg-9 no-padding approve_class"> 
								<input type="submit" name="mystore_submit" id="mystore_submit" value="SAVE CHANGES" title="Save Changes" class="btn btn-success cancel_button">
						</div>
					</div>
				</form>
			</div>
		</section>
		<?php footerLogin(); ?>
		<?php commonFooter(); ?>
		
	<script type="text/javascript">		
		//geolocation();		
	</script>
</html>
<script type="text/javascript">
function price_val(val){
	$("#priceValidation").val(val);
}
  //document.ready
/*jQuery(document).ready(function() {
	showCategory(<?php echo $show_cat;?>);
});*/
$(document).ready(function() {
	//geolocation();		
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
	
	jQuery.event.props.push('dataTransfer');
	var dataArray 	= 	[];
	var imgextArray	=	['png','jpg','jpeg']
	// Bind the drop event to the drop zone.
	$('#drop-files').bind('drop', function(e) {
		if(dataArray.length < 10) {
			var files = e.dataTransfer.files;
			// For each file
			$.each(files, function(index, file) {
				
				//validate image type 
				filename	=	files[index].type;
				ext			=	filename.split('/');
				if(jQuery.inArray( ext[1], imgextArray ) != '-1') {				
					ImageExt	=	$('#ImageExt').val();
					if(ImageExt != '') 
						$('#ImageExt').val(ImageExt+','+ext[1]);
					else
						$('#ImageExt').val(ext[1]);
				} else {
					alert('Please upload JPEG, JPG and PNG images only.');
					return false;
				}
				
				//validate image size 
				if(files[index].size > '1048576'){
					alert('Image size should not be less than 1 MB.');
					return false;
				}
				
				totalImageNow	=	parseInt($('#totalImageNow').val());
				totalImageNow	=	totalImageNow + 1;
				if(totalImageNow > 10) {
					return false;
				}
				$('#totalImageNow').val(totalImageNow)
				var fileReader = new FileReader();
				fileReader.onload = (function(file) {				
										return function(e) { 					
											// Push the data URI into an array
											dataArray.push({name : file.name, value : this.result});
											index =	dataArray.length - 1;
											
											//total image uploaded
											oldtotalImage	=	parseInt($('#oldtotalImage').val());
											
											//new images
											imagecount		=	$('#imagecount').val();
											if(imagecount != '') {
												imagecountarray	=	imagecount.split(',');
												newimagelength	=	imagecountarray.length;	
											} else {
												newimagelength	=	0;
											}
											
											DeleteIds		=	$('#DeleteIds').val();
											if(DeleteIds != '') {
												DeleteIdsarray	=	DeleteIds.split(',');
												DeleteIdslength	=	DeleteIdsarray.length;
												oldtotalImage	=	oldtotalImage - DeleteIdslength;
											}
											
											totalimagesin	=	oldtotalImage + newimagelength + 1;
											orgtotalImage	=	parseInt($('#totalImage').val());
											totalImage		=	orgtotalImage + 1;
											
											if(imagecount != '') 
												$('#imagecount').val(imagecount+','+totalImage)
											else
												$('#imagecount').val(totalImage)
											$.ajax({
													type: "POST",
													url: 'models/upload.php?img='+totalImage,
													data: 'name='+file.name+'&value='+this.result,
													success: function (result){
														
													}			
												});
											
											$('#totalImage').val(totalImage);
											imgcount	=	totalimagesin + 1;
											imgcontent	=	'<div class="col-sm-6 col-xs-12 form-group" id="temp'+totalImage+'">';
											imgcontent	+=	'<div class="col-xs-12" align="center">';
											imgcontent	+=	'<div  class="photo_gray_bg" style="background-color:#fff;">';
											imgcontent	+=	'<img style="vertical-align:top" class="slidershow_image" src="'+this.result+'" width="200" height="115" alt="">';
											imgcontent	+=	'</div>';
											imgcontent	+=	'</div>';
											if(totalimagesin == 10) {
												imgcontent	+=	'<div class="col-xs-12 col-md-12" align="center">';
												imgcontent	+=	'<a class="delete_image col-xs-12" onclick="return deleteBefore('+totalImage+',\''+ ext[1]+'\')" title="Delete" href="javascript:void(0);">';
												imgcontent	+=	'<i class="fa fa-trash-o"></i>&nbsp;&nbsp;';
												imgcontent	+=	'DELETE IMAGE';
												imgcontent	+=	'</a>';
												imgcontent	+=	'</div></div>';
												$(imgcontent).insertBefore('#temp0');
												$('#temp0').hide();
											} else {
												imgcontent	+=	'<div class="col-xs-12 col-md-12" align="center">';
												imgcontent	+=	'<a class="delete_image col-xs-12" onclick="return deleteBefore('+totalImage+',\''+ ext[1]+'\')" title="Delete" href="javascript:void(0);">';
												imgcontent	+=	'<i class="fa fa-trash-o"></i>&nbsp;&nbsp;';
												imgcontent	+=	'DELETE IMAGE';
												imgcontent	+=	'</a>';
												imgcontent	+=	'</div></div>';
												$(imgcontent).insertBefore('#temp0');												
												$('#imgdrag').attr('src','<?php SITE_PATH;?>webresources/images/no_photo_my_store.png');
											}
										}; 				
									})(files[index]);				
				// For data URI purposes
				fileReader.readAsDataURL(file);
			});	
		} else {
			alert('You can upload only 10 pictures')
		}
		return false;
	});
	
	//Slideshow image
	var myStore = {name: 'myStore', type : '2'};
	$('#myStore').change(myStore,uploadFiles);
	
	//Logo image
	var icon_photo = {name: 'icon_photo', type : '0'};
	$('#icon_photo').change(icon_photo,uploadFiles);
	
	//merchant image
	var merchant_photo = {name: 'merchant_photo', type : '0'};
	$('#merchant_photo').change(merchant_photo,uploadFiles);
	
	//add salesperson
	$(".salesperson").fancybox({
				width: '320',
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
});

</script>
