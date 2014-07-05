<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();
global $discountTierArray;
global $days_array;
$error = '';
$merchantInfo = $errorMessage = '';
$min_val	=	$max_val	= $prizeRange	= $maximumPrice = $minimumPrice = $imagePath = $iconPath	='';
$prize_type	= 0;
$merchantCategory = array();
$date_now = date('Y-m-d H:i:s');

$merchantId					= 	$_SESSION['merchantInfo']['MerchantId'];
$url						=	WEB_SERVICE.'v1/merchants/'.$merchantId."?From=0";
$curlMerchantResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201 && $curlMerchantResponse['merchant']['MerchantId'] != '' ) 
 {
	$merchantInfo  			= 	$_SESSION['merchantDetailsInfo']   =	$curlMerchantResponse['merchant'];
	$newCategory			=	$merchantInfo['Category'];
}

//echo'<pre>';print_r($merchantInfo);echo'</pre>';
//echo"<br>===================>".$newCategory;
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
	
} else if(isset($curlCategoryResponse['meta']['errorMessage']) && $curlCategoryResponse['meta']['errorMessage'] != '') {
		$errorMessage	=	$curlCategoryResponse['meta']['errorMessage'];
} else {
		$errorMessage	= 	"Bad Request";
}

//echo "<pre>"; echo print_r($merchantInfo); echo "</pre>";
$merchantId		= 	$_SESSION['merchantInfo']['MerchantId'];
$url			=	WEB_SERVICE.'v1/products/';
$curlMerchantResponse  = 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201) {
	$ProductsArray   =	$curlMerchantResponse['ProductList'];		
}
//echo "<pre>"; echo print_r($ProductsArray); echo "</pre>";
if(isset($_POST['merchant_account_submit']) && $_POST['merchant_account_submit'] == 'SAVE'){
	if(isset($_POST['CompanyName']))
		$merchantInfo['CompanyName']		=	$_POST['CompanyName'];
	$merchantInfo['Email']					=	$_POST['Email'];
	if(isset($_POST['Address']))
		$merchantInfo['Address']			=	$_POST['Address'];
	if(isset($_POST['PhoneNumber']))
		$merchantInfo['PhoneNumber']		=	$_POST['PhoneNumber'];
	if(isset($_POST['Website']))
		$merchantInfo['WebsiteUrl']			=	$_POST['Website'];
	if(isset($_POST['Description']))
		$merchantInfo['Description']		=	$_POST['Description'];
	if(isset($_POST['ShortDescription']))
		$merchantInfo['ShortDescription']	=	$_POST['ShortDescription'];
	if(isset($_POST['categorySelected']))
		$newCategory						=	$_POST['categorySelected'];
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
	/*for($t=0;$t<=6;$t++) {
		$openTiming[$t]['id'] 	= $_POST['id_'.$t];
		$openTiming[$t]['Start'] = $_POST['from1_'.$t];
		$openTiming[$t]['End'] 	= $_POST['to1_'.$t];
		if(isset($_POST['samehours']) && $_POST['samehours'] == 'on') 
			$openTiming[$t]['DateType'] = '1';
		else
			$openTiming[$t]['DateType'] = '0';
	*/
	$merchantInfo['OpeningHours']	=	$openTiming;	
		
	/* product price scheme */
	$product_list = '';
	if(isset($_POST['Products_List']) && is_array($_POST['Products_List']) ){
		
		if(in_array('all',$_POST['Products_List'])){
			$product_list = 'all';
		}else{
			if(isset($ProductsArray) && is_array($ProductsArray) && count($ProductsArray) > 0){
				foreach($ProductsArray as $key=>$value){
					foreach($value as $s_key=>$s_value){
						$proArray[$s_key] = $s_value['ProductId'];
					}
				}
				$productExists = array_diff($proArray,$_POST['Products_List']);
				if(is_array($productExists) && count($productExists) > 0 ){
					$product_list = implode(',',$_POST['Products_List']);
				}
				else
					$product_list = 'all';
			}
		}
	}
	$merchantInfo['DiscountProductId']  =	$product_list;
	if($product_list != '')
		$prize_type	=	1;
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
	//echo "<pre>".print_r($_POST)."</pre>"; die();
	$data	=	array(
					'CompanyName' 		=> $_POST['CompanyName'],
					'Email' 			=> $_POST['Email'],
					'Address' 			=> $_POST['Address'],
					'PhoneNumber' 		=> $_POST['PhoneNumber'],
					'WebsiteUrl' 		=> $_POST['Website'],
					'ShortDescription' 	=> $_POST['ShortDescription'],
					'Description' 		=> $_POST['Description'],
					'OpeningHours' 		=> $openTiming,
					'IconPhoto' 		=> $iconPath,
					'MerchantPhoto' 	=> $imagePath,
					'IconExist'			=> $_POST['old_icon_photo'],
					'MerchantExist'		=> $_POST['old_merchant_photo'],
					'DiscountTier' 		=> $_POST['DiscountTier'],
					'DiscountProductId'	=> $product_list,
					'DiscountType'		=> $prize_type,
					'PriceRange' 		=> $prizeRange,
					'Categories' 		=> $_POST['categorySelected']
				);
	$url	=	WEB_SERVICE.'v1/merchants/';
	$method	=	'PUT';
	//echo json_encode($data);die();
	$curlResponse	=	curlRequest($url,$method,json_encode($data), $_SESSION['merchantInfo']['AccessToken']);
	//echo "<pre>"; print_r( $curlResponse); echo "</pre>";
	//die();
	if(isset($curlResponse) && is_array($curlResponse) && $curlResponse['meta']['code'] == 201) {
		//unset($_SESSION['merchantDetailsInfo']);
		//echo'<pre>';print_r($_SESSION);echo'</pre>';
		$merchantId					= 	$_SESSION['merchantInfo']['MerchantId'];
		$url						=	WEB_SERVICE.'v1/merchants/'.$merchantId;
		$curlMerchantResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
		//echo'<pre>';print_r($curlMerchantResponse);echo'</pre>';
		//die();
		if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201 && $curlMerchantResponse['merchant']['MerchantId'] != '' ) 
		 {
			$merchantInfo  						= 	$curlMerchantResponse['merchant'];
			$_SESSION['merchantDetailsInfo']	=	$merchantInfo;
			$newCategory						=	$merchantInfo['Category'];
		}
	
		$successMessage	=	$curlResponse['notifications'][0];
		//header("location:Myaccount");
		//die();
	} else if(isset($curlResponse['meta']['errorMessage']) && $curlResponse['meta']['errorMessage'] != '') {
		$errorMessage		=	$curlResponse['meta']['errorMessage'];
	} else {
		$errorMessage		= 	"Bad Request";
	}
}
//echo'<pre>';print_r($merchantInfo);echo'</pre>';
//echo'<pre>';print_r($_SESSION['merchantDetailsInfo']);echo'</pre>';
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

