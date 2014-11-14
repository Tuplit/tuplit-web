<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
global $admin_days_array;
global $BusinessTypeArray;
global $HowyouHeared;
require_once('controllers/MerchantController.php');
$MerchantObj   	=   new MerchantController();
require_once('controllers/ProductController.php');
$ProductObj   	=   new ProductController();
require_once('controllers/AdminController.php');
$adminLoginObj   	=   new AdminController();
require_once('controllers/LocationController.php');
$locationObj   	=   new LocationController();
require_once('controllers/CurrencyController.php');
$currencyObj   	=   new CurrencyController();
require_once("includes/phmagick.php");
$date_now 		= date('Y-m-d H:i:s');
$cat_id_array = array();
$image_path		=	$cimage_path =  $ExistCondition = $Email_exists = '';

$categories 	= $MerchantObj->getCategories();
$locCon			= ' Status	=	1 and fkCurrencyId != 0 order by Location asc';
$tempLocations	 	= $locationObj->getLocationArray('*', $locCon);
if($tempLocations) {
	foreach($tempLocations as $val)
		$locations[$val->id]	=	$val;
}
$curCon			= ' Status	=	1 order by Currency asc';
$tempCurrencies	 	= $currencyObj->getCurrencyArray('*', $curCon);
if($tempCurrencies) {
	foreach($tempCurrencies as $val)
		$currencies[$val->fkLocationId]	=	$val;
}

