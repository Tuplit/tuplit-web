<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
global $admin_days_array;
global $admin_time_array;
commonHead();
require_once('controllers/MerchantController.php');
$MerchantObj   	=   new MerchantController();
require_once('controllers/ProductController.php');
$ProductObj   	=   new ProductController();
require_once("includes/phmagick.php");
$date_now 		= date('Y-m-d H:i:s');
$categories 	= $MerchantObj->getCategories();

if(isset($_POST['submit']) && $_POST['submit'] != ''){
	$_POST          =   unEscapeSpecialCharacters($_POST);
   	$_POST          =   escapeSpecialCharacters($_POST);
	
	$iconName = $iconPath = $imageName = $imagePath = $icimg = $imimg = '';
	$merchantListResult  = $MerchantObj->selectMerchantDetail($_POST['merchant_id']);
	
	//echo "<pre>"; echo print_r($_POST); echo "</pre>";//die();
	
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
	header("location:MerchantList?msg=2");
	
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
?>
<body class="skin-blue" onload="return fieldfocus('FirstName');">
	<?php top_header(); ?>
	<section class="content-header no-padding">
	<!-- Content Header (Page header) -->
		<div class="col-xs-12"> 
			<h1><i class="fa fa-edit"></i> Edit Merchant</h1>
		</div>
	</section>
	
	 <!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-md-12"> 
			<form name="merchant_edit_form" id="merchant_edit_form" action="" method="post" onsubmit="">
			<div class="box box-primary"> 
				<!-- left column -->
					<input type="Hidden" name="merchant_id" id="merchant_id" value="<?php if(isset($_GET['editId']) && $_GET['editId'] != '' ) echo $_GET['editId'];?>">
					<div class="form-group col-sm-6">
						<label>First Name</label>
						<input type="text" class="form-control" id="FirstName" name="FirstName" maxlength="100" value="<?php echo $merchantListResult[0]->FirstName;?>" >
					</div>
					<div class="form-group col-sm-6 ">
						<label>Last Name</label>
						<input type="text" class="form-control" id="LastName" name="LastName" maxlength="20" value="<?php echo $merchantListResult[0]->LastName;?>" >
					</div>					
					<div class="form-group col-sm-6 clear">
						<label>Email</label>
						<input type="text" class="form-control" name="Email" id="Email" maxlength="100" value="<?php echo $merchantListResult[0]->Email;?>" >
					</div>
					<div class="form-group col-sm-6 ">
						<label>Company Name</label>
						<input type="text" class="form-control" id="CompanyName" name="CompanyName" maxlength="30" value="<?php echo $merchantListResult[0]->CompanyName;?>" >
					</div>						
					<div class="form-group col-sm-6 clear">
						<label>Phone Number</label>
						<div class="col-md-6 no-padding"> <input type="text" class="form-control" id="PhoneNumber" name="PhoneNumber" maxlength="15" onkeypress="return isNumberKey_Phone(event);" value="<?php echo $merchantListResult[0]->PhoneNumber;?>" ></div>
					</div>
					<div class="form-group col-sm-6 ">
						<label>Website Url</label>
						<input type="text" class="form-control" id="WebsiteUrl" name="WebsiteUrl" maxlength="100" value="<?php echo $merchantListResult[0]->WebsiteUrl;?>" >
					</div>
					<div class="form-group col-sm-6 clear">
						<label>Location</label>
						<div class="col-md-6 no-padding"><input type="text" class="form-control" id="Location" name="Location" maxlength="30" value="<?php echo $merchantListResult[0]->Location;?>" ></div>
					</div>
					<div class="form-group col-sm-6">
						<label>Items Sold</label>
						<div class="col-md-6 no-padding"><input type="text" class="form-control" id="ItemsSold" name="ItemsSold" maxlength="5" onkeypress="return isNumberKey_numbers(event);" value="<?php echo $merchantListResult[0]->ItemsSold;?>" ></div>
					</div>
					<div class="form-group col-sm-6 clear">
						<label>Address</label>
						<textarea class="form-control" id="Address" name="Address" cols="5"><?php echo $merchantListResult[0]->Address;?></textarea>
					</div>
					<div class="form-group col-sm-6">
						<label>Short Description</label>
						<textarea class="form-control" id="ShortDescription" name="ShortDescription" maxlength="250" cols="5"><?php echo $merchantListResult[0]->ShortDescription;?></textarea>	
					</div>	
					<div class="form-group col-sm-6 clear">
						<label>Description</label>
						<textarea class="form-control" id="Description" name="Description" cols="5"><?php echo $merchantListResult[0]->Description;?></textarea>
					</div>						
					<div class="form-group col-sm-6 ">
						<label>Icon</label>
							<div class="col-sm-8 no-padding"> 
								<input type="file"  name="icon_photo" id="icon_photo" onchange="return ajaxAdminFileUploadProcess('icon_photo');"  /> 
								<p class="help-block no-margin">(Minimum dimension 100x100)</p>
								<span class="error" for="empty_user_photo" generated="true" style="display: none">Icon is required</span>
							</div>
							<div class="col-sm-3 no-padding" >
						      <div id="icon_photo_img">
								 <?php 
								 	$image_path = '';
								 	if(!empty($merchantListResult[0]->Icon)) { 
										$photo = $merchantListResult[0]->Icon;
										if(SERVER){
											if(image_exists(6,$photo))
												$image_path = MERCHANT_ICONS_IMAGE_PATH.$photo;
										}else{
											if(file_exists(MERCHANT_ICONS_IMAGE_PATH_REL.$photo))
												$image_path = MERCHANT_ICONS_IMAGE_PATH.$photo;
										}
									?>
								  <a href="<?php echo $image_path; ?>" class="fancybox" title="<?php echo ucfirst($merchantListResult[0]->CompanyName); ?>"><img class="img_border" src="<?php echo $image_path;?>" width="75" height="75" alt="Image"/></a>
							 	<?php } ?>
							  </div>								
						  </div>	
						  <input type="Hidden" name="old_icon_photo" id="old_icon_photo" value="<?php echo $merchantListResult[0]->Icon;?>" />			
						  <?php  if(isset($_POST['icon_photo_upload']) && $_POST['icon_photo_upload'] != ''){  ?><input type="Hidden" name="icon_photo_upload" id="icon_photo_upload" value="<?php  echo $_POST['icon_photo_upload'];  ?>"><?php  }  ?>
										<input type="Hidden" name="empty_icon_photo" id="empty_icon_photo" value="<?php  if(isset($image_path) && $image_path != '') { echo $image_path; }  ?>" />
										<input type="Hidden" name="name_icon_photo" id="name_icon_photo" value="<?php  if(isset($image_path) && $image_path != '') { echo $image_path; }  ?>" />						
					</div>
					<div class="form-group col-sm-6 clear">
						<label>Image</label>
						<div class="col-sm-7 no-padding"> 
							<input type="file"  name="merchant_photo" id="merchant_photo" onclick="" onchange="return ajaxAdminFileUploadProcess('merchant_photo');"  /> 
							<p class="help-block no-margin">(Minimum dimension 640x260)</p>
							<span class="error" for="empty_com_photo" generated="true" style="display: none">Image is required</span>
						</div>	
						<div class="col-sm-5 no-padding"> 
					      <div id="merchant_photo_img">
						  	<?php 
								$cimage_path ='';
								if(!empty($merchantListResult[0]->Image)) { 
									$cmerchant_image = $merchantListResult[0]->Image;
									if(SERVER){
										if(image_exists(7,$cmerchant_image))
											$cimage_path = MERCHANT_IMAGE_PATH.$cmerchant_image;
									}else{
										if(file_exists(MERCHANT_IMAGE_PATH_REL.$cmerchant_image))
											$cimage_path = MERCHANT_IMAGE_PATH.$cmerchant_image;
									}
								?>
							  	<a href="<?php echo $cimage_path; ?>" class="fancybox" title="<?php echo ucfirst($merchantListResult[0]->CompanyName); ?>">	<img class="img_border" src="<?php echo $cimage_path;?>" width="200" height="100" alt="Image"/></a>
						 	<?php } ?>
						  </div>
					  	</div>	
						<input type="Hidden" name="old_merchant_photo" id="old_merchant_photo" value="<?php echo $merchantListResult[0]->Image;?>" />	
						<?php  if(isset($_POST['merchant_photo_upload']) && $_POST['merchant_photo_upload'] != ''){  ?>
							<input type="Hidden" name="merchant_photo_upload" id="icon_photo_upload" value="<?php  echo $_POST['merchant_photo_upload'];  ?>"><?php  }  ?>
							<input type="Hidden" name="empty_merchant_photo" id="empty_merchant_photo" value="<?php  if(isset($cimage_path) && $cimage_path != '') { echo $cimage_path; }  ?>" />
							<input type="Hidden" name="name_merchant_photo" id="name_merchant_photo" value="<?php  if(isset($cimage_path) && $cimage_path != '') { echo $cimage_path; }  ?>" />						
					</div>	
					<div class="form-group col-sm-6 ">
						<label>Category</label>
						<div class="col-sm-6 col-lg-6 no-padding form-group">
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
					<div class="form-group col-sm-6 clear">
						<div class="col-xs-6 col-sm-6 col-md-5 no-padding">
							<label>Price Scheme</label>
							<div class="form-group col-md-12 col-lg-12 no-padding ">
							<select class="form-control" id="DiscountTier" name="DiscountTier" onclick="selectPrice(this.value);">
								<option value="" >Select
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
							<div class="form-group col-sm-8 col-lg-1 text-center LH30">OR</div>
							<div class="form-group col-sm-6  no-padding">
								<label class="col-md-12 no-padding">Select the product list or menu to be discounted (30% and the whole menu)</label>
								<div class="col-md-12 no-padding"> 
									 <select multiple class="form-control" id="Products_List" name="Products_List[]" onclick="selectProduct(this.value);"><!-- return getPrice(this); -->
										<option value="all" <?php if(in_array('all',$ProductIds)) echo "selected='selected'"; ?>>Select All</option>
										<?php foreach($ProductsArray as $key=>$value){ ?>
												<option value="<?php echo $value->id; ?>" <?php if(in_array($value->id,$ProductIds)) echo "selected='selected'"; else if(in_array('all',$ProductIds)) echo "selected='selected'"; ?>><?php echo ucfirst($value->ItemName); ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
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
									<div class="col-sm-2 col-xs-2 no-padding LH30">$</div>
									<div class="col-sm-10 col-xs-10 no-padding"><input type="Text" name="min_price" maxlength="7" onchange="price_val(this.value);" value="<?php echo $min_val;?>" id="min_price" onkeypress="return isNumberKey(event);" class="form-control"></div>
								</div>
								<div class="col-sm-2 col-xs-2 no-padding LH30" align="center"><strong>to</strong></div>
								<div class="col-sm-5 col-xs-5 no-padding">
									<div class="col-sm-2 col-xs-2 no-padding LH30">$</div>
									<div class="col-sm-10 col-xs-10 no-padding"><input type="Text" name="max_price" maxlength="7" onchange="price_val(this.value);"  value="<?php echo $max_val;?>" id="max_price" onkeypress="return isNumberKey(event);" class="form-control"></div>
								</div>
								<input  type="hidden" id="priceValidation" name="priceValidation" value="">
							</div>
						</div>
						
						
					<div class="form-group col-xs-12  col-md-10   col-lg-8 clear">
						<div class="form-group col-xs-12 no-padding"><label>Open Hours leave as empty for not service, HH:MM AM/PM</label></div>
							<?php 								
								if(isset($admin_days_array) && count($admin_days_array)>0) {
								foreach($admin_days_array as $key=>$val){ ?>
							<div class="col-xs-12 no-padding <?php if($key != 0) echo "rowHide";?>" <?php if(isset($merchantOpeningHoursResult[0]['DateType']) && $merchantOpeningHoursResult[0]['DateType'] == '1' && $key != 0) echo 'style="display:none;"'; ?>>
								<?php if($key == 0) { ?>
									<div class="col-sm-4 col-lg-3 col-xs-12 no-padding">
										<input type="checkbox" name="samehours" id="samehours"  onclick="return hideAllDays();" <?php if(isset($merchantOpeningHoursResult[0]['DateType']) && $merchantOpeningHoursResult[0]['DateType'] == '1') echo "checked"; ?>>&nbsp;Same for all days 
										<input type="hidden" id="showdays" name="showdays" value="<?php if(isset($merchantOpeningHoursResult[0]['id'])) echo $merchantOpeningHoursResult[0]['DateType']; else echo "0"; ?>"/>
									</div>
									<div class="col-sm-4 col-lg-4 col-xs-5 text-center LH30">From :</div>
									<div class="col-sm-4 col-lg-4 col-xs-5 text-center LH30">To :</div>
								<?php } ?>
								<div class="col-sm-4 col-lg-3  col-xs-12 col-md-4 no-padding LH30"><strong><span class="<?php if($key == 0) echo "rowshow";?>"><?php if(isset($merchantOpeningHoursResult[0]['DateType']) && $merchantOpeningHoursResult[0]['DateType'] == '1' && $key == 0) echo "<strong>Monday to Sunday :</strong> "; else echo $val." : "; ?></span></strong></div>
								<div class="col-sm-4 col-xs-6 no-padding select_sm">
									
									
										<select class="form-control" id="fromhours_list<?php echo $key; ?>" name="fromhours_list<?php echo $key; ?>" onchange="return setTime('<?php echo $key; ?>');">
											<option value="">HH</option>
											<?php foreach($admin_hours_array as $keyhr=>$value){  ?>
													<option value="<?php echo $keyhr; ?>" <?php if(isset($merchantOpeningHoursResult[$key]['Start']['hr']) && $merchantOpeningHoursResult[$key]['Start']['hr'] == $keyhr) echo "selected"; ?>><?php echo $value; ?></option>
											<?php } ?>
										</select>
										<select class="form-control" id="fromminute_list<?php echo $key; ?>" name="fromminute_list<?php echo $key; ?>" onchange="return setTime('<?php echo $key; ?>');">
											<option value="">MM</option>
											<?php foreach($admin_minute_array as $keymin=>$value){  ?>
													<option value="<?php echo $keymin; ?>" <?php if(isset($merchantOpeningHoursResult[$key]['Start']['min']) && $merchantOpeningHoursResult[$key]['Start']['min'] == $keymin) echo "selected"; ?>><?php echo $value; ?></option>
											<?php } ?>
										</select>
										<select class="form-control" id="fromampm_list<?php echo $key; ?>" name="fromampm_list<?php echo $key; ?>" onchange="return setTime('<?php echo $key; ?>');">
											<option value="">AM/PM</option>
											<?php foreach($admin_ampm_array as $keyampm=>$value){  ?>
													<option value="<?php echo $keyampm; ?>" <?php if(isset($merchantOpeningHoursResult[$key]['Start']['ampm']) && $merchantOpeningHoursResult[$key]['Start']['ampm'] == $keyampm) echo "selected"; ?>><?php echo $value; ?></option>
											<?php } ?>
										</select>
										<input type="hidden" class="form-control" style="font-size:13px"  id="from1_<?php echo $key; ?>" name="from1_<?php echo $key; ?>" onchange="return setTime('<?php echo $key; ?>');" value="<?php if(isset($merchantOpeningHoursResult[$key]['Start']['fromTime'])) echo $merchantOpeningHoursResult[$key]['Start']['fromTime']; ?>" >
									
								</div>
								<div class="col-sm-4  col-xs-6 no-padding select_sm">
								
										<select class="form-control" id="tohours_list<?php echo $key; ?>" name="tohours_list<?php echo $key; ?>" onchange="return setTime('<?php echo $key; ?>');">
											<option value="">HH</option>
											<?php foreach($admin_hours_array as $keyhr=>$value){  ?>
													<option value="<?php echo $keyhr; ?>" <?php if(isset($merchantOpeningHoursResult[$key]['End']['hr']) && $merchantOpeningHoursResult[$key]['End']['hr'] == $keyhr) echo "selected"; ?>><?php echo $value; ?></option>
											<?php } ?>
										</select>
										<select class="form-control" id="tominute_list<?php echo $key; ?>" name="tominute_list<?php echo $key; ?>" onchange="return setTime('<?php echo $key; ?>');">
											<option value="">MM</option>
											<?php foreach($admin_minute_array as $keymin=>$value){  ?>
													<option value="<?php echo $keymin; ?>" <?php if(isset($merchantOpeningHoursResult[$key]['End']['min']) && $merchantOpeningHoursResult[$key]['End']['min'] == $keymin) echo "selected"; ?>><?php echo $value; ?></option>
											<?php } ?>
										</select>
										<select class="form-control" id="toampm_list<?php echo $key; ?>" name="toampm_list<?php echo $key; ?>" onchange="return setTime('<?php echo $key; ?>');">
											<option value="">AM/PM</option>
											<?php foreach($admin_ampm_array as $keyampm=>$value){  ?>
													<option value="<?php echo $keyampm; ?>" <?php if(isset($merchantOpeningHoursResult[$key]['End']['ampm']) && $merchantOpeningHoursResult[$key]['End']['ampm'] == $keyampm) echo "selected"; ?>><?php echo $value; ?></option>
											<?php } ?>
										</select>
									<div class="col-sm-7 col-xs-7 no-padding"><input type="hidden"  class="form-control"  style="font-size:13px" id="to1_<?php echo $key; ?>" name="to1_<?php echo $key; ?>" onchange="return setTime('<?php echo $key; ?>');" value="<?php if(isset($merchantOpeningHoursResult[$key]['End']['toTime'])) echo $merchantOpeningHoursResult[$key]['End']['toTime']; ?>" ></div>
								</div>
								<input type="hidden" style="width:90px;" id="id_<?php echo $key; ?>" name="id_<?php echo $key; ?>" value="<?php if(isset($merchantOpeningHoursResult[$key]['id'])) echo $merchantOpeningHoursResult[$key]['id']; ?>" >
							</div>
							<div class="form-group col-xs-12 no-padding">
								<input type="hidden" id="row_<?php echo $key; ?>" name="row_<?php echo $key; ?>" value="<?php if(!empty($merchantOpeningHoursResult[$key]['Start']['fromTime']) || !empty($merchantOpeningHoursResult[$key]['End']['toTime'])) echo "1"; ?>" />
										<span id="error_<?php echo $key; ?>" style="color: #FF0000;font-size: 13px;"></span>
							</div>
							<?php } } ?>
					</div>	
					
					</div>
					
					<div class="box-footer col-sm-12 col-xs-12 " align="center">
						<input type="submit" class="btn btn-success" name="submit" id="submit" value="Save" title="Save" alt="Save">&nbsp;&nbsp;&nbsp;&nbsp;
						<?php $href_page = "MerchantList"; 	?>		
							<a href="<?php if(isset($href_page) && $href_page != '' ) echo $href_page; else echo 'MerchantList';?>" class="btn btn-default" name="Back" id="Back" title="Back" alt="Back" >Back </a>	
						
					</div>
				</div><!-- /.box -->
			</form>	
			</div>
		</div><!-- /.row -->
	</section><!-- /.content -->	
						  	
<?php }
}commonFooter(); ?>
</html>
<script type="text/javascript">
$(".fancybox").fancybox();
showCategory('<?php if(isset($cat_id_values) && $cat_id_values>0) echo $cat_id_values; ?>');
function price_val(val){
	$("#priceValidation").val(val);
}
$(document).ready(function() {
	if($("#min_price").val() > 0)
		price_val($("#min_price").val());
	else
		price_val($("#max_price").val());
});
</script>