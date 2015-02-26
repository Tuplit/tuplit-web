<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
require_once('controllers/AdminController.php');
$adminLoginObj   =   new AdminController();
$class =  $msg  = '';
$display = 'none';
$error = $msg = $photoUpdateString = '';
$fields		  =	" * ";
$where		  =	" 1 ";
$fees         = '';
$user_details = $adminLoginObj->getAdminDetails($fields,$where);
if(isset($user_details) && is_array($user_details) && count($user_details)>0){
		$user_name 			= 	$user_details[0]->UserName;
		$email				=	$user_details[0]->EmailAddress;
		$limit 				=	$user_details[0]->LocationLimit;
		$fees 				=	$user_details[0]->MangoPayFees;
		$contactEmail 		=	$user_details[0]->ContactEmail;
		$phone 				=	$user_details[0]->Phone;
}
if(isset($_POST['general_settings_submit']) && $_POST['general_settings_submit'] != '' )
{	
	if (isset($_POST['admin_logo_upload']) && !empty($_POST['admin_logo_upload'])) {
		$imageName 				= time().'.png';
	   	$tempImagePath 			= TEMP_USER_IMAGE_PATH_REL . $_POST['admin_logo_upload'];
		$imagePath 				= UPLOAD_ADMIN_LOGO_REL . $imageName;
		$oldAdminLogo			= $_POST['name_admin_logo'];
		if ( !file_exists(UPLOAD_ADMIN_LOGO_REL) ){
	  		mkdir (UPLOAD_ADMIN_LOGO_REL, 0777);
		}
		copy($tempImagePath,$imagePath);
		if($oldAdminLogo!='') {
			if (SERVER){
				if(image_exists(11,$oldAdminLogo)) {
					deleteImages(11,$oldAdminLogo);
				}
			}
			else{
				if(file_exists(UPLOAD_ADMIN_LOGO_REL.$oldAdminLogo))
					unlink(UPLOAD_ADMIN_LOGO_REL . $oldAdminLogo);
			}
		}
		if (SERVER){
			uploadImageToS3($imagePath,11,$imageName);
			unlink($imagePath);
		}
		//echo"<br>===================>".ADMIN_LOGO_PATH.$imageName;
		//die();
		$photoUpdateString	.= " , AdminLogo = '" . $imageName . "'";
		unlink(TEMP_USER_IMAGE_PATH_REL.$_POST['admin_logo_upload']);
		
	}
	$updateString   =   " UserName  = '".$_POST['user_name']."',
						  EmailAddress = '".$_POST['email']."',
						  LocationLimit= '".($_POST['limit']/1000)."',
						  MangoPayFees='".$_POST['fees']."' ,
						  ContactEmail='".$_POST['contactEmail']."' ,
						  Phone='".$_POST['phone']."' 
						  ".$photoUpdateString."";
	$condition      =   " id = 1 ";
	$adminLoginObj->updateAdminDetails($updateString,$condition);
	header('location:CommonSettings?msg=1');
}
if(isset($_GET['msg']) && $_GET['msg'] != '')
	$msg = "General settings updated successfully";
commonHead(); ?>
<body class="skin-blue">
	<?php top_header(); ?>
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<div class="row col-md-12 col-lg-6"> 
			<h1><i class="fa fa-cog"></i> General Settings</h1>
		</div>
	</section>
	<!-- Main content -->
	<section class="content">
		<div class="row">
		<!-- left column -->
			<div class="col-md-12"><!--  col-lg-6 -->
				<div class="box box-primary">
				
					<!-- GENERAL SETTINGS-->
					<h2>General Settings</h2>
					<!-- form start -->
					<form name="general_settings_form" id="general_settings_form" action="" method="post">
						<div class="box-body row ">
							<?php if($msg !='') { ?><div class="alert alert-success alert-dismissable col-sm-5 col-xs-10" align="center"><i class="fa fa-check"></i>&nbsp;&nbsp;<?php echo $msg;?></div><?php  } ?>
							<div class="form-group col-sm-5 col-xs-12">
								<label>Username</label>
								<div class="col-sm-8 col-xs-12  no-padding">
									<input type="text" readonly="readonly"  class="form-control" name="user_name" id="user_name" value="<?php  if(isset($user_name) && $user_name) echo $user_name  ?>" />
								</div>
							</div>
							<div class="form-group col-sm-5 col-xs-12">
								<label>Email</label>
								<div class="col-sm-8 col-xs-12  no-padding">
									<input type="text" class="form-control" name="email" id="email" value="<?php  if(isset($email) && $email) echo $email;  ?>"  >
								</div>
							</div>
							<div class="form-group col-sm-5 col-xs-12">
								<label>Location Limit</label>
								<div class="col-sm-8 col-xs-5  no-padding">
									<input type="text" class="form-control" name="limit" id="limit" maxlength="6" onkeypress="return isNumberKey_numbers(event);" value="<?php if(isset($limit)) echo $limit; ?>"  >
								</div>
								<span class="help-block LH30">&nbsp;&nbsp;(In kilometer)</span>
							</div>
							<div class="form-group col-sm-5 col-xs-12">
								<label>MangoPay Fees </label>
								<div class="col-sm-8 col-xs-12  no-padding">
									<input type="text" class="form-control" name="fees" id="fees" maxlength="3" onkeypress="return isNumberKey_numbers(event);" value="<?php if(isset($fees)) echo $fees; ?>"  >
								</div>
							</div>
							<div class="col-xs-12" align="left">
								<input type="submit" class="btn btn-success" name="general_settings_submit" id="general_settings_submit" value="Submit" title="Submit" >
							</div>
						</div><!-- /.box-body -->
					</form>
					<!-- GENERAL SETTINGS END-->
						
				</div><!-- /.box -->
			</div>
		</div><!-- /.row -->
	</section><!-- /.content -->	
<?php commonFooter(); ?>
</html>