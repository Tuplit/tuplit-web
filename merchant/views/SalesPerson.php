<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();

$firstname	=	$lastname	=	$email	=	$id	=	$sucmsg = 	$Photo	= 	$PhotoContent = '';
if(isset($_POST) && !empty($_POST)){
	if(isset($_POST['salesperson_upload']) && !empty($_POST['salesperson_upload'])) {
		$Photo 					= 	TEMP_IMAGE_PATH.$_POST['salesperson_upload'];
		$PhotoContent			= 	$_POST['salesperson_upload'];		
	}	
	if(isset($_POST['FirstName']))		$firstname	=	$_POST['FirstName'];
	if(isset($_POST['LastName']))		$lastname	=	$_POST['LastName'];
	if(isset($_POST['Email']))			$email		=	$_POST['Email'];
	if(isset($_POST['Password']))		$password	=	$_POST['Password'];
	$data	=	array(
					'Photo' 			=> 	$Photo,
					'FirstName' 		=> 	$firstname,
					'LastName' 			=> 	$lastname,
					'Email' 			=> 	$email,
					'Password' 			=> 	$password,
					'ImageAlreadyExists' 	=> 	$_POST['name_salesperson']
				);
	if(!empty($_POST['salespersonId'])) {
		$data['id']	=	$id	=	$_POST['salespersonId'];
		$url					=	WEB_SERVICE.'v1/merchants/salesperson/'.$id;
		$method					=	'PUT';
		$curlResponse			=	curlRequest($url,$method,json_encode($data),$_SESSION['merchantInfo']['AccessToken']);
	} else {
		$url					=	WEB_SERVICE.'v1/merchants/salesperson/';
		$method					=	'POST';
		$curlResponse			=	curlRequest($url,$method,$data,$_SESSION['merchantInfo']['AccessToken']);
	}	
	if(isset($curlResponse) && is_array($curlResponse) && $curlResponse['meta']['code'] == 201) {
		if(!empty($id))
			$sucmsg = 2;		
		else
			$sucmsg = 1;
	} else if(isset($curlResponse['meta']['errorMessage']) && $curlResponse['meta']['errorMessage'] != '')
		$errorMessage	=	$curlResponse['meta']['errorMessage'];
	else
		$errorMessage 	= 	"Bad Request";
	
}
if(isset($_GET['editId']) && !empty($_GET['editId'])) {
	//getting salespersons list
	$url						=	WEB_SERVICE.'v1/merchants/salesperson/'.$_GET['editId'];
	$curlSalespersonResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlSalespersonResponse) && is_array($curlSalespersonResponse) && $curlSalespersonResponse['meta']['code'] == 201 && is_array($curlSalespersonResponse['salesperson']) ) {
		if(isset($curlSalespersonResponse['salesperson'])){
			$salesperson 	= 	$curlSalespersonResponse['salesperson'];
			$firstname	=	$salesperson['FirstName'];	
			$lastname	=	$salesperson['LastName'];	
			$email		=	$salesperson['Email'];	
			$id			=	$salesperson['id'];	
			if($salesperson['Image'] != '') {
				$Photo			= 	$salesperson['Image'];
				$PhotoContent	= 	$salesperson['Image'];
			}
		}
	} else if(isset($curlSalespersonResponse['meta']['errorMessage']) && $curlSalespersonResponse['meta']['errorMessage'] != '')
		$errorMessage		=	$curlSalespersonResponse['meta']['errorMessage'];
	else
		$errorMessage		= 	"Bad Request";
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
if(isset($sucmsg) && $sucmsg != ''){
	if($sucmsg == 1)
		$successMessage	=	'Salesperson has been created successfully';
	if($sucmsg == 2)
		$successMessage	=	'Salesperson has been updated successfully';
	$msg			=	$successMessage;
	$display		=	"block";
	$class 			= 	"alert-success";
	$class_icon 	= 	"fa-check";
	$successMessage = 	'';
}
//commonHead();
popup_head();
?>