$merchantInfo['OpeningHours']	=	formOpeningHours($merchantInfo['OpeningHours']);
commonHead();
?>

<body class="skin-blue fixed" onload="fieldfocus('CompanyName');">
		<?php top_header(); ?>
		<section class="content">
		<div class="col-lg-12">
		
			<section class="content-header">
                <h1>My Account</h1>
            </section>
			<?php if(isset($msg) && $msg != '') { ?>
					               <div align="center" class="alert <?php  echo $class;  ?> alert-dismissable col-xs-12 col-sm-5 col-lg-3"><i class="fa <?php  echo $class_icon;  ?>"></i>  <?php echo $msg; ?></div>
							<?php } ?>
			<form action="" name="add_account_form" id="add_account_form"  method="post">
				<div class="row clear">
				<div class="col-md-6">
					<div class="box box-primary no-padding">
						<div class="box-header no-padding">
							<h3 class="box-title">Business Info</h3>
						</div>
						<div class="form-group  col-sm-6 col-md-12">
							<label>Company Name</label>
							<input class="form-control" type="text" name="CompanyName"  id="CompanyName" value="<?php if(isset($merchantInfo['CompanyName']) && !empty($merchantInfo['CompanyName'])) echo $merchantInfo['CompanyName'];?>">
						</div>
						<div class="form-group col-sm-6 col-md-12">
							<label>Email</label>
							<input class="form-control" type="text" name="Email"  id="Email" value="<?php if(isset($merchantInfo['Email']) && !empty($merchantInfo['Email'])) echo $merchantInfo['Email'];?>">
						</div>
						<div class="form-group col-sm-6  col-md-12">
							<label>Password (<a href="ChangePassword" class="changePass" >Change Password</a>)</label>
							<input class="form-control" type="text" readonly id="Pass" value="**********">
						</div>
						<div class="form-group col-sm-6  col-md-12">
							<label>Address</label>
							<textarea class="form-control" id="Address" name="Address" cols="5"><?php if(isset($merchantInfo['Address']) && !empty($merchantInfo['Address'])) echo $merchantInfo['Address'];?></textarea>
						</div>
						<div class="form-group col-sm-6  col-md-12">
							<label>Phone Number</label>
							<input class="form-control" type="text" name="PhoneNumber"  id="PhoneNumber" onkeypress="return isNumberKey_Phone(event);" maxlength="15" value="<?php if(isset($merchantInfo['PhoneNumber']) && !empty($merchantInfo['PhoneNumber'])) echo $merchantInfo['PhoneNumber'];?>">
						</div>						    
						<div class="form-group col-sm-6  col-md-12">
							<label>Website</label>
							<input type="url" name="Website" id="Website" class="form-control" value="<?php if(isset($merchantInfo['WebsiteUrl']) && !empty($merchantInfo['WebsiteUrl'])) echo $merchantInfo['WebsiteUrl'];?>"/>
						</div>						
						<div class="form-group col-sm-6  col-md-12">
							<label>Short Description</label>
							<input type="text" name="ShortDescription" id="ShortDescription" class="form-control" value="<?php if(isset($merchantInfo['ShortDescription']) && !empty($merchantInfo['ShortDescription'])) echo $merchantInfo['ShortDescription'];?>"/>
						</div>
						<div class="form-group col-sm-6  col-md-12">
							<label>Description</label>
							<textarea class="form-control" id="Description" name="Description" cols="5"><?php if(isset($merchantInfo['Description']) && !empty($merchantInfo['Description'])) echo $merchantInfo['Description'];?></textarea>
						</div>
						<div class="form-group col-sm-12 col-md-12">
							<div class="form-group col-md-12 no-padding"><label>Open Hours leave as HH MM AM/PM for not service</label></div>
							<?php 
							if(isset($days_array) && count($days_array)>0) {
							foreach($days_array as $key=>$val){ ?>
							<div class="col-xs-12 no-padding form-group <?php if($key != 0) echo "rowHide";?>"  <?php if(isset($merchantInfo['OpeningHours'][0]['DateType']) && $merchantInfo['OpeningHours'][0]['DateType'] == '1' && $key != 0) echo 'style="display:none;"'; ?>>
								<?php if($key == 0) { ?>
									<div class="col-sm-4 col-lg-3 col-xs-12 no-padding">
										<input type="checkbox" name="samehours" id="samehours"  onclick="return hideAllDays();" <?php if(isset($merchantInfo['OpeningHours'][0]['DateType']) && $merchantInfo['OpeningHours'][0]['DateType'] == '1') echo "checked"; ?>>&nbsp;Same for all days 
										<input type="hidden" id="showdays" name="showdays" value="<?php if(isset($merchantInfo['OpeningHours'][0]['DateType']) && $merchantInfo['OpeningHours'][0]['DateType'] == '1') echo 'checked'; ?>"/>
									</div>
									<div class="col-sm-4 col-xs-6 no-padding LH30">From :</div>
									<div class="col-sm-4 col-xs-6 no-padding LH30">To :</div>
									
								<?php } ?>
								<div class="col-sm-4  col-lg-3 col-xs-12  no-padding LH30"><strong><span class="<?php if($key == 0) echo "rowshow";?>"><?php if(isset($merchantInfo['OpeningHours'][0]['DateType']) && $merchantInfo['OpeningHours'][0]['DateType'] == '1' && $key == 0) echo "Monday to Sunday : "; else echo $val." : "; ?></span></strong></div>
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
				<div class="col-md-6 ">
					<div class="box box-primary no-padding">
						<div class="box-header ">
							<h3 class="box-title">Category</h3>
						</div>
						<div class="form-group col-xs-12">
						<select name="Category" id="Category" class="form-control col-xs-6" onchange="showCategory(this.value)">
							<option value="">Select</option>	
							<?php if(isset($categories) && !empty($categories)) {
								foreach($categories as $key=>$val) {
								if($key != 'totalCount') {
							?>
							<option value="<?php echo $val['CategoryId'];?>"  style="background-image:url(<?php echo $val['CategoryIcon']; ?>);"><?php echo ucfirst($val['CategoryName']);?></option>
							<?php } } } ?>
						</select><span id="njkj"></span>				
						</div>
						<div class="form-group cats col-xs-12">
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
						<div class="col-md-6 col-sm-6  no-padding">
						
						<div class="box-header ">
							<h3 class="box-title">Merchant Icon</h3>
						</div>
						<div  class="box-body no-padding">
						<div class="form-group col-md-12">
							<div class="col-md-12 no-padding"> 
								<input type="file"  name="icon_photo" id="icon_photo" onchange="return ajaxAdminFileUploadProcess('icon_photo');"  /> 
								<p class="help-block">(dimension 100x100)</p>
								<span class="error" for="empty_icon_photo" generated="true" style="display: none">Icon is required</span>
							</div>
							<div class="col-xs-4 no-padding text-center" >
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
						
						<div class="col-md-6  col-sm-6 no-padding">
						<div class="box-header clear ">
							<h3 class="box-title">Merchant Image</h3>
						</div>
						<div  class="box-body no-padding">
						<div class="form-group col-xs-12">
							<div class="col-xs-12 no-padding"> 
								<input type="file"  name="merchant_photo" id="merchant_photo" onclick="" onchange="return ajaxAdminFileUploadProcess('merchant_photo');"   /> 
								<p class="help-block">(dimension 640x260)</p>
								<span class="error" for="empty_merchant_photo" generated="true" style="display: none">Image is required</span>
							</div>	
							<div class="col-xs-10 no-padding text-center"> 
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
				<div class="col-md-3 col-sm-6">
					<div class="box box-primary no-padding">
						<div class="box-header ">
							<h3 class="box-title">Price Range</h3>
						</div>
						<div class="form-group col-md-12 error_msg_align">							
								<div class="col-xs-5 no-padding">
									<div class="col-xs-2 no-padding LH30">$</div>
									<div class="col-xs-9 no-padding"><input type="Text" onchange="price_val(this.value);" maxlength="7" name="min_price" value="<?php echo $min_val;?>" id="min_price" onkeypress="return isNumberKey_price(event);" class="form-control"></div>
								</div>
								<div class="col-xs-1 no-padding LH30" align="center"><strong>to</strong></div>
								<div class="col-xs-5 no-padding">
									<div class="col-xs-2 no-padding LH30">$</div>
									<div class="col-xs-9 no-padding"><input type="Text" onchange="price_val(this.value);" maxlength="7" name="max_price" value="<?php echo $max_val;?>" id="max_price" onkeypress="return isNumberKey_price(event);" class="form-control"></div>
								</div>
								<input  type="hidden" id="priceValidation" name="priceValidation" value="">
						</div>
					</div>
				</div>	
				
				<div class="col-md-3 col-sm-6">
					<div class="box box-primary no-padding">
						<div class="box-header ">
							<h3 class="box-title">Payment Account</h3>
						</div>
						<?php if(isset($merchantInfo['MangoPayUniqueId']) && $merchantInfo['MangoPayUniqueId']!= ''){?>
						<div class="form-group col-md-12 error_msg_align ">
							<h4 class="box-title text-teal"><strong>Connected with Mango Pay</strong></h4>
						</div>
						<?php } else {?>
						<div class="form-group col-md-12 error_msg_align ">
							<label class="pad5"></label><a href="MangoPayAccount" class="MangoPay">
							<button type="button" name="MangoPay" id="MangoPay" value="" class="btn bg-olive btn-md ">
								<i class="fa fa-plus"></i> Add Mango Pay Account
							</button></a> 
						</div>
						<?php } ?>
						<?php if($_SERVER['REMOTE_ADDR']=='172.21.4.215') {?>
						<div class="form-group col-md-12 error_msg_align ">
							<label class="pad5"></label><a href="MangoPayAccount" class="MangoPay">
							<button type="button" name="MangoPay" id="MangoPay" value="" class="btn bg-olive btn-md ">
								<i class="fa fa-plus"></i> Add Mango Pay Account
							</button></a> 
						</div>
						
						<?php }?>
					</div>
				</div>
				<div class="col-md-6 col-sm-12">
					<div class="box box-primary no-padding">
						<div class="box-header ">
							<h3 class="box-title">Price Scheme</h3>
						</div>
						<div class="form-group col-md-12 ">
							<label class="col-xs-7 no-padding">Select Price Scheme</label>
							<div class="col-xs-5 no-padding""> 
							<select class="form-control" id="DiscountTier" name="DiscountTier" onclick="selectPrice(this.value);">
								<option value="" >Select
								<?php if(isset($discountTierArray) && is_array($discountTierArray) && count($discountTierArray) > 0) {
										foreach($discountTierArray as $key=>$value){
								 ?>
								<option value="<?php echo $key; ?>" <?php if(isset($merchantInfo['DiscountTier']) &&  $merchantInfo['DiscountTier'] == $value.'%' ) echo 'selected';?>><?php echo $value.'%'; ?>
								<?php } } ?>
							</select>
							</div>
						</div>
						<?php if(isset($ProductsArray) && is_array($ProductsArray) && count($ProductsArray) > 0) {?>
							<div class="form-group col-md-12 text-center">OR</div>
							<div class="form-group col-xs-12 ">
								<label class="col-md-7 no-padding">Select the product list or menu to be discounted (30% and the whole menu)</label>
								<div class="col-md-5 no-padding"> 
									 <select multiple class="form-control" id="Products_List" name="Products_List[]" onclick="selectProduct(this.value);"><!-- return getPrice(this); -->
										<option value="all">Select All</option>
										<?php
											   if(isset($merchantInfo['DiscountProductId']) && $merchantInfo['DiscountProductId'] != ''){
											   	 	$productListArray = explode(',',$merchantInfo['DiscountProductId']);
												}
														foreach($ProductsArray as $key=>$value){
															foreach($value as $s_key=>$s_value){
															
										 ?>										
										<option value="<?php echo $s_value['ProductId']; ?>" <?php if(isset($productListArray) &&  in_array($s_value['ProductId'],$productListArray)) { echo 'selected'; } else if($merchantInfo['DiscountProductId'] == 'all') echo 'selected';?> ><?php echo $s_value['ItemName']; ?></option>
										<?php }  } ?>
									</select>
								</div>
							</div>
						<?php } ?>
					</div>
				</div>
				
				
		</div>
				<div class="footer col-sm-12 text-center no-padding" align="center"> 
						<input type="submit" name="merchant_account_submit" id="merchant_account_submit" value="SAVE" class="btn btn-success col-xs-12 col-sm-6  col-lg-3 box-center "><br><br>
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
			width: '380',
			maxWidth: '100%',
			scrolling: 'auto',			
			type: 'iframe',
			fitToView: true,
			autoSize: true
	});
	$(".MangoPay").fancybox({
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