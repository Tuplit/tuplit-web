<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();

if(isset($_GET['ajax']) && $_GET['ajax'] == 1) {
	$img = $_POST['img'];
	$img = str_replace('data:image/png;base64,', '', $img);
	$img = str_replace(' ', '+', $img);
	$data = base64_decode($img);
	$file = TEMP_IMAGE_PATH_REL . $_POST['id'] . 'mystore.png';
	$success = file_put_contents($file, $data);
	print $success ? $file : 'Unable to save the file.';
}

$merchantCategory = array();
global $days_array;
$merchantId					= 	$_SESSION['merchantInfo']['MerchantId'];
$url						=	WEB_SERVICE.'v1/merchants/'.$merchantId."?From=0";
$curlMerchantResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201 && $curlMerchantResponse['merchant']['MerchantId'] != '' ) 
 {
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
if(isset($merchantInfo['Category']) && !empty($merchantInfo['Category'])) {
	$merchantCategory			= 	explode(',',$merchantInfo['Category']);
}

//echo "<pre>"; echo print_r($ProductsArray); echo "</pre>";
if(isset($_POST['mystore_submit']) && $_POST['mystore_submit'] == 'SAVE CHANGES'){
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
		$merchantInfo['Country']			=	$_POST['Country'];
	if(isset($_POST['Facebook']))
		$merchantInfo['FBId']			=	$_POST['Facebook'];
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
					'CompanyName' 		=> $_POST['ShopName'],
					'Email' 			=> $_POST['Email'],
					'Address' 			=> $_POST['Street'],
					'PhoneNumber' 		=> $_POST['Phone'],
					'WebsiteUrl' 		=> $_POST['Website'],
					'ShopDescription' 	=> $_POST['ShopDescription'],
					'Description' 		=> $_POST['MoreInfo'],
					'OpeningHours' 		=> $openTiming,
					'IconPhoto' 		=> $iconPath,
					'MerchantPhoto' 	=> $imagePath,
					'IconExist'			=> $_POST['old_icon_photo'],
					'MerchantExist'		=> $_POST['old_merchant_photo'],
					'PriceRange' 		=> $prizeRange,
					'Categories' 		=> $_POST['categorySelected'],
					'DiscountTier' 		=> $_POST['DiscountTier'],
					'City'				=> $_POST['City'],
					'State'				=> $_POST['State'],
					'ZipCode'			=> $_POST['ZipCode'],
					'Country'			=> $_POST['Country'],
					'FBId'				=> $_POST['Facebook'],
					'TwitterId'			=> $_POST['Twitter']
				);
	$url			=	WEB_SERVICE.'v1/merchants/';
	$method			=	'PUT';
	$curlResponse	=	curlRequest($url,$method,json_encode($data), $_SESSION['merchantInfo']['AccessToken']);
	//echo "<pre>"; print_r($curlResponse ); echo "</pre>";die();
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

commonHead();
?>

<body class="skin-blue fixed" onload="fieldfocus('Address');">
		<?php top_header(); ?>
		<section class="content">
		<div class="col-lg-8 col-md-11 box-center">
			<section class="content-header">
                <h1>My Store</h1>
            </section>
			<?php if(isset($msg) && $msg != '') { ?>
				<div align="center" class="alert <?php  echo $class;  ?> alert-dismissable col-xs-12 col-sm-5 col-lg-3"><i class="fa <?php  echo $class_icon;  ?>"></i>  <?php echo $msg; ?></div>
			<?php } ?>
			<form action="" name="mystore_form" id="mystore_form"  method="post">
				<div class="row clear">
					<div class="col-sm-12 col-md-12">					
					<div class="box box-primary no-padding">
						<div class="box-header no-padding">
							<h3 class="box-title"></h3>
						</div>
						<div class="form-group col-sm-12 col-md-12">
							<div class="col-sm-8 col-md-8 control-label no-padding">
							<label class="control-label" >Shop Name</label>
							<p class="help-block col-sm-12 no-padding">Name is visible on your card in mobile app</p>
							</div>
							<div class="col-sm-4 col-md-4 no-padding"><input type="text" name="ShopName" class="form-control valid"  id="ShopName" value="<?php if(isset($merchantInfo['CompanyName']) && !empty($merchantInfo['CompanyName'])) echo $merchantInfo['CompanyName'];?>"></div>
						</div>
						<div class="form-group col-sm-12 col-md-12">
							<label class="col-sm-12 col-md-12 control-label no-padding border-right"><span>Shop Description</span><em></em></label>
							<p class="help-block col-sm-12 no-padding">Shop Description(max. 80 characters)</p>
							<div class="col-sm-12 no-padding"><input type="text" name="ShopDescription"  id="ShopDescription" maxlength="80" placeholder="e.g: The best burger" class="form-control valid" value="<?php if(isset($merchantInfo['ShortDescription']) && !empty($merchantInfo['ShortDescription'])) echo $merchantInfo['ShortDescription'];?>"></div>
						</div>
						<div class="form-group col-sm-12 col-sm-12">
							<label class="col-sm-8 control-label no-padding">Category</label>
							<div class="col-sm-4 control-label no-padding">
								<select name="Category" id="Category" class="form-control" onchange="showCategory(this.value)">
									<option value="">Select</option>	
									<?php if(isset($categories) && !empty($categories)) {
										foreach($categories as $key=>$val) {
										if($key != 'totalCount') {
									?>
									<option value="<?php echo $val['CategoryId'];?>"  style="background-image:url(<?php echo $val['CategoryIcon']; ?>);"><?php echo ucfirst($val['CategoryName']);?></option>
									<?php } } } ?>
								</select><span id="njkj"></span>
							</div>
						</div>
						<div class="form-group col-sm-12 col-md-12">
							<?php if(isset($categories) && !empty($categories)) {
								foreach($categories as $key=>$val) {
								//echo "<pre>"; echo print_r($val); echo "</pre>";
							?>
								<span id="cat_id_<?php echo $val['CategoryId']; ?>" <?php if(in_array($val['CategoryId'],$merchantCategory )){ ?> class="cat_box" <?php } else {?> style="display:none;" class="cat_box" <?php } ?>>
									<img width="30" src="<?php echo $val['CategoryIcon']; ?>"/>
									<span class="cname"><?php echo ucfirst($val['CategoryName']);?></span>
									<a class="delete" title="Remove" href="javascript:void(0)" onclick="removeCategory(<?php echo $val['CategoryId']; ?>,'<?php echo $val['CategoryIcon']; ?>')">
										<i class="fa fa-trash-o "></i>
									</a>
								</span>
							<?php  } } ?>
							<input type="Hidden" id="categorySelected" name="categorySelected" value="<?php //if(isset($newCategory) && $newCategory>0) echo $newCategory;?>"/>
						</div>
						<div class="form-group col-sm-12 col-md-12 clear">
							<label class="col-sm-8 control-label no-padding">Price Range</label>
							<span class="col-sm-4 control-label no-padding">
								<div class="col-xs-5 col-md-5 no-padding">
									<div class="col-xs-2 col-md-2 no-padding LH30">$</div>
									<div class="col-xs-9 col-md-10 no-padding"><input type="Text" onchange="price_val(this.value);" maxlength="7" name="min_price" value="<?php if(isset($min_val)) echo $min_val;?>" id="min_price" onkeypress="return isNumberKey_price(event);" class="form-control"></div>
								</div>
								<div class="col-xs-1 col-md-2 no-padding LH30" align="center"><strong>to</strong></div>
								<div class="col-xs-5 col-md-5 no-padding">
									<div class="col-xs-2 col-md-2 no-padding LH30">$</div>
									<div class="col-xs-9 col-md-10 no-padding"><input type="Text" onchange="price_val(this.value);" maxlength="7" name="max_price" value="<?php if(isset($min_val)) echo $max_val;?>" id="max_price" onkeypress="return isNumberKey_price(event);" class="form-control"></div>
								</div>
								<input  type="hidden" id="priceValidation" name="priceValidation" value="">
							</span>
						</div>
						<div class="form-group col-sm-12 col-sm-12">
							<label class="col-sm-8 control-label no-padding">Discount Scheme</label>
							<div class="col-sm-4 control-label no-padding">
								<div class=" col-sm-5 no-padding">
								<select class="form-control" id="DiscountTier" name="DiscountTier" onclick="selectPrice(this.value,'<?php if(isset($ProductsArray) && count($ProductsArray) > 0) echo "1"; else echo "0"; ?>');">
									<option value="" >Select
									<?php if(isset($discountTierArray) && is_array($discountTierArray) && count($discountTierArray) > 0) {
											foreach($discountTierArray as $key=>$value){
									 ?>
									<option value="<?php echo $key; ?>" <?php if(isset($merchantInfo['DiscountTier']) &&  $merchantInfo['DiscountTier'] == $value.'%' ) echo 'selected';?>><?php echo $value.'%'; ?>
									<?php } } ?>
								</select>
								</div>
							</div>
						</div>
						<div class="form-group col-sm-12 col-md-12">
							<div class="col-sm-8 col-md-8 control-label no-padding">
								<label class="control-label" >Icon</label>
								<p class="help-block col-sm-12 no-padding">(dimension 100x100)</p>
							</div>
							<div class="col-sm-4 col-md-4 no-padding">
								<div class="col-xs-12 no-padding"><input type="file"  name="icon_photo" id="icon_photo" onchange="return ajaxAdminFileUploadProcess('icon_photo');"  /><br>
								<span class="error" for="empty_merchant_photo" generated="true" style="display: none">Icon is required</span>
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
						</div>
						<div class="form-group col-sm-12 col-md-12">
							<div class="col-sm-8 col-md-8 control-label no-padding">
								<label class="control-label" >Logo</label>
								<p class="help-block col-sm-12 no-padding">(dimension 640x260)</p>
							</div>
							<div class="col-sm-4 col-md-4 no-padding">
								<div class="col-xs-12 no-padding"><input type="file"  name="merchant_photo" id="merchant_photo" onclick="" onchange="return ajaxAdminFileUploadProcess('merchant_photo');"   /><br>
								<span class="error" for="empty_merchant_photo" generated="true" style="display: none">Image is required</span>
								</div>
								
								<div class="col-xs-12 no-padding"> 
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
						<div class="form-group col-sm-12">
							<label class="col-sm-12 control-label no-padding border-right"><span>Slideshow Pictures</span><em></em></label>
							<p class="help-block no-padding">Upload upto 10 pictures(resolution is 1000*350 pixels, bigger images will scaled down automatically)</p>


							<div class="row">	
								<div class="col-sm-6 col-xs-12 form-group" id='temp0'>
									<div class="col-xs-1 no-padding" id="imgcount">1.</div>
									<div class="col-xs-11 no-padding" align="center" id="drop-files" ondragover="return false">
										<div  class="photo_gray_bg_light" >
											<img style="vertical-align:top" class="resize" id="imgdrag" src="<?php SITE_PATH;?>webresources/images/no_photo_my_store.png" width="330" height="160" alt="">
											
											<!-- upload image place it here -->
											<!-- <img style="vertical-align:top" class="" src="<?php SITE_PATH;?>webresources/images/banner1.jpg" width="330" height="160" alt=""> -->
										</div>
										
										<div class="drag_pos" id="holder">
											Drag & drop an image or
											<span>
											choose a file to upload
											<input type="file" name="myStore" id="myStore" onchange="return ajaxAdminFileUploadProcess('myStore');" >
											</span>
										</div>
										
									</div>
									<div class="col-xs-12">&nbsp;</div>
									
									<div class="col-xs-2 col-md-1 clear">&nbsp;</div>
									<div class="col-xs-10 col-md-11" align="center">
										<!--<input type="button" name="Upload" id="Upload" value="Upload" title="Upload" class="box-center btn btn-success  col-xs-10">-->
									</div>
								</div>								
							</div>							
							<input type='hidden' id="totalImage" name="totalImage" value="0" />
							<input type='hidden' id="uploadImage" name="uploadImage" value="" />
							<input type='hidden' id="dragImage" name="dragImage" value="" />
							<input type='hidden' id="dragImageValues" name="dragImageValues" value="" />
						</div>
						<div class="form-group col-sm-12 col-md-12">
							<label class="col-sm-12 control-label no-padding border-right"><span>More Info</span><em></em></label>
							<p class="help-block no-padding">More info ...............(max. 300 characters) </p>
							<span class="col-md-12 no-padding"><input type="text" name="MoreInfo"  id="MoreInfo" maxlength="300" placeholder="More Info" class="form-control valid" value="<?php if(isset($merchantInfo['Description']) && !empty($merchantInfo['Description'])) echo $merchantInfo['Description'];?>"></span>	
						</div>
						<div class="form-group col-sm-12 col-md-12 clearfix no-padding">
						<div class="form-group col-sm-12 col-md-12">
							<label class="col-sm-12  control-label no-padding border-right"><span>Contact Info</span><em></em></label>
							<div class="form-group col-sm-3 col-md-2 no-padding"><input type="button" title="Use my location" value="Use my location" class="btn bg-olive btn-md " /></div>
						
							<div class="col-sm-9 col-md-10 no-padding">
								<div class="show-grid form-group col-sm-9 no-padding">
									<div class="form-group col-sm-12 no-padding"><input type="text"  id="Street" name="Street" value="<?php if(isset($merchantInfo['Address']) && !empty($merchantInfo['Address'])) echo $merchantInfo['Address'];?>" placeholder="Street Address" class="form-control"></div>
									<div class="form-group col-sm-7 no-padding"><input type="text"  id="City" name="City" value="<?php if(isset($merchantInfo['City']) && !empty($merchantInfo['City'])) echo $merchantInfo['City'];?>" placeholder="City" class="form-control"></div>
									<div class="form-group col-sm-5 no-padding-right"><input type="text"  id="ZipCode" name="ZipCode" value="<?php if(isset($merchantInfo['PostCode']) && !empty($merchantInfo['PostCode'])) echo $merchantInfo['PostCode'];?>" placeholder="ZIP" class="form-control"></div>
									<div class="form-group col-sm-12 no-padding"><input type="text"  id="State" name="State" value="<?php if(isset($merchantInfo['State']) && !empty($merchantInfo['State'])) echo $merchantInfo['State'];?>" placeholder="State" class="form-control"></div>	
									<div class="form-group col-sm-12 no-padding"><input type="text"  id="Country" name="Country" value="<?php if(isset($merchantInfo['Country']) && !empty($merchantInfo['Country'])) echo $merchantInfo['Country'];?>" placeholder="Country" class="form-control"></div>	
								</div>
								<div class="show-grid form-group col-sm-9 no-padding">
									<div class="form-group col-sm-12 no-padding"><input type="text"  id="Phone" name="Phone" value="<?php if(isset($merchantInfo['PhoneNumber']) && !empty($merchantInfo['PhoneNumber'])) echo $merchantInfo['PhoneNumber'];?>" placeholder="Phone" class="form-control"></div>	
									<div class="form-group col-sm-12 no-padding"><input type="text"  id="Email" name="Email" value="<?php if(isset($merchantInfo['Email']) && !empty($merchantInfo['Email'])) echo $merchantInfo['Email'];?>" placeholder="Email" class="form-control"></div>	
									<div class="form-group col-sm-12 no-padding"><input type="text"  id="Website" name="Website" value="<?php if(isset($merchantInfo['WebsiteUrl']) && !empty($merchantInfo['WebsiteUrl'])) echo $merchantInfo['WebsiteUrl'];?>" placeholder="Website" class="form-control"></div>
								</div>
								<div class="form-group col-sm-9 no-padding"><input type="text"  id="Facebook" name="Facebook" value="<?php if(isset($merchantInfo['FBId']) && !empty($merchantInfo['FBId'])) echo $merchantInfo['FBId'];?>" placeholder="Facebook" class="form-control"></div>	
								<div class="form-group col-sm-9 no-padding"><input type="text"  id="Twitter" name="Twitter" value="<?php if(isset($merchantInfo['TwitterId']) && !empty($merchantInfo['TwitterId'])) echo $merchantInfo['TwitterId'];?>" placeholder="Twitter" class="form-control"></div>													
								<!-- <div class="form-group col-sm-7 no-padding"><input type="text"  id="TimeCheck24" name="TimeCheck24" value="" placeholder="HH:MM:SS" class="form-control"></div>													
								<div class="form-group col-sm-7 no-padding"><input type="text"  id="TimeCheck" name="TimeCheck" value="" placeholder="HH:MM AM/PM" class="form-control"></div>		 -->											
							</div>
						</div>
						<div class="form-group col-sm-12 col-md-12">
							<label class="col-sm-12 col-md-12 control-label no-padding border-right"><span>Business Hours</span><em></em></label>
							<p class="help-block col-sm-12 no-padding">Business Hours leave as HH MM AM/PM for not service</p>
							<?php 
							if(isset($days_array) && count($days_array)>0) {
							foreach($days_array as $key=>$val){ ?>
							<div class="col-xs-12 no-padding form-group <?php if($key != 0) echo "rowHide";?>"  <?php if(isset($merchantInfo['OpeningHours'][0]['DateType']) && $merchantInfo['OpeningHours'][0]['DateType'] == '1' && $key != 0) echo 'style="display:none;"'; ?>>
								<?php if($key == 0) { ?>
									<div class="col-sm-4 col-lg-3 col-xs-12 no-padding">
										<input type="checkbox" name="samehours" id="samehours"  onclick="return hideAllDays();" <?php if(isset($merchantInfo['OpeningHours'][0]['DateType']) && $merchantInfo['OpeningHours'][0]['DateType'] == '1') echo "checked"; ?>>&nbsp;Same for all days 
										<input type="hidden" id="showdays" name="showdays" value="<?php if(isset($merchantInfo['OpeningHours'][0]['DateType']) && $merchantInfo['OpeningHours'][0]['DateType'] == '1') echo 'checked'; ?>"/>
									</div>
									<div class="col-xs-6 col-sm-4 col-xs-6 no-padding LH30">From :</div>
									<div class="col-xs-5 col-sm-4 col-xs-6 no-padding LH30">To :</div>
									
								<?php } ?>
								<div class="col-sm-4  col-lg-3 col-xs-12  no-padding LH30"><strong><span class="<?php if($key == 0) echo "rowshow";?>"><?php if(isset($merchantInfo['OpeningHours'][0]['DateType']) && $merchantInfo['OpeningHours'][0]['DateType'] == '1' && $key == 0) echo "Monday to Sunday"; else echo $val.""; ?></span></strong></div>
								<div class="col-sm-4 col-xs-6  no-padding select_sm">
									
										<select class="form-control" id="fromhours_list<?php echo $key; ?>" name="fromhours_list<?php echo $key; ?>" onchange="return setTime('<?php echo $key; ?>');">
											<option value="">HH</option>
											<?php foreach($admin_hours_array as $keyhr=>$value){  ?>
													<option value="<?php echo $keyhr; ?>" <?php if(isset($merchantInfo['OpeningHours'][$key]['Start']['hr']) && $merchantInfo['OpeningHours'][$key]['Start']['hr'] == $keyhr) echo "selected"; ?>><?php echo $value; ?></option>
											<?php } ?>
										</select>
										<select class="form-control" id="fromminute_list<?php echo $key; ?>" name="fromminute_list<?php echo $key; ?>" onchange="return setTime('<?php echo $key; ?>');">
											<option value="">MM</option>
											<?php foreach($admin_minute_array as $keymin=>$value){  ?>
													<option value="<?php echo $keymin; ?>" <?php if(isset($merchantInfo['OpeningHours'][$key]['Start']['min']) && $merchantInfo['OpeningHours'][$key]['Start']['min'] == $keymin) echo "selected"; ?>><?php echo $value; ?></option>
											<?php } ?>
										</select>
										<select class="form-control" id="fromampm_list<?php echo $key; ?>" name="fromampm_list<?php echo $key; ?>" onchange="return setTime('<?php echo $key; ?>');">
											<option value="">AM/PM</option>
											<?php foreach($admin_ampm_array as $keyampm=>$value){  ?>
													<option value="<?php echo $keyampm; ?>" <?php if(isset($merchantInfo['OpeningHours'][$key]['Start']['ampm']) && $merchantInfo['OpeningHours'][$key]['Start']['ampm'] == $keyampm) echo "selected"; ?>><?php echo $value; ?></option>
											<?php } ?>
										</select>
										<input type="hidden"  class="form-control" id="from1_<?php echo $key; ?>" name="from1_<?php echo $key; ?>" onchange="return setTime('<?php echo $key; ?>');" value="<?php if(isset($merchantInfo['OpeningHours'][$key]['Start']['fromTime'])) echo $merchantInfo['OpeningHours'][$key]['Start']['fromTime']; ?>" >
								</div>
								<div class="col-sm-4 col-xs-6  no-padding select_sm">
										<select class="form-control" id="tohours_list<?php echo $key; ?>" name="tohours_list<?php echo $key; ?>" onchange="return setTime('<?php echo $key; ?>');">
											<option value="">HH</option>
											<?php foreach($admin_hours_array as $keyhr=>$value){  ?>
													<option value="<?php echo $keyhr; ?>" <?php if(isset($merchantInfo['OpeningHours'][$key]['End']['hr']) && $merchantInfo['OpeningHours'][$key]['End']['hr'] == $keyhr) echo "selected"; ?>><?php echo $value; ?></option>
											<?php } ?>
										</select>
										<select class="form-control" id="tominute_list<?php echo $key; ?>" name="tominute_list<?php echo $key; ?>" onchange="return setTime('<?php echo $key; ?>');">
											<option value="">MM</option>
											<?php foreach($admin_minute_array as $keymin=>$value){  ?>
													<option value="<?php echo $keymin; ?>" <?php if(isset($merchantInfo['OpeningHours'][$key]['End']['min']) && $merchantInfo['OpeningHours'][$key]['End']['min'] == $keymin) echo "selected"; ?>><?php echo $value; ?></option>
											<?php } ?>
										</select>
										<select class="form-control" id="toampm_list<?php echo $key; ?>" name="toampm_list<?php echo $key; ?>" onchange="return setTime('<?php echo $key; ?>');">
											<option value="">AM/PM</option>
											<?php foreach($admin_ampm_array as $keyampm=>$value){  ?>
													<option value="<?php echo $keyampm; ?>" <?php if(isset($merchantInfo['OpeningHours'][$key]['End']['ampm']) && $merchantInfo['OpeningHours'][$key]['End']['ampm'] == $keyampm) echo "selected"; ?>><?php echo $value; ?></option>
											<?php } ?>
										</select>
									<input type="hidden" class="form-control" id="to1_<?php echo $key; ?>" name="to1_<?php echo $key; ?>" onchange="return setTime('<?php echo $key; ?>');" value="<?php if(isset($merchantInfo['OpeningHours'][$key]['End']['toTime'])) echo $merchantInfo['OpeningHours'][$key]['End']['toTime']; ?>" ></div>
								<input type="hidden" id="id_<?php echo $key; ?>" name="id_<?php echo $key; ?>" value="<?php if(isset($merchantInfo['OpeningHours'][$key]['id'])) echo $merchantInfo['OpeningHours'][$key]['id']; ?>" >
							</div>
							<div class="col-md-12">
								<input type="hidden" id="row_<?php echo $key; ?>" name="row_<?php echo $key; ?>" value="<?php if(!empty($merchantInfo['OpeningHours'][$key]['Start']['fromTime']) || !empty($merchantInfo['OpeningHours'][$key]['End']['toTime'])) echo "1"; ?>" />
								<span id="error_<?php echo $key; ?>" style="color:red;"></span>
							</div>
							<?php } } ?>
						</div>
					</div>
				</div>				
				</div>
				<div class="footer col-xs-12 " align="center"> 
						<input type="submit" name="mystore_submit" id="mystore_submit" value="SAVE CHANGES" title="Save Changes" class="btn btn-success col-xs-5 box-center">
				</div>
				<div class="footer col-xs-12 " align="center"> <br>
						<a href="Dashboard" name="cancel" id="cancel">Cancel</a>
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


	// Makes sure the dataTransfer information is sent when we
	// Drop the item in the drop box.
	jQuery.event.props.push('dataTransfer');
	
	// Get all of the data URIs and put them in an array
	var dataArray = [];
	
	// Bind the drop event to the drop zone.
	$('#drop-files').bind('drop', function(e) {
		if(dataArray.length < 10) {
			var files = e.dataTransfer.files;
			// For each file
			$.each(files, function(index, file) {
				// Some error messaging
				if (!files[index].type.match('image.*')) {
					alert('Hey! Images only');
					return false;
				}
				
				// Start a new instance of FileReader
				var fileReader = new FileReader();
				// When the file reader loads initiate a function
				fileReader.onload = (function(file) {				
										return function(e) { 					
											// Push the data URI into an array
											dataArray.push({name : file.name, value : this.result});
											index =	dataArray.length - 1;
											
											orgtotalImage	=	parseInt($('#totalImage').val());
											totalImage	=	orgtotalImage + 1;
											$('#totalImage').val(totalImage);
											imgcount	=	totalImage + 1;
											
											dragimg	=	$('#dragImage').val()
											if(dragimg != '') 
												$('#dragImage').val(dragimg+','+totalImage)
											else
												$('#dragImage').val(totalImage)
												
											dragImageValues	=	$('#dragImageValues').val()
											if(dragImageValues != '') 
												$('#dragImageValues').val(dragImageValues+'######'+this.result)
											else
												$('#dragImageValues').val(this.result)
											
											imgcontent	=	'<div class="col-sm-6 col-xs-12 form-group" id="temp'+totalImage+'"><div class="col-xs-1 no-padding">'+totalImage+'.</div><div class="col-xs-11 no-padding" align="center">';
											imgcontent	+=	'<div  class="photo_gray_bg"><img style="vertical-align:top" class="resize" src="'+this.result+'" height="160" alt=""></div></div>';
											if(totalImage == 10) {
												imgcontent	+=	'<div class="col-xs-12">&nbsp;</div><div class="col-xs-2 col-md-1 clear">&nbsp;</div>';
												imgcontent	+=	'<div class="col-xs-10 col-md-11" align="center"><!--<input type="button" name="Upload1" id="Upload1" value="Upload" title="Upload" class="box-center btn btn-success  col-xs-10">--></div></div>';
												$(imgcontent).insertBefore('#temp0');
												$('#temp0').remove();
											} else {
												$(imgcontent).insertBefore('#temp0');	
												imgcontent	+=	'<div class="col-xs-12">&nbsp;</div><div class="col-xs-2 col-md-1 clear">&nbsp;</div></div>';
												$('#imgcount').html(imgcount+'.');
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
	
	$('#mystore_submit').click(function() {	
		var dragimg	=	$('#dragImage').val();
		var res 	= 	dragimg.split(",");
		alert(res.length)
		$.each(dataArray, function(index, file) {			
			$.post('models/upload.php?img='+res[index], dataArray[index], function(data) {				
			});	
			var tt	=	index + 1;
			alert(index+'###'+tt)
		});
		return false;
	});
});

</script>