<body class="skin-blue fixed body_height popup_bg mystore_msg" onload="fieldfocus('FirstName');">
		<?php //top_header(); ?>
		<?php if(isset($msg) && $msg != '') { ?>
			<div align="center" class="box-center alert <?php  echo $class;  ?> alert-dismissable col-xs-10 col-sm-10"><i class="fa <?php  echo $class_icon;  ?>"></i>  <?php echo $msg; ?></div>
		<?php } ?>
		<?php if(empty($sucmsg)) { ?> 
		<div class="popup_white">
		<section class="content no-padding">
		<div class="col-lg-8 col-md-11 box-center no-padding">
			<section class="row content-header no-margin popup">
				<h1 class="no-margin space_bottom"><?php if(!empty($id)) echo "Update"; else echo "Create"; ?> Salesperson</h1>
			</section>
			<form action="" name="<?php if(!empty($id)) echo "edit_"; ?>subuser_form" id="<?php if(!empty($id)) echo "edit_"; ?>subuser_form"  method="post">

				<div class="row clear">
					<div class="col-sm-12 ">					
					<div class="box-primary">
						<div class="col-xs-12 col-sm-12">
							<div class="upload_img" id="salesperson_img">
								<span class="upload_info" <?php if(isset($Photo) && !empty($Photo)) echo 'style="display:none"'; ?>>Tap here to upload a file</span>
								<input type="file"  name="salesperson" id="salesperson" />
								<img  id="salesperson_image_upload" width="140" height="140" <?php if(isset($Photo) && !empty($Photo)) echo 'src="'.$Photo.'"'; else echo ' style="display:none"'; ?>>
								<input type="hidden" name="salesperson_upload" id="salesperson_upload" value="<?php  if(isset($_POST['salesperson_upload']) && $_POST['salesperson_upload'] != '') echo $_POST['salesperson_upload']; ?>" />
							</div>
							<div class="salesperson_error_msg">							
								<p class="help-block clear txt_center">Please upload only JPG or PNG files.</p>						
								<span class="error col-xs-10 col-xs-offset-1 no-padding text-center" for="empty_salesperson" generated="true" style="display: none">Product Image is required</span>										
								<input type="Hidden" name="empty_salesperson" id="empty_salesperson" value="<?php  if(isset($Photo) && !empty($Photo)) echo "1";  ?>" />
								<input type="Hidden" name="name_salesperson" id="name_salesperson" value="<?php if(isset($PhotoContent) && $PhotoContent != '') echo $PhotoContent; ?>" />
								<input type="hidden" name="old_salesperson" id="old_salesperson" value="<?php  if(isset($PhotoContent) && $PhotoContent != '') { echo $PhotoContent; }  ?>" />
							</div>
						</div>
						<div class="form-group col-xs-12">
							<label class="col-sm-4">First Name</label>
							<div class="col-sm-7" ><input type="text"  id="FirstName" name="FirstName" value="<?php echo $firstname; ?>" class="form-control"></div>	
						</div>
						<div class="form-group col-xs-12">
							<label class="col-sm-4">Last Name</label>
							<div class=" col-sm-7  " ><input type="text"  id="LastName" name="LastName" value="<?php echo $lastname; ?>" class="form-control"></div>	
						</div>
						<div class="form-group col-xs-12">
							<label class="col-sm-4">Email</label>
							<div class=" col-sm-7  " ><input type="text"  id="Email" name="Email" value="<?php echo $email; ?>" class="form-control"></div>	
						</div>
						<div class="form-group col-xs-12">
							<label class="col-sm-4">Password</label>
							<div class=" col-sm-7  " ><input type="password"  id="Password" name="Password" value="" class="form-control"></div>	
						</div>
						<?php if(empty($id)) { ?>
						<div class="form-group col-xs-12">
							<label class="col-sm-4">Confirm Password</label>
							<div class=" col-sm-7  " ><input type="password"  id="C_Password" name="C_Password" value="" class="form-control"></div>	
						</div>
						<?php } ?>
						<input type="hidden"  id="salespersonId" name="salespersonId" value="<?php echo $id; ?>" class="form-control">
						<div class="footer col-xs-12 no-padding" align="center"> 
								<input type="submit" name="subuser_submit" id="subuser_submit" value="<?php if(empty($id)) echo "Create"; else echo "Save"; ?>" title="<?php if(empty($id)) echo "Create"; else echo "Save"; ?>" class="btn btn-success col-xs-12 col-sm-3 box-center">
						</div>					
				</div>				
				</div>
			</form>
		 </div>
		</section>
		</div>
		<?php } ?>
		<?php //footerLogin(); ?>
	<?php commonFooter(); ?>
</html>
<script type="text/javascript">
	$(function() {
		var data = {name: 'salesperson', type : '1'};
		$('#salesperson').change(data,uploadFiles);
	});
</script>