if(isset($_GET['editId']) && $_GET['editId'] != '' ){
	$merchantListResult  		= $MerchantObj->selectMerchantDetail($_GET['editId']);
	$merchantOpeningHoursResult = $MerchantObj->selectOpeningHoursDetail($_GET['editId']);
	$merchantOpeningHoursResult	= formOpeningHours($merchantOpeningHoursResult);
	$merchantcategorylist  		= $MerchantObj->selectMerchantCategory($_GET['editId']);
	$ProductsArray				= $ProductObj->getProductNamesList($_GET['editId']);
	$cat_id_array 				= array(); 
	$cat_id_values 				= '';
	if(count($merchantcategorylist) > 0) {		
		$cat_id_array  = explode(',',$merchantcategorylist[0]->cat_id);
		$cat_id_values = $merchantcategorylist[0]->cat_id;			
	}
	if(!empty($cat_id_values))
		$cat_id_values = rtrim($cat_id_values,',');
	if(isset($merchantListResult) && is_array($merchantListResult) && count($merchantListResult) > 0){
		$FirstName 							= 	$merchantListResult[0]->FirstName;
		$LastName 							= 	$merchantListResult[0]->LastName;
		$Email 								= 	$merchantListResult[0]->Email;
		$PhoneNumber 						= 	$merchantListResult[0]->PhoneNumber;
		//$BusinessName 						= 	$merchantListResult[0]->BusinessName;
		$BusinessTypeval 					= 	$merchantListResult[0]->BusinessType;
		$CompanyName 						= 	$merchantListResult[0]->CompanyName;
		//$CompanyNumber 						= 	$merchantListResult[0]->RegisterCompanyNumber;
		//$Address 							= 	$merchantListResult[0]->Address;
		$Country 							= 	$merchantListResult[0]->Country;
		$Postcode 							= 	$merchantListResult[0]->PostCode;
		$Currency 							= 	$merchantListResult[0]->Currency;
		$HowHeared 							= 	$merchantListResult[0]->HowHeared;
		$WebsiteUrl							= 	$merchantListResult[0]->WebsiteUrl;
		$Location							= 	$merchantListResult[0]->Location;
		//$ItemsSold							= 	$merchantListResult[0]->ItemsSold;
		$Description						= 	$merchantListResult[0]->Description;
		$ShortDescription					= 	$merchantListResult[0]->ShortDescription;
		if(isset($merchantListResult[0]->Icon) && $merchantListResult[0]->Icon != ''){
			$photo 				= 	$merchantListResult[0]->Icon;
			$photo = $merchantListResult[0]->Icon;
			if(SERVER){
				if(image_exists(6,$photo))
					$image_path = MERCHANT_ICONS_IMAGE_PATH.$photo;
			}else{
				if(file_exists(MERCHANT_ICONS_IMAGE_PATH_REL.$photo))
					$image_path = MERCHANT_ICONS_IMAGE_PATH.$photo;
			}
		}
		if(isset($merchantListResult[0]->Image) && $merchantListResult[0]->Image != ''){
			$cmerchant_image 	= 	$merchantListResult[0]->Image;
			if(SERVER){
				if(image_exists(7,$cmerchant_image))
					$cimage_path = MERCHANT_IMAGE_PATH.$cmerchant_image;
			}else{
				if(file_exists(MERCHANT_IMAGE_PATH_REL.$cmerchant_image))
					$cimage_path = MERCHANT_IMAGE_PATH.$cmerchant_image;
			}
		}
	}
}
if(isset($_POST['submit']) && $_POST['submit'] != ''){
	$_POST          =   unEscapeSpecialCharacters($_POST);
   	$_POST          =   escapeSpecialCharacters($_POST);
	if(isset($_POST['Email']))
		$Email      		= 	$_POST['Email'];
	if(isset($_POST['FirstName']) )
		$FirstName 			= 	$_POST['FirstName'];
	if(isset($_POST['LastName']) )
		$LastName 			= 	$_POST['LastName'];
	if(isset($_POST['PhoneNumber']))
		$PhoneNumber   		= 	$_POST['PhoneNumber'];
	/*if(isset($_POST['BusinessName']) )
		$BusinessName     	= 	$_POST['BusinessName'];*/
	if(isset($_POST['BusinessType']) )
		$BusinessTypeval    = 	$_POST['BusinessType'];
	if(isset($_POST['CompanyName']) )
		$CompanyName     	= 	$_POST['CompanyName'];
	/*if(isset($_POST['CompanyNumber']) )
		$CompanyNumber     	= 	$_POST['CompanyNumber'];*/
	if(isset($_POST['Country']) )
		$Country     		= 	$locations[$_POST['Country']]->Location;
	if(isset($_POST['Currency']) )
		$Currency     		= 	$_POST['Currency'];
		//$Currency     		= 	$currencies[$_POST['Currency']]->Code;
	if(isset($_POST['Postcode']))
		$Postcode   		= 	$_POST['Postcode'];
	if(isset($_POST['Address']))
		$Address   			= 	$_POST['Address'];
	if(isset($_POST['ReferedBy']))
		$HowHeared       	= 	$_POST['ReferedBy'];
	
	if(isset($locations) && count($locations) > 0)
		$_POST['Country'] =  $locations[$_POST['Country']]->Location;
	if(isset($currencies) && count($currencies) > 0)
		$_POST['Currency'] =  $_POST['Currency'];
		//$_POST['Currency'] =  $currencies[$_POST['Currency']]->Code;
	
	$iconName = $iconPath = $imageName = $imagePath = $icimg = $imimg = '';
	$_POST['ipaddress']     = 	ipAddress();
	if($Email != '')
		$ExistCondition 	.= 	"  (Email = '".$Email."' ";
	if($_POST['submit'] == 'Save')
		$id_exists 			= 	") and id != '".$_POST['merchant_id']."' and Status in (1,2) ";
	else
		$id_exists 			= 	" ) and Status in (1,2) ";
	$field 					= 	" * ";	
	$ExistCondition 		.= 	$id_exists;
	$alreadyExist   		= 	$MerchantObj->selectMerchantDetails($field,$ExistCondition);	
	if(isset($alreadyExist) && is_array($alreadyExist) && count($alreadyExist) > 0){
		if(($alreadyExist[0]->Email == $Email) && ($Email != ''))
			$Email_exists 	= 	1;
	}	
	if($Email_exists != '1' ){
		if($_POST['submit'] == 'Save'){	
			//echo "<pre>"; echo print_r($_POST); echo "</pre>";die();
			$merchantListResult  = $MerchantObj->selectMerchantDetail($_POST['merchant_id']);
			//from - name of from hour, to - name of to hour, set - validator name 
			if(isset($merchantListResult) && is_array($merchantListResult) && count($merchantListResult) > 0){
				if (isset($_POST['icon_photo_upload']) && !empty($_POST['icon_photo_upload'])) {
					if(file_exists(MERCHANT_ICONS_IMAGE_PATH_REL.$merchantListResult[0]->Icon))
						unlink(MERCHANT_ICONS_IMAGE_PATH_REL.$merchantListResult[0]->Icon);
					$iconName 				= $_POST['merchant_id'] . '_' . strtotime($date_now) . '.png';
				   	$tempIconPath 			= TEMP_USER_IMAGE_PATH_REL . $_POST['icon_photo_upload'];
					$iconPath 				= UPLOAD_MERCHANT_ICONS_IMAGE_PATH_REL . $iconName;
					$oldIconName			= $_POST['name_icon_photo'];
					if ( !file_exists(UPLOAD_MERCHANT_ICONS_IMAGE_PATH_REL) ){
				  		mkdir (UPLOAD_MERCHANT_ICONS_IMAGE_PATH_REL, 0777);
					}
					//copy($tempIconPath,$iconPath);
					imagethumb_addbg($tempIconPath, $iconPath,'','',100,100);
					/*$phMagick = new phMagick($tempIconPath);
					$phMagick->setDestination($iconPath)->resize(100,100);*/
					if (SERVER){
						if($oldIconName!='') {
							if(image_exists(6,$oldIconName)) {
								deleteImages(6,$oldIconName);
							}
						}
						uploadImageToS3($iconPath,6,$iconName);
						unlink($iconPath);
					}
					unlink(TEMP_USER_IMAGE_PATH_REL.$_POST['icon_photo_upload']);
				}
				if (isset($_POST['merchant_photo_upload']) && !empty($_POST['merchant_photo_upload'])) {
					if(file_exists(MERCHANT_IMAGE_PATH_REL.$merchantListResult[0]->Image))
						unlink(MERCHANT_IMAGE_PATH_REL.$merchantListResult[0]->Image);
					$imageName 				= $_POST['merchant_id'] . '_' . strtotime($date_now) . '.png';
				   	$tempImagePath 			= TEMP_USER_IMAGE_PATH_REL . $_POST['merchant_photo_upload'];
					$imagePath 				= UPLOAD_MERCHANT_IMAGE_PATH_REL . $imageName;
					$oldImageName			= $_POST['name_merchant_photo'];
					if ( !file_exists(UPLOAD_MERCHANT_IMAGE_PATH_REL) ){
				  		mkdir (UPLOAD_MERCHANT_IMAGE_PATH_REL, 0777);
					}
					//copy($tempImagePath,$imagePath);
					imagethumb_addbg($tempImagePath, $imagePath,'','',640,260);
					/*$phMagick = new phMagick($tempImagePath);
					$phMagick->setDestination($imagePath)->resize(640,240);*/
					
					if (SERVER){
						if($oldImageName!='') {
							if(image_exists(7,$oldImageName)) {
								deleteImages(7,$oldImageName);
							}
						}
						uploadImageToS3($imagePath,7,$imageName);
						unlink($imagePath);
					}
				}
				$MerchantObj->updateDetails($_POST,$iconName,$imageName);	
				$MerchantObj->updateShoppingHours($_POST);
			}
			unset($_POST);
			header("location:Merchants?msg=2");
		}
		else if($_POST['submit'] == 'Add'){	
			/*if(isset($locations) && count($locations) > 0)
				$_POST['Country'] =  $locations[$_POST['Country']]->Location;
			if(isset($currencies) && count($currencies) > 0)
				$_POST['Currency'] =  $currencies[$_POST['Currency']]->Code;*/
			$merchantId	=	$MerchantObj->insertDetails($_POST);	
			$fields = '*';
			$condition = ' 1';
			$login_result 					= $adminLoginObj->getAdminDetails($fields,$condition);
			$mailContentArray['name'] 		= ucfirst($_POST['FirstName'].' '. $_POST['LastName']);
			$mailContentArray['toemail'] 	= $_POST['Email'];
			$mailContentArray['email'] 		= $_POST['Email'];
			$mailContentArray['password'] 	= $_POST['Password'];
			$mailContentArray['subject'] 	= 'Registration';
			$mailContentArray['from'] 		= $login_result[0]->EmailAddress;
			$mailContentArray['fileName']	= 'merchantregistration.html';
			sendMail($mailContentArray,'5');
			unset($_POST);
			header("location:Merchants?msg=1");
		}
	}
	else{
		if($Email_exists == 1){
			$error_msg   = "Email address already exists";
			$field_focus = 'Email';
		}
		$display = "block";
		$class   = "alert-danger";
		$class_icon          = "fa-warning";
	}
} 
commonHead();
?>
<body class="skin-blue" onload="return fieldfocus('FirstName');">
	<?php top_header(); ?>
	<section class="content-header no-padding">
		<!-- Content Header (Page header) -->
		<div class="col-xs-12"> 
			<h1><i class="fa <?php if(isset($_GET['editId']) && $_GET['editId'] != '' ) echo "fa-edit "; else echo 'fa-plus-circle ';?>"></i> <?php if(isset($_GET['editId']) && $_GET['editId'] != '' ) echo "Edit "; else echo 'Add ';?>Merchant</h1>
		</div>
	</section>
	
	<!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-md-12"> 
			<form name="<?php if(isset($_GET['editId']) && $_GET['editId'] != '' ) echo 'merchant_edit_form'; else echo 'merchant_add_form';?>" id="<?php if(isset($_GET['editId']) && $_GET['editId'] != '' ) echo 'merchant_edit_form'; else echo 'merchant_add_form';?>" action="" method="post" onsubmit="">
			<div class="box box-primary box-padding"> 
				<!-- left column -->
					<?php if(isset($error_msg) && $error_msg != '')  { ?> <div class="alert <?php echo $class;  ?> alert-dismissable col-lg-4  col-sm-5  col-xs-11 text-center"><i class="fa <?php echo $class_icon ;  ?>"></i>  <?php echo $error_msg;  ?></div> <?php } ?>
					<input type="Hidden" name="merchant_id" id="merchant_id" value="<?php if(isset($_GET['editId']) && $_GET['editId'] != '' ) echo $_GET['editId'];?>">
					<div class="form-group col-sm-6">
						<label>First Name</label>
						<input type="text" class="form-control" id="FirstName" name="FirstName" maxlength="100" value="<?php if(isset($FirstName) && $FirstName != '') echo ucfirst($FirstName);  ?>" >
					</div>
					<div class="form-group col-sm-6 ">
						<label>Last Name</label>
						<input type="text" class="form-control" id="LastName" name="LastName" maxlength="20" value="<?php if(isset($LastName) && $LastName != '') echo ucfirst($LastName);  ?>" >
					</div>					
					<div class="form-group col-sm-6 clear">
						<label>Email</label>
						<input type="text" class="form-control" name="Email" id="Email" maxlength="100" value="<?php if(isset($Email) && $Email != '') echo $Email;  ?>" >
					</div>
					<div class="form-group col-sm-6">
						<label>Phone</label>
						<input type="text" class="form-control" id="PhoneNumber" name="PhoneNumber" maxlength="15" onkeypress="return isNumberKey_Phone(event);" value="<?php if(isset($PhoneNumber) && $PhoneNumber != '') echo $PhoneNumber;  ?>" >
					</div>					
					<div class="form-group col-sm-6 clear">
						<label>Password</label>
						<input type="Password" class="form-control" id="Password" name="Password"  value="<?php if(isset($Password) && $Password != '' && !isset($_GET['editId'])) echo $Password;  ?>" >
					</div>
					<div class="form-group col-sm-6">
						<label>Confirm Password</label>
						<input type="Password" class="form-control" id="C_Password" name="C_Password"  value="<?php if(isset($C_Password) && $C_Password != '') echo $C_Password;  ?>" >
					</div>
					<!--<div class="form-group col-sm-6 clear">
						<label>Business Name</label>
						<input type="text" class="form-control" id="BusinessName" name="BusinessName" maxlength="50" value="<?php if(isset($BusinessName) && $BusinessName != '') echo $BusinessName;  ?>" >
					</div>
					<div class="form-group col-sm-6">
						<label>Business Type</label>
						<select class="form-control" id="BusinessType" name="BusinessType">
								<option value="">Select</option>
								<?php foreach($BusinessTypeArray as $busi_key=>$busi_type){ ?>
								<option value="<?php echo $busi_key; ?>" <?php if(isset($BusinessTypeval) && $BusinessTypeval == $busi_key) echo "selected"; ?>><?php echo $busi_type; ?></option>
								<?php } ?>
						</select>
					</div>-->
					<div class="form-group col-sm-6 clear">
						<label>Company Name</label>
						<input type="text" class="form-control" id="CompanyName" name="CompanyName" maxlength="30" value="<?php if(isset($CompanyName) && $CompanyName != '') echo $CompanyName;  ?>" >
					</div>
					<!--<div class="form-group col-sm-6">
						<label>Company Number</label>
						<input type="text" class="form-control" id="CompanyNumber" name="CompanyNumber" maxlength="15" onkeypress="return isNumberKey_Phone(event);" value="<?php if(isset($CompanyNumber) && $CompanyNumber != '') echo $CompanyNumber;  ?>" >
					</div>-->
					<div class="form-group col-sm-6">
							<label>Country</label>
							<select class="form-control" id="Country" name="Country">
								<option value="">Select</option>
									<?php if(isset($locations) && count($locations) > 0) { foreach($locations as $val){ ?>
										<option value="<?php echo $val->id; ?>" <?php if(isset($Country) && $Country == $val->Location) echo "selected"; ?>><?php echo ucfirst($val->Location)." (".$val->Code.")"; ?></option>
									<?php } } ?>
							</select>
					</div>
					<!--<div class="form-group col-sm-6">
						<label>Currency</label>
						<select class="form-control" name="Currency1" id="Currency1" disabled/>
							<option value="">Choose Currency</option>
							<?php if(isset($currencies) && !empty($currencies) && count($currencies)>0) { foreach($currencies as $code){ ?>
								<option  value="<?php echo $code->fkLocationId; ?>" <?php if(isset($Currency) && $Currency == $code->Code) echo "selected"; ?>><?php echo $code->Code; ?></option>
							<?php } } ?>
						</select>
						<input type="hidden" class="form-control" id="Currency" name="Currency" readonly="readonly" value="<?php if(isset($Currency) && $Currency != '') echo $Currency;  ?>" >
					</div> -->
					<div class="form-group col-sm-6 clear">
						<label>Postcode</label>
						<input type="text" class="form-control" id="Postcode" name="Postcode" maxlength="8" onkeypress="return isNumberKey_Phone(event);" value="<?php if(isset($Postcode) && $Postcode != '') echo $Postcode;  ?>" >
					</div>
					<!-- <div class="form-group col-sm-6 ">
						<label>Address</label>
						<textarea class="form-control" id="Address" name="Address" cols="5"><?php if(isset($Address) && $Address != '') echo $Address;  ?></textarea>
					</div> -->
					<?php if(!isset($_GET['editId'])) { ?>		
					<div class="form-group col-sm-6 clear">
						<label>How did you hear about us?</label>
						<select class="form-control" name="ReferedBy" id="ReferedBy" required />
							<option value="">Select an option</option>
							<?php foreach($HowyouHeared as $refer_key=>$referer){ ?>
								<option value="<?php echo $refer_key; ?>" <?php if(isset($HowHeared) && $HowHeared == $refer_key) echo "selected"; ?>><?php echo $referer; ?></option>
							<?php } ?>
						</select>
					</div>
					<?php } ?>
					<?php if(isset($_GET['editId'])) { ?>
					<div class="form-group col-sm-6 clear">
						<label>Website Url</label>
						<input type="text" class="form-control" id="WebsiteUrl" name="WebsiteUrl" maxlength="100" value="<?php if(isset($WebsiteUrl) && $WebsiteUrl != '') echo $WebsiteUrl;  ?>" >
					</div>
					<div class="form-group col-sm-6 ">
						<label>Location</label>
						<input type="text" class="form-control" id="Location" name="Location" maxlength="30" value="<?php if(isset($Location) && $Location != '') echo $Location;  ?>" >
					</div>
					<!--<div class="form-group col-sm-6">
						<label>Items Sold</label>
						<input type="text" class="form-control" id="ItemsSold" name="ItemsSold" maxlength="5" onkeypress="return isNumberKey_numbers(event);" value="<?php if(isset($ItemsSold) && $ItemsSold != '') echo $ItemsSold;  ?>" >
					</div>-->
					<div class="form-group col-sm-6 clear">
						<label>Short Description</label>
						<textarea class="form-control" id="ShortDescription" name="ShortDescription" maxlength="250" cols="5"><?php if(isset($ShortDescription) && $ShortDescription != '') echo $ShortDescription;  ?></textarea>	
					</div>
					<div class="form-group col-sm-6">
						<label>Description</label>
						<textarea class="form-control" id="Description" name="Description" cols="5"><?php if(isset($Description) && $Description != '') echo $Description;  ?></textarea>
					</div>		
					<?php } ?>		
					<?php if(isset($_GET['editId'])) { ?>		
					 <div class="form-group col-sm-6 clear">
						<label>Icon</label>
							<div class="col-sm-8 no-padding"> 
								<input type="file"  name="icon_photo" id="icon_photo" onchange="return ajaxAdminFileUploadProcess('icon_photo');"  /> 
								<p class="help-block no-margin">(Minimum dimension 100x100)</p>
								<span class="error" for="empty_user_photo" generated="true" style="display: none">Icon is required</span>
							</div>
							<div class="col-sm-3 no-padding" >
						      <div id="icon_photo_img">
							 	 <?php if(isset($image_path) && $cimage_path != ''){?>
								  <a onclick="return loaded;" href="<?php echo $image_path; ?>" class="fancybox" title="<?php echo ucfirst($CompanyName); ?>"><img class="img_border" src="<?php echo $image_path;?>" width="75" height="75" alt="Image"/></a>
								<?php } ?>
							  </div>								
						   </div>	
						  <?php  if(isset($_POST['icon_photo_upload']) && $_POST['icon_photo_upload'] != ''){  ?>
						  	<input type="Hidden" name="icon_photo_upload" id="icon_photo_upload" value="<?php  echo $_POST['icon_photo_upload'];  ?>">
						  <?php  }  ?>
							<input type="Hidden" name="empty_icon_photo" id="empty_icon_photo" value="<?php  if(isset($image_path) && $image_path != '') { echo $image_path; }  ?>" />
							<input type="Hidden" name="name_icon_photo" id="name_icon_photo" value="<?php  if(isset($image_path) && $image_path != '') { echo $image_path; }  ?>" />						
					</div>
					<div class="form-group col-sm-6 ">
						<label>Image</label>
						<div class="col-sm-7 no-padding"> 
							<input type="file"  name="merchant_photo" id="merchant_photo" onclick="" onchange="return ajaxAdminFileUploadProcess('merchant_photo');"  /> 
							<p class="help-block no-margin">(Minimum dimension 640x260)</p>
							<span class="error" for="empty_com_photo" generated="true" style="display: none">Image is required</span>
						</div>	
						<div class="col-sm-5 no-padding"> 
					      <div id="merchant_photo_img">
						  		<?php if(isset($cimage_path) && $cimage_path != ''){?>
							  	<a onclick="return loaded;" href="<?php echo $cimage_path; ?>" class="fancybox" title="<?php echo ucfirst($CompanyName); ?>"><img class="img_border" src="<?php echo $cimage_path;?>" width="200" height="100" alt="Image"/></a>
								<?php } ?>
						  </div>
					  	</div>	
						<?php  if(isset($_POST['merchant_photo_upload']) && $_POST['merchant_photo_upload'] != ''){  ?>
							<input type="Hidden" name="merchant_photo_upload" id="icon_photo_upload" value="<?php  echo $_POST['merchant_photo_upload'];  ?>">
						<?php  }  ?>
							<input type="Hidden" name="empty_merchant_photo" id="empty_merchant_photo" value="<?php  if(isset($cimage_path) && $cimage_path != '') { echo $cimage_path; }  ?>" />
							<input type="Hidden" name="name_merchant_photo" id="name_merchant_photo" value="<?php  if(isset($cimage_path) && $cimage_path != '') { echo $cimage_path; }  ?>" />						
					</div>
					
					<div class="form-group col-sm-12 ">
						<label>Category</label>
						<div class="col-sm-3 col-lg-3 no-padding form-group">
						<select name="Category" id="Category" class="form-control" onchange="showCategory(this.value)">
							<option value="">Select</option>	
							<?php if(isset($categories) && !empty($categories)) {
								foreach($categories as $val) {
									//if(!in_array($val->Id,$cat_id_array)) {
							?>
							
							<option value="<?php echo $val->Id; ?>" style="background-image:url(<?php echo CATEGORY_IMAGE_PATH.$val->CategoryIcon; ?>);"><?php echo ucfirst($val->CategoryName);?></option>
							<?php } } //} ?>
						</select>
						</div>
						<div class="col-sm-12 no-padding">
							<?php if(isset($categories) && !empty($categories)) {
								foreach($categories as $val) {									
							?>
							
								<span id="cat_id_<?php echo $val->Id; ?>" style="<?php if(in_array($val->Id,$cat_id_array)) echo "display:block;"; else echo "display:none;";?>"  class="cat_box">
									<img width="30" height="30" src="<?php echo CATEGORY_IMAGE_PATH.$val->CategoryIcon; ?>"/>
									<span class="cname"><?php echo ucfirst($val->CategoryName);?></i></span>
									<a class="delete" title="Remove" href="javascript:void(0)" onclick="removeCategory(<?php echo $val->Id; ?>)">
										<i class="fa fa-trash-o "></i>
									</a>
								</span>
							<?php } } ?>
							<input type="Hidden" id="categorySelected" name="categorySelected" value="<?php //echo $cat_id_values; ?>"/>
						</div>
					</div>
					<div class="form-group col-sm-3 clear">
						<div class="col-xs-12 col-sm-12 col-md-12 no-padding">
							<label>Price Scheme</label>
							<div class="form-group col-md-12 col-lg-12 no-padding ">
							<select class="form-control" id="DiscountTier" name="DiscountTier" onclick="selectPrice(this.value);">
								<option value="" >Select</option>
								<?php if(isset($discountTierArray) && is_array($discountTierArray) && count($discountTierArray) > 0) {
										foreach($discountTierArray as $key=>$value){
								 ?>
								 
								<option value="<?php echo $key; ?>" <?php if(isset($merchantListResult[0]->DiscountTier) &&  $merchantListResult[0]->DiscountTier == $key ) echo 'selected';?>><?php echo $value.'%'; ?>
								<?php } } ?>
							</select>
							</div>
						</div>
						
						
						
						
						<?php if(isset($ProductsArray) && is_array($ProductsArray) && count($ProductsArray) > 0) { 
								if(!empty($merchantListResult[0]->DiscountProductId))
									$ProductIds = explode(',',$merchantListResult[0]->DiscountProductId);
								else
									$ProductIds = array();
						?>
						
							<!--<div class="form-group col-sm-8 col-lg-1 text-center LH30">OR</div>
							<div class="form-group col-sm-6  no-padding">
								<label class="col-md-12 no-padding">Select the product list or menu to be discounted (30% and the whole menu)</label>
								<div class="col-md-12 no-padding"> 
									 <select multiple class="form-control" id="Products_List" name="Products_List[]" onclick="selectProduct(this.value);">return getPrice(this);
										<option value="all" <?php if(in_array('all',$ProductIds)) echo "selected='selected'"; ?>>Select All</option>
										<?php foreach($ProductsArray as $key=>$value){ ?>
												<option value="<?php echo $value->id; ?>" <?php if(in_array($value->id,$ProductIds)) echo "selected='selected'"; else if(in_array('all',$ProductIds)) echo "selected='selected'"; ?>><?php echo ucfirst($value->ItemName); ?></option>
										<?php } ?>
									</select>
								</div>
							</div>-->
						<?php } ?>
						
						
						<?php
							$min_val = $max_val = '';
							if(isset($merchantListResult[0]->PriceRange) && !empty($merchantListResult[0]->PriceRange)) {
								$pricerange = explode(',',$merchantListResult[0]->PriceRange);
								$min_val = $pricerange[0];
								$max_val = $pricerange[1];
							}
						?>
					</div>
					
					<div class="col-sm-6  col-xs-12 col-lg-4  form-group">
							<label>Price Range</label>
							<div class="col-md-12 col-xs-12 no-padding">							
								<div class="col-sm-5 col-xs-5 no-padding">
									<div class="col-sm-2 col-xs-2 no-padding LH30">&pound</div>
									<div class="col-sm-10 col-xs-10 no-padding"><input type="Text" name="min_price" maxlength="7" onchange="price_val(this.value);" value="<?php echo $min_val;?>" id="min_price" onkeypress="return isNumberKey(event);" class="form-control"></div>
								</div>
								<div class="col-sm-2 col-xs-2 no-padding LH30" align="center"><strong>to</strong></div>
								<div class="col-sm-5 col-xs-5 no-padding">
									<div class="col-sm-2 col-xs-2 no-padding LH30">&pound</div>
									<div class="col-sm-10 col-xs-10 no-padding"><input type="Text" name="max_price" maxlength="7" onchange="price_val(this.value);"  value="<?php echo $max_val;?>" id="max_price" onkeypress="return isNumberKey(event);" class="form-control"></div>
								</div>
								<input  type="hidden" id="priceValidation" name="priceValidation" value="">
							</div>
					</div>
										
					<div class="form-group col-xs-12  col-md-10   col-lg-8 clear">
						<div class="form-group col-xs-12 no-padding"><label>Open Hours leave as empty for not service</label></div>
							<?php 								
								if(isset($admin_days_array) && count($admin_days_array)>0) {
								foreach($admin_days_array as $key=>$val){ ?>
							<div class="col-xs-12 no-padding <?php if($key != 0) echo "rowHide";?>" <?php if(isset($merchantOpeningHoursResult[0]['DateType']) && $merchantOpeningHoursResult[0]['DateType'] == '1' && $key != 0) echo 'style="display:none;"'; ?>>
								<?php if($key == 0) { ?>
									<div class="col-sm-4 col-lg-3 col-xs-12 no-padding">
										<input type="checkbox" name="samehours" id="samehours"  onclick="return hideAllDays();" <?php if(isset($merchantOpeningHoursResult[0]['DateType']) && $merchantOpeningHoursResult[0]['DateType'] == '1') echo "checked"; ?>>&nbsp;Same for all days 
										<input type="hidden" id="showdays" name="showdays" value="<?php if(isset($merchantOpeningHoursResult[0]['id'])) echo $merchantOpeningHoursResult[0]['DateType']; else echo "0"; ?>"/>
									</div>
									<div class="col-sm-4 col-lg-4 col-xs-5 LH30"><label>From :</label></div>
									<div class="col-sm-4 col-lg-4 col-xs-5 LH30"><label>To :</label></div>
								<?php } ?>
								<div class="col-sm-4 col-lg-3  col-xs-12 col-md-4 no-padding LH30"><strong><span class="<?php if($key == 0) echo "rowshow";?>"><?php if(isset($merchantOpeningHoursResult[0]['DateType']) && $merchantOpeningHoursResult[0]['DateType'] == '1' && $key == 0) echo "<strong>Monday to Sunday :</strong> "; else echo $val." : "; ?></span></strong></div>
								<div class="col-sm-4 col-xs-6 no-padding select_sm">
									<input type="text" rowid="<?php echo $key; ?>" class="form-control timepicker" style="width:50%" id="from1_<?php echo $key; ?>" name="from1_<?php echo $key; ?>" value="<?php if(isset($merchantOpeningHoursResult[$key]['Start']['fromTime'])) echo $merchantOpeningHoursResult[$key]['Start']['fromTime']; ?>" readonly>
								</div>
								<div class="col-sm-4  col-xs-6 no-padding select_sm">
									<input type="text" rowid="<?php echo $key; ?>" class="form-control timepicker" style="width:50%" id="to1_<?php echo $key; ?>" name="to1_<?php echo $key; ?>" value="<?php if(isset($merchantOpeningHoursResult[$key]['End']['toTime'])) echo $merchantOpeningHoursResult[$key]['End']['toTime']; ?>" readonly>
								</div>
								<input type="hidden" style="width:90px;" id="id_<?php echo $key; ?>" name="id_<?php echo $key; ?>" value="<?php if(isset($merchantOpeningHoursResult[$key]['id'])) echo $merchantOpeningHoursResult[$key]['id']; ?>" >
							</div>
							<div class="form-group col-xs-12 no-padding">
								<input type="hidden" id="row_<?php echo $key; ?>" name="row_<?php echo $key; ?>" value="<?php if(!empty($merchantOpeningHoursResult[$key]['Start']['fromTime']) || !empty($merchantOpeningHoursResult[$key]['End']['toTime'])) echo "1"; ?>" />
								<span id="error_<?php echo $key; ?>" style="color: #FF0000;font-size: 13px;"></span>
							</div>
							<?php } } ?>
					</div>	
					<?php } ?>
					
					
					<div class="col-sm-12 col-xs-12 top-boronly" align="center"><!--box-footer merchants  -->
						<?php if(isset($_GET['editId']) && $_GET['editId'] != ''){ ?>
							<input type="submit" class="btn btn-success mR-button" name="submit" id="submit" value="Save" title="Save" alt="Save">
						<?php } else { ?>
							<input type="submit" class="btn btn-success mR-button" name="submit" id="submit" value="Add" title="Add" alt="Add">
						<?php } ?>
						<?php if(isset($_GET['back']) && $_GET['back'] != ''){
							if ($_GET['back'] == '1'){ 
								$href_page = "Merchants?cs=1&status=0";
							}else{
								$href_page = "Merchants";
							}
						?>
								
						
						<?php } ?>
						<?php //$href_page = "Merchants"; 	?>		
							<a href="<?php if(isset($href_page) && $href_page != '' ) echo $href_page; else echo 'Merchants';?>" class="trans-button" name="Back" id="Back" title="Back" alt="Back" >Back </a>	
						
					</div>
				</div><!-- /.box -->
			</form>	
			</div>
		</div><!-- /.row -->
	</section><!-- /.content -->	
						  	
<?php //}
//}
commonFooter(); ?>
</html>
<script type="text/javascript">
$(".fancybox").fancybox();
showCategory('<?php if(isset($cat_id_values) && $cat_id_values>0) echo $cat_id_values; ?>');
function price_val(val){
	$("#priceValidation").val(val);
}
$(document).ready(function() {
	
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


	if($("#min_price").val() > 0)
		price_val($("#min_price").val());
	else
		price_val($("#max_price").val());
});

$( "#Country" ).change(function() {
			value	=	$('#Country').val();
			$("#Currency1").val(value);
			$("#Currency").val(value);
		});
$( "#Country" ).keyup(function() {
			value	=	$('#Country').val();
			$("#Currency1").val(value);
			$("#Currency").val(value);
		});
</script>