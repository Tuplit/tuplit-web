<?php 
require_once('includes/CommonIncludes.php');
require_once('includes/mangopay/functions.php');
//require_once('includes/php_image_magician.php');
admin_login_check();
commonHead();
require_once('controllers/UserController.php');
$userObj   			=   new UserController();
require_once('controllers/AdminController.php');
$adminLoginObj   	=   new AdminController();
require_once('controllers/LocationController.php');
$locationObj   			=   new LocationController();
require_once("includes/phmagick.php");
$FirstName = $LastName = $UserName = $Email = $FbId = $GooglePlusId = $UserImage = $ImagePath  = $IpAddress = $class = $class_icon  = $msg = $ExistCondition = $error_msg = $location   = $Gender = $Age = $DOB = '';
$field_focus 		= 	'UserName';
$display      		= 	'none';
$GooglePlusId_exists = $PinCode_exists = $CellNumber_exists = $Email_exists = $FbId_exists = $UserName_exists = 0;
$photoUpdateString	= 	'';
unset($_SESSION['orderby']);
unset($_SESSION['ordertype']);
$field				=	'Location,Code';
$condition			=	' Status = 1 order by Location asc';
$LocationList		=	$locationObj->getLocationArray($field,$condition);
if(isset($_GET['editId']) && $_GET['editId'] != '' ){
	$condition       		= 	" id = ".$_GET['editId']." and Status in (1,2)";
	$field					=	' FirstName,LastName,Email,FBId,GooglePlusId,Photo,Location,Country,DateCreated,PushNotification,Platform,PinCode,ZipCode,SendCredit,RecieveCredit,BuySomething,CellNumber,DealsOffers,Sounds,Passcode,PaymentPreference,RememberMe,Gender,DOB';
	$userDetailsResult  	= 	$userObj->selectUserDetails($field,$condition);
	if(isset($userDetailsResult) && is_array($userDetailsResult) && count($userDetailsResult) > 0){
		$FirstName 			= 	$userDetailsResult[0]->FirstName;
		$LastName 			= 	$userDetailsResult[0]->LastName;
		$Email      		= 	$userDetailsResult[0]->Email;
		$FbId       		= 	$userDetailsResult[0]->FBId;
		$GooglePlusId  		= 	$userDetailsResult[0]->GooglePlusId;
		$Location	   		= 	$userDetailsResult[0]->Location;
		$Country	   		= 	$userDetailsResult[0]->Country;
		$ZipCode	   		= 	$userDetailsResult[0]->ZipCode;
		$CellNumber	   		= 	$userDetailsResult[0]->CellNumber;
		$PinCode	   		= 	$userDetailsResult[0]->PinCode;
		$PushNotification  	= 	$userDetailsResult[0]->PushNotification;	
		$SendCredit		  	= 	$userDetailsResult[0]->SendCredit;	
		$RecieveCredit  	= 	$userDetailsResult[0]->RecieveCredit;	
		$BuySomething	  	= 	$userDetailsResult[0]->BuySomething;	
		$DealsOffers   		= 	$userDetailsResult[0]->DealsOffers;
		$Sounds   			= 	$userDetailsResult[0]->Sounds;
		$Passcode   		= 	$userDetailsResult[0]->Passcode;
		$PaymentPreference  = 	$userDetailsResult[0]->PaymentPreference;
		$RememberMe   		= 	$userDetailsResult[0]->RememberMe;
		$Gender		   		= 	$userDetailsResult[0]->Gender;
		$DOB		   		= 	$userDetailsResult[0]->DOB;
		if(isset($userDetailsResult[0]->Photo) && $userDetailsResult[0]->Photo != ''){
			$UserImageName 		= 	$userDetailsResult[0]->Photo;
			$OriginalImagePath 	= 	$UserImagePath = '';
			if(SERVER){
				if(image_exists(1,$UserImageName))
					$OriginalImagePath 	= 	USER_IMAGE_PATH.$UserImageName;
				if(image_exists(2,$UserImageName))
					$UserImagePath 		= 	USER_THUMB_IMAGE_PATH.$UserImageName;
			}
			else{
				if(file_exists(USER_IMAGE_PATH_REL.$UserImageName))
					$OriginalImagePath 	= 	USER_IMAGE_PATH.$UserImageName;
				if(file_exists(USER_THUMB_IMAGE_PATH_REL.$UserImageName)){
					$UserImagePath 		= 	USER_THUMB_IMAGE_PATH.$UserImageName;
				}
			}
		}
	}
}
if(isset($_POST['submit']) && $_POST['submit'] != ''){

	$_POST          =   unEscapeSpecialCharacters($_POST);
   	$_POST          =   escapeSpecialCharacters($_POST);
	$IpAddress     	=   ipAddress();
	if(isset($_POST['FirstName']) )
		$FirstName 			= 	$_POST['FirstName'];
	if(isset($_POST['LastName']) )
		$LastName 			= 	$_POST['LastName'];
/*	if(isset($_POST['UserName']))
		$UserName  			= $_POST['UserName'];
*/
	if(isset($_POST['Email']))
		$Email      		= 	$_POST['Email'];
	if(isset($_POST['Location']) )
		$Location     		= 	$_POST['Location'];
	if(isset($_POST['Country']) )
		$Country     		= 	$_POST['Country'];	
	if(isset($_POST['FbId']))
		$FbId       		= 	$_POST['FbId'];
	if(isset($_POST['GooglePlusId']))
		$GooglePlusId   	= 	$_POST['GooglePlusId'];
	if(isset($_POST['PinCode']))
		$PinCode   			= 	$_POST['PinCode'];
	if(isset($_POST['ZipCode']))
		$ZipCode   			= 	$_POST['ZipCode'];
	if(isset($_POST['PushNotification']))
		$PushNotification   = 	$_POST['PushNotification'];
	if(isset($_POST['SendCredit']))
		$SendCredit   		= 	$_POST['SendCredit'];
	if(isset($_POST['RecieveCredit']))
		$RecieveCredit   	= 	$_POST['RecieveCredit'];
	if(isset($_POST['BuySomething']))
		$BuySomething   	= 	$_POST['BuySomething'];
	if(isset($_POST['CellNumber']))
		$CellNumber   		= 	$_POST['CellNumber'];
	if(isset($_POST['DealsOffers']))
		$DealsOffers   		= 	$_POST['DealsOffers'];
	if(isset($_POST['Sounds']))
		$Sounds   			= 	$_POST['Sounds'];
	if(isset($_POST['Passcode']))
		$Passcode	   		= 	$_POST['Passcode'];
	if(isset($_POST['PaymentPreference']))
		$PaymentPreference	= 	$_POST['PaymentPreference'];
	if(isset($_POST['RememberMe']))
		$RememberMe   		= 	$_POST['RememberMe'];
	if(isset($_POST['Gender']))
		$Gender   			= 	$_POST['Gender'];
	if(isset($_POST['DOB'])){
		$DOB			=	changeDate_format($_POST['DOB']);
		$dob_year		=	date('Y',strtotime($_POST['DOB']));
		$cur_year		=	date('Y');
		$Age			=	$cur_year-$dob_year;
		$_POST['Age']	= 	$Age;
		$_POST['DOB']	= 	$DOB;
	}
	if (isset($_POST['user_photo_upload']) && !empty($_POST['user_photo_upload'])) {
		$UserImageName 		= 	$_POST['user_photo_upload'];
		$UserImagePath 		= 	TEMP_USER_IMAGE_PATH.$UserImageName;
	}
/*	if($UserName != '' )
		$ExistCondition .= " (UserName = '".$UserName."' ";
*/
	if($Email != '')
		$ExistCondition 	.= 	"  (Email = '".$Email."' ";
	if($FbId != '')
		$ExistCondition  	.= 	" or FbId = '".$FbId."' ";	
	if($GooglePlusId != '')
		$ExistCondition  	.= 	" or GooglePlusId = '".$GooglePlusId."' ";
	if($PinCode != '')
		$ExistCondition  	.= 	" or PinCode = '".$PinCode."' ";
	if($CellNumber != '')
		$ExistCondition  	.= 	" or CellNumber = '".$CellNumber."' ";
	if($_POST['submit'] == 'Save')
		$id_exists 			= 	") and id != '".$_POST['user_id']."' and Status in (1,2) ";
	else
		$id_exists 			= 	" ) and Status in (1,2) ";
	$field 					= 	" * ";	
	$ExistCondition 		.= 	$id_exists;
	$alreadyExist   		= 	$userObj->selectUserDetails($field,$ExistCondition);	
	if(isset($alreadyExist) && is_array($alreadyExist) && count($alreadyExist) > 0){
		if(($alreadyExist[0]->Email == $Email) && ($Email != ''))
			$Email_exists 	= 	1;
		else if($alreadyExist[0]->FBId == $FbId && ($FbId != ''))
			$FbId_exists 	= 	1;
		else if($alreadyExist[0]->GooglePlusId  == $GooglePlusId && ($GooglePlusId != ''))
			$GooglePlusId_exists 	= 	1;
		/*else if($alreadyExist[0]->PinCode  == $PinCode && ($PinCode != ''))
			$PinCode_exists 		= 	1;*/
		else if($alreadyExist[0]->CellNumber  == $CellNumber && ($CellNumber != ''))
			$CellNumber_exists 		= 	1;
		/*else
			$UserName_exists 		= 	1;*/
	}	
	if($Email_exists != '1' && $FbId_exists != '1' && $GooglePlusId_exists != '1'  && $UserName_exists != '1' && $CellNumber_exists != '1'){
		if($_POST['submit'] == 'Save'){		
			if(isset($_POST['user_id']) && $_POST['user_id'] != ''){
				$latlong = $lat = $lng = '';
				
				if(!empty($Location)){					
					$latlong = getLatLngFromAddress($Location) ;
					if($latlong != ''){
						$latlngArray = explode('###',$latlong);
						if(isset($latlngArray) && is_array($latlngArray) && count($latlngArray) > 0){
							if(isset($latlngArray[0]))
								$lat = $latlngArray[0];
							if(isset($latlngArray[1]))
								$lng = $latlngArray[1];
						}
					}					
				}
				
				//UserName 				= '".$UserName."',
				$fields    = "FirstName            	= '".$FirstName."',
							  LastName            	= '".$LastName."',							  
							  Email 				= '".$Email."',
							  FBId					= '".$FbId."',
							  GooglePlusId			= '".$GooglePlusId."',
							  IpAddress 			= '".$IpAddress."',
							  Location 				= '".$Location."', 
							  Latitude 				= '".$lat."', 
							  Longitude				= '".$lng."', 
							  Country 				= '".$Country."', 
							  ZipCode 				= '".$ZipCode."', 
							  PinCode 				= '".$PinCode."', 
							  CellNumber 			= '".$CellNumber."',
							  PushNotification 		= '".$PushNotification."', 
							  SendCredit 			= '".$SendCredit."', 
							  RecieveCredit 		= '".$RecieveCredit."', 
							  BuySomething 			= '".$BuySomething."', 
							  DealsOffers 			= '".$DealsOffers."', 
							  Sounds 				= '".$Sounds."', 
							  Passcode 				= '".$Passcode."', 
							  PaymentPreference 	= '".$PaymentPreference."', 
							  RememberMe 			= '".$RememberMe."', 
							  Gender	 			= '".$Gender."', 
							  DOB		 			= '".$DOB."',
							  Age		 			= '".$Age."', 
							  DateModified			= '".date('Y-m-d H:i:s')."'";
				$condition = ' id = '.$_POST['user_id'];
				$userObj->updateUserDetails($fields,$condition);			
				$insert_id = $_POST['user_id'];
				if (isset($_POST['user_photo_upload']) && !empty($_POST['user_photo_upload'])) {
					if(isset($_POST['name_user_photo']) && $_POST['name_user_photo'] != ''){
						$ImagePath = $_POST['name_user_photo'];
						if(!SERVER){
							if(file_exists(USER_IMAGE_PATH_REL.$ImagePath))
								unlink(USER_IMAGE_PATH_REL . $ImagePath);
							if(file_exists(USER_THUMB_IMAGE_PATH_REL.$ImagePath))
								unlink(USER_THUMB_IMAGE_PATH_REL . $ImagePath);
						}
					}
				}
			$msg = 2;
			}
		}
		if($_POST['submit'] == 'Add'){
			$_POST['ipaddress']     = $IpAddress;
			if(empty($_POST['Location']))
				$_POST['Location']  = 'US';
			if(empty($_POST['Country']))
				$_POST['Country']  	= 'US';
			$insert_id   		    = $userObj->insertUserDetails($_POST);	

			//Mangopay account
			$MangopayDetails['FirstName']			=	$_POST['FirstName'];
			$MangopayDetails['LastName']			=	$_POST['LastName'];
			$MangopayDetails['Email']				=	$_POST['Email'];
			$MangopayDetails['Address']				=	$_POST['Location'];
			if(isset($DOB) && $DOB != '')
				$MangopayDetails['Birthday']		=	$DOB;
			else
				$MangopayDetails['Birthday']		=	'1991-01-01';
			$MangopayDetails['Nationality']			=	'US';
			$MangopayDetails['CountryOfResidence']	=	'US';
			$MangopayDetails['Occupation']			=	'';
			$MangopayDetails['IncomeRange']			=	'';
			$Mangopay								=	userRegister($MangopayDetails);
			if( isset($Mangopay->Id) && $Mangopay->Id != ''){
				$uniqueId					=	$Mangopay->Id;
				$walletId					=	createWallet($uniqueId,'USD');			
				$upwall		=	" MangoPayResponse='".serialize($Mangopay)."', MangoPayUniqueId='".$uniqueId."', WalletId='".$walletId."' ";
				$upwallcon	=	" id ='".$insert_id."'";
				$userObj->updateUserDetails($upwall,$upwallcon);
			}
			
			//$actualPassword         = $_POST['Password'];
			$password				= sha1($_POST['Password'].ENCRYPTSALT);
			$numeric                = '1234567890';
			$numbers                = substr(str_shuffle($numeric), 0, 3);
			$uniqueId				= 'tuplit'.$numbers.$insert_id;
			$updateString 			= " Password = '" . $password . "',UniqueId = '" . $uniqueId . "'";
			$condition 				= " id = ".$insert_id;
			$userObj->updateUserDetails($updateString,$condition);
			$fields = '*';
			$condition = ' 1';
			$login_result = $adminLoginObj->getAdminDetails($fields,$condition);
			$mailContentArray['name'] 		= ucfirst($_POST['FirstName'].' '. $_POST['LastName']);
			//$mailContentArray['userName'] 	= $_POST['UserName'];
			$mailContentArray['toEmail'] 	= $_POST['Email'];
			$mailContentArray['email'] 		= $_POST['Email'];
			$mailContentArray['password'] 	= $_POST['Password'];
			$mailContentArray['subject'] 	= 'User Registration Mail';
			$mailContentArray['userType']	= 'User';
			$mailContentArray['from'] 		= $login_result[0]->EmailAddress;
			$mailContentArray['fileName']	= 'registration.html';
			sendMail($mailContentArray,'2');
			$msg = '1&cs=1';
		}
		$date_now = date('Y-m-d H:i:s');
		if(isset($insert_id) && $insert_id != '' ){
			if (isset($_POST['user_photo_upload']) && !empty($_POST['user_photo_upload'])) {
				$imageName 				= $insert_id . '_' . strtotime($date_now) . '.png';
			   	$tempImagePath 			= TEMP_USER_IMAGE_PATH_REL . $_POST['user_photo_upload'];
				$imagePath 				= UPLOAD_USER_PATH_REL . $imageName;
				$imageThumbPath     	= UPLOAD_USER_THUMB_PATH_REL.$imageName;
				$oldUserName			= $_POST['name_user_photo'];
				if ( !file_exists(UPLOAD_USER_PATH_REL) ){
			  		mkdir (UPLOAD_USER_PATH_REL, 0777);
				}
				if ( !file_exists(UPLOAD_USER_THUMB_PATH_REL) ){
					mkdir (UPLOAD_USER_THUMB_PATH_REL, 0777);
				}
				copy($tempImagePath,$imagePath);
				$phMagick = new phMagick($imagePath);
				$phMagick->setDestination($imageThumbPath)->resize(200,200);
				//imagethumb_new($ImagePath,$imageThumbPath,'','',100,100);
				if (SERVER){
					if($oldUserName!='') {
						if(image_exists(1,$oldUserName)) {
							deleteImages(1,$oldUserName);
						}
						if(image_exists(2,$oldUserName)) {
							deleteImages(2,$oldUserName);
						}
					}
					uploadImageToS3($imageThumbPath,2,$imageName);					
					uploadImageToS3($imagePath,1,$imageName);
					unlink($imagePath);
					unlink($imageThumbPath);
				}
				$photoUpdateString	.= " Photo = '" . $imageName . "'";
				unlink(TEMP_USER_IMAGE_PATH_REL.$_POST['user_photo_upload']);
			}
			if($photoUpdateString!='')
			{
				$condition 			= "id = ".$insert_id;
				$userObj->updateUserDetails($photoUpdateString,$condition);
			}
		}
		header("location:Customers?msg=".$msg);
	}else{
		if($Email_exists == 1){
			$error_msg   = "Email address already exists";
			$field_focus = 'Email';
		}
		else if ($FbId_exists == 1){
			$error_msg   = "Facebook Id already exists";
			$field_focus = 'FBId';
		}
		else if ($GooglePlusId_exists == 1){
			$error_msg   = "Google Plus Id already exists";
			$field_focus = 'GooglePlusId';
		}
		else if ($CellNumber_exists == 1){
			$error_msg   = "CellNumber already exists";
			$field_focus = 'CellNumber';
		}
		$display = "block";
		$class   = "alert-danger";
		$class_icon          = "fa-warning";
	}
}
?>
<body class="skin-blue" onload="return fieldfocus('<?php echo $field_focus; ?>');">
	<?php top_header(); ?>
	<!-- Content Header (Page header) -->
	<section class="content-header no-padding">
		<div class="col-xs-12"> 
			<h1><i class="fa <?php if(isset($_GET['editId']) && $_GET['editId'] != '' ) echo "fa-edit "; else echo 'fa-plus-circle ';?>"></i> <?php if(isset($_GET['editId']) && $_GET['editId'] != '' ) echo "Edit "; else echo 'Add ';?>Customer</h1>
		</div>
	</section>
	<!-- Main content -->
	<section class="content">
		<div class="row">
			<form name="add_user_form" id="add_user_form" action="" method="post">
			<div class="col-md-12"> 
				<div class="box box-padding box-primary"> 
					<div class="col-md-12 no-padding">
					<?php if(isset($error_msg) && $error_msg != '')  { ?> <div class="alert <?php echo $class;  ?> alert-dismissable col-lg-4  col-sm-5  col-xs-11 text-center"><i class="fa <?php echo $class_icon ;  ?>"></i>  <?php echo $error_msg;  ?></div> <?php } ?>
					<input type="Hidden" name="user_id" id="user_id" value="<?php if(isset($_GET['editId']) && $_GET['editId'] != '' ) echo $_GET['editId'];?>">
					</div>
					
					<div class="form-group col-sm-6">
						<label>First Name</label>
						<input type="text" class="form-control" id="FirstName" name="FirstName" maxlength="100" value="<?php if(isset($FirstName) && $FirstName != '') echo ucfirst($FirstName);  ?>" >
					</div>
					<div class="form-group col-sm-6">
						<label>Last Name</label>
						<input type="text" class="form-control" id="LastName" name="LastName" maxlength="20" value="<?php if(isset($LastName) && $LastName != '' ) echo ucfirst($LastName);  ?>" >
					</div>
					
					<div class="form-group col-sm-6 clear">
						<label>Email</label>
						<input type="text" class="form-control" name="Email" id="Email" maxlength="100" value="<?php if(isset($Email) && $Email != '') echo $Email;  ?>" >
					</div>
					<div class="form-group col-sm-6">
						<label>Cell Number</label>
						<input type="text" class="form-control" id="CellNumber" name="CellNumber" maxlength="15" onkeypress="return isNumberKey_Phone(event);" value="<?php if(isset($CellNumber) && $CellNumber != '' ) echo $CellNumber; ?>" >
					</div>
					
					<?php if(isset($_GET['editId']) && $_GET['editId'] != ''){
							$type = "hidden";
						} else {
							$type = "Password";
						}
					?>	
					
					<?php if(!isset($_GET['editId'])){ ?>
					<div class="form-group col-sm-6 clear">
						<label>Password</label>
						<input type="<?php echo $type; ?>" class="form-control" id="Password" name="Password" maxlength="50" value="<?php if(isset($Password) && $Password != '') echo $Password; ?>" >
					</div>
					<div class="form-group col-sm-6">
						<label>Confirm Password</label>
						<input type="<?php echo $type; ?>" class="form-control" id="C_Password" name="C_Password" maxlength="30" value="<?php if(isset($Password) && $Password != '') echo $Password; ?>" >
					</div>
					<?php } ?>		
					<div class="form-group col-sm-6 clear">
						<label>Gender</label>
						<select class="form-control" id="Gender" name="Gender">
							<option value="">Select</option>
							<?php
							if(isset($GenderArray)){
								foreach($GenderArray as $key=>$val){ ?>
							<option value="<?php echo $key; ?>" <?php if(isset($Gender) && $Gender == $key) echo "selected"; ?>><?php echo ucfirst($val); ?></option>
							<?php } }?>
						</select>
					</div>	
					<div class="form-group col-sm-6">
						<label>Date Of Birth</label>
						<input type="text" id = "DOB" class="form-control datepicker" autocomplete="off"  title="Select Date" name="DOB" value="<?php if(isset($DOB) && $DOB != '0000-00-00') echo date('m/d/Y',strtotime($DOB)); ?>">
					</div>		
					<div class="form-group col-sm-6 clear">
						<label>Google Plus Id</label>
						<input type="text" class="form-control" id="GooglePlusId" name="GooglePlusId" maxlength="30" value="<?php if(isset($GooglePlusId) && $GooglePlusId != '' ) echo $GooglePlusId; ?>" >
					</div>	
					<div class="form-group col-sm-6">
						<label>Facebook Id</label>
						<input type="text" class="form-control" id="FbId" name="FbId" maxlength="20" value="<?php if(isset($FbId) && $FbId != '' ) echo $FbId; ?>" >
					</div>
					
					<div class="form-group col-sm-6 clear">
							<label>Country</label>
							<select class="form-control" id="Country" name="Country">
								<option value="">Select</option>
								<?php
								if(isset($LocationList) && count($LocationList) > 0){
									foreach($LocationList as $key=>$val){ ?>
								<option value="<?php echo $val->Location; ?>" <?php if(isset($Country) && $Country == $val->Location) echo "selected"; ?>><?php echo ucfirst($val->Location).'&nbsp;('.$val->Code.')'; ?></option>
								<?php } }?>
							</select>
					</div>
					<div class="form-group col-sm-6 ">
						<label>Location</label>
						<input type="text" class="form-control" id="Location" name="Location" maxlength="100" value="<?php if(isset($Location) && $Location != '' ) echo $Location; ?>" >
					</div>
					
					<div class="form-group col-sm-6 clear">
						<label>Zip Code</label>
						<input type="text" class="form-control" id="ZipCode" name="ZipCode" maxlength="30" value="<?php if(isset($ZipCode) && $ZipCode != '' ) echo $ZipCode; ?>" >
					</div>
					<div class="form-group col-sm-6">
						<label>PIN Code</label>
						<input type="text" class="form-control" id="PinCode" name="PinCode" maxlength="4" onkeypress="return isNumberKey_numbers(event);" value="<?php if(isset($PinCode) && $PinCode != '' ) echo $PinCode; ?>" >
					</div>	
					
					<?php if(!isset($_GET['editId'])) { ?>
					<div class="form-group col-sm-6 col-lg-6 clear">
						<label>Photo</label>
						<div class="row">
						    <div class="col-sm-8  col-lg-5"> 
								<input type="file"  name="user_photo" id="user_photo" title="User Photo" onclick="" onchange="return ajaxAdminFileUploadProcess('user_photo');"  /> 
								<p class="help-block">(Minimum dimension 100x100)</p>
								<span class="error" for="empty_user_photo" generated="true" style="display: none">User Image is required</span>
							</div>
						    <div class="col-sm-4">
						         <div id="user_photo_img">
									<?php  
									if(isset($UserImagePath) && $UserImagePath != ''){  ?>
						                 <a <?php if(isset($OriginalImagePath) && $OriginalImagePath != '') { ?> href="<?php echo $OriginalImagePath; ?>" class="user_photo_pop_up"<?php } else { ?> href="Javascript:void(0);"<?php } ?> title="Click here" alt="Click here" ><img class="img_border" src="<?php  echo $UserImagePath;  ?>" width="75" height="75" alt="Image"/></a>
									<?php  }  ?>
						         </div>
						    </div>
						</div>
						<?php  if(isset($_POST['user_photo_upload']) && $_POST['user_photo_upload'] != ''){  ?><input type="Hidden" name="user_photo_upload" id="user_photo_upload" value="<?php  echo $_POST['user_photo_upload'];  ?>"><?php  }  ?>
						<input type="Hidden" name="empty_user_photo" id="empty_user_photo" value="<?php  if(isset($UserImageName) && $UserImageName != '') { echo $UserImageName; }  ?>" />
						<input type="Hidden" name="name_user_photo" id="name_user_photo" value="<?php  if(isset($UserImageName) && $UserImageName != '') { echo $UserImageName; }  ?>" />
					</div>	
					<?php } else { ?>
				
					<div class="form-group col-sm-6 col-lg-6 clears">
						<label>Photo</label>
						<div class="row">
							<div class="col-sm-8  col-lg-5"> 
								<input type="file"  name="user_photo" id="user_photo" title="User Photo" onclick="" onchange="return ajaxAdminFileUploadProcess('user_photo');"  /> 
								<p class="help-block">(Minimum dimension 100x100)</p>
								<span class="error" for="empty_user_photo" generated="true" style="display: none">User Image is required</span>
							</div><!-- imageValidation('empty_cat_sel_photo'); -->
						
							<div class="col-sm-4" >
						      <div id="user_photo_img">
								<?php  
								if(isset($UserImagePath) && $UserImagePath != ''){  ?>
						              <a <?php if(isset($OriginalImagePath) && $OriginalImagePath != '') { ?> href="<?php echo $OriginalImagePath; ?>" class="user_photo_pop_up"<?php } else { ?> href="Javascript:void(0);"<?php } ?> title="Click here" alt="Click here" ><img class="img_border" src="<?php  echo $UserImagePath;  ?>" width="75" height="75" alt="Image"/></a>
								<?php  }  ?>
						      </div>
							</div>
							<?php  if(isset($_POST['user_photo_upload']) && $_POST['user_photo_upload'] != ''){  ?><input type="Hidden" name="user_photo_upload" id="user_photo_upload" value="<?php  echo $_POST['user_photo_upload'];  ?>"><?php  }  ?>
							<input type="Hidden" name="empty_user_photo" id="empty_user_photo" value="<?php  if(isset($UserImageName) && $UserImageName != '') { echo $UserImageName; }  ?>" />
							<input type="Hidden" name="name_user_photo" id="name_user_photo" value="<?php  if(isset($UserImageName) && $UserImageName != '') { echo $UserImageName; }  ?>" />
						</div>
					</div>						
					<?php } ?>
					
					<?php if(isset($_GET['editId']) && $_GET['editId'] != ''){ ?>
					
					<div class="col-sm-12"> <h3>Notification Settings</h3></div>
					<div style="clear: both;height: 15px"></div>
					<div class="form-group col-sm-6  col-xs-12">
						<label class="notification col-xs-6 no-padding">Push Notification</label>
						<div class=" col-xs-6 no-padding">
							<label class="col-xs-5 no-padding"><input type="Radio" onclick="" value="1" class="checkPush"   id="onPushNotification"  name="PushNotification" <?php if(isset($PushNotification) && $PushNotification == '1') echo 'checked';?> > &nbsp;&nbsp;On</label>&nbsp;&nbsp;&nbsp;&nbsp;
							<label class="col-xs-5 no-padding"><input type="Radio" onclick="" id="offPushNotification" class="checkPush" value="0" id="PushNotification1" name="PushNotification" <?php if(isset($PushNotification) && $PushNotification == '0') echo 'checked';?> > &nbsp;&nbsp;Off
						</div>
					</div>	
					<div class="form-group col-sm-6 col-xs-12">
						<label class="notification col-xs-6   no-padding">Sounds</label>
						<div class=" col-xs-6 no-padding">
							<label class="col-xs-5 no-padding"><input type="Radio" onclick="return notification();" value="1" id="Sounds"  class="" name="Sounds" <?php if(isset($Sounds) && $Sounds == '1') echo 'checked';?> > &nbsp;&nbsp;On</label>&nbsp;&nbsp;&nbsp;&nbsp;
							<label class="col-xs-5 no-padding"><input type="Radio" onclick="return notification();" value="0" id="Sounds" class="" name="Sounds" <?php if(isset($Sounds) && $Sounds == '0') echo 'checked';?> > &nbsp;&nbsp;Off</label>
						</div>
					</div>
					<div class="form-group col-sm-6 col-xs-12">
						<label class="notification col-xs-6   no-padding">Buy Something</label>
						<div class=" col-xs-6   no-padding">
							<label class="col-xs-5 no-padding"><input type="Radio" onclick="return notification();" value="1" class="pushOn" id="BuySomething"  name="BuySomething" <?php if(isset($BuySomething) && $BuySomething == '1') echo 'checked';?> > &nbsp;&nbsp;On</label>&nbsp;&nbsp;&nbsp;&nbsp;
							<label class="col-xs-5 no-padding"><input type="Radio" onclick="return notification();" value="0" id="BuySomething" name="BuySomething" class="pushOff" <?php if(isset($BuySomething) && $BuySomething == '0') echo 'checked';?> > &nbsp;&nbsp;Off</label>
						</div>
					</div>	
					
					<div class="form-group col-sm-6 col-xs-12">
						<label class="notification col-xs-6   no-padding">Passcode</label>
						<div class=" col-xs-6 no-padding">
							<label class="col-xs-5 no-padding"><input type="Radio" onclick="return notification();" value="1" id="Passcode"  class="" name="Passcode" <?php if(isset($Passcode) && $Passcode == '1') echo 'checked';?> > &nbsp;&nbsp;On</label>&nbsp;&nbsp;&nbsp;&nbsp;
							<label class="col-xs-5 no-padding"><input type="Radio" onclick="return notification();" value="0" id="Passcode" class="" name="Passcode" <?php if(isset($Passcode) && $Passcode == '0') echo 'checked';?> > &nbsp;&nbsp;Off</label>
						</div>
					</div>	
					<div class="form-group col-sm-6 col-xs-12">
						<label class="notification col-xs-6   no-padding">Deals &amp; Offers</label>
						<div class=" col-xs-6 no-padding">
							<label class="col-xs-5 no-padding"><input type="Radio" onclick="return notification();" value="1" id="DealsOffers"  class="pushOn" name="DealsOffers" <?php if(isset($DealsOffers) && $DealsOffers == '1') echo 'checked';?> > &nbsp;&nbsp;On</label>&nbsp;&nbsp;&nbsp;&nbsp;
							<label class="col-xs-5 no-padding"><input type="Radio" onclick="return notification();" value="0" id="DealsOffers" class="pushOff" name="DealsOffers" <?php if(isset($DealsOffers) && $DealsOffers == '0') echo 'checked';?> > &nbsp;&nbsp;Off</label>
						</div>
					</div>	
					<div class="form-group col-sm-6 col-xs-12">
						<label class="notification col-xs-6   no-padding">Remember Me</label>
						<div class=" col-xs-6 no-padding">
							<label class="col-xs-5 no-padding"><input type="Radio" onclick="return notification();" value="1" id="RememberMe" class="" name="RememberMe" <?php if(isset($RememberMe) && $RememberMe == '1') echo 'checked';?> > &nbsp;&nbsp;On</label>&nbsp;&nbsp;&nbsp;&nbsp;
							<label class="col-xs-5 no-padding"><input type="Radio" onclick="return notification();" value="0" id="RememberMe"  class="" name="RememberMe" <?php if(isset($RememberMe) && $RememberMe == '0') echo 'checked';?> > &nbsp;&nbsp;Off</label>
						</div>
					</div>
					
					
					<div class="form-group col-sm-6 col-xs-12">
						<label class="notification col-xs-6   no-padding">Send Credit</label>
						<div class=" col-xs-6   no-padding">
							<label class="col-xs-5 no-padding"><input type="Radio" onclick="return notification();" value="1" class="pushOn"   id="SendCredit"  name="SendCredit" <?php if(isset($SendCredit) && $SendCredit == '1') echo 'checked';?> > &nbsp;&nbsp;On</label>&nbsp;&nbsp;&nbsp;&nbsp;
							<label class="col-xs-5 no-padding"><input type="Radio" onclick="return notification();" value="0" id="SendCredit" class="pushOff" name="SendCredit" <?php if(isset($SendCredit) && $SendCredit == '0') echo 'checked';?> > &nbsp;&nbsp;Off
						</div>
					</div>
					<div class="form-group col-sm-6 col-xs-12">
						<label class="notification col-xs-6   no-padding">Payment Preference</label>
						<div class=" col-xs-6 no-padding">
							<label class="col-xs-5 no-padding"><input type="Radio" onclick="return notification();" value="1" id="PaymentPreference" class="" name="PaymentPreference" <?php if(isset($PaymentPreference) && $PaymentPreference == '1') echo 'checked';?> > &nbsp;&nbsp;On</label>&nbsp;&nbsp;&nbsp;&nbsp;
							<label class="col-xs-5 no-padding"><input type="Radio" onclick="return notification();" value="0" id="PaymentPreference" class="" name="PaymentPreference" <?php if(isset($PaymentPreference) && $PaymentPreference == '0') echo 'checked';?> > &nbsp;&nbsp;Off</label>
						</div>
					</div>
					
					<div class="form-group col-sm-6 col-xs-12">
						<label class="notification col-xs-6   no-padding">Recieve Credit</label>
						<div class=" col-xs-6   no-padding">
							<label class="col-xs-5 no-padding"><input type="Radio" onclick="return notification();" value="1" class="pushOn" id="RecieveCredit"  name="RecieveCredit" <?php if(isset($RecieveCredit) && $RecieveCredit == '1') echo 'checked';?> > &nbsp;&nbsp;On</label>&nbsp;&nbsp;&nbsp;&nbsp;
							<label class="col-xs-5 no-padding"><input type="Radio" onclick="return notification();" value="0" id="RecieveCredit" class="pushOff" name="RecieveCredit" <?php if(isset($RecieveCredit) && $RecieveCredit == '0') echo 'checked';?> > &nbsp;&nbsp;Off</label>
						</div>
					</div>					
					
					<?php } ?>
					
					<div class="box-footer  col-xs-12" align="center">
						<?php if(isset($_GET['editId']) && $_GET['editId'] != ''){ ?>
							<input type="submit" class="btn btn-success mR-button" name="submit" id="submit" value="Save" title="Save" alt="Save">
						<?php } else { ?>
							<input type="submit" class="btn btn-success mR-button" name="submit" id="submit" value="Add" title="Add" alt="Add">
						<?php } ?>
						<?php $href_page = "Customers";//"UserList"; 	?>		
						<a href="<?php if(isset($href_page) && $href_page != '' ) echo $href_page; else echo 'Customers';//'UserList';?>" class="btn btn-default" name="Back" id="Back" title="Back" alt="Back" >Back </a>	
					</div>
					
				</div><!-- /.box -->
			</div><!-- /.col -->
			</form>	
		</div><!-- /.row -->
	</section><!-- /.content -->	
<?php commonFooter(); ?>
<script type="text/javascript">
//$(".user_photo_pop_up").colorbox({title:true});
$(".checkPush").click(function(){
		if($(this).val() == 1)
			$(".pushOn").prop("checked",$(this).prop("checked"));
		else	
			$(".pushOff").prop("checked",$(this).prop("checked"));
		/*
	}else
			$(".checkoff").prop("checked",$(this).prop("checked"));
*/
});
$(".datepicker").datepicker({
	showButtonPanel	:	true,        
    buttonText		:	'<i class="fa fa-calendar"></i>',
    buttonImageOnly	:	true,
    buttonImage		:	path+'webresources/images/calender.png',
    dateFormat		:	'mm/dd/yy',
	changeMonth		:	true,
	changeYear		:	true,
	hideIfNoPrevNext:	true,
	showWeek		:	true,
	yearRange		:	"c-30:c",
	closeText		:   "Close"
   });
   
</script>
</html>