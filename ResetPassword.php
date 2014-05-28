<?php

//require_once('admin/includes/CommonIncludes.php');
require_once('admin/includes/AdminTemplates.php');
require_once('admin/config/config.php');
require_once('admin/includes/CommonFunctions.php');
//merchant_reset_password_check();
if(isset($_GET['Type']) && !empty($_GET['Type']) && isset($_GET['UID']) && !empty($_GET['UID'])) {

}
else if( (isset($_GET['Type']) && !empty($_GET['Type'])) && (isset($_GET['Success']) && $_GET['Success'] =='1') ){
	$success  = 1;
}
else{
	header('location:404.php');
	die();
}  
$msg_class 		= "alert alert-danger alert-dismissable col-xs-12";
$class_icon   	= "fa-warning";
$responseMessage = '';
if(isset($_GET['Type']) && !empty($_GET['Type']) && ($_GET['Type'] == 1 || $_GET['Type'] == 2) && isset($_GET['UID']) && !empty($_GET['UID'])) {
	if($_GET['Type'] == 1){
		$data 	= array();
		$url	=	WEB_SERVICE.'v1/users/checkResetPassword/'.decode($_GET['UID']);
	}
	if($_GET['Type'] == 2){
		$data 	= array();
		$url	=	WEB_SERVICE.'v1/merchants/checkResetPassword/'.decode($_GET['UID']);
	}	
	$method	=	'GET';
	$curlResponse	=	curlRequest($url,$method,$data);
	if(isset($curlResponse) && is_array($curlResponse)  && $curlResponse['meta']['code'] == 201) {
		$requestMessage = '';
	}else if(isset($curlResponse['meta']['errorMessage']) && $curlResponse['meta']['errorMessage'] != '') {
		header('location:404.php?UID='.$_GET['UID'].'&Type='.$_GET['Type']);
		die();
	} else {
		$requestMessage 	= 	"Bad Request";
	}
}
else if(isset($_GET['Success']) && $_GET['Success'] =='1'){
	$success  = 1;
}
else{
	$responseMessage = 'Invalid Link';
}  
	
if(isset($requestMessage) && $requestMessage == ''){
	if($responseMessage == '' && isset($_POST['reset_password_submit']) && $_POST['reset_password_submit'] == 'SUBMIT'){
		if($_GET['Type'] == 1){
			$data	=	array(
					'UserId' => decode($_GET['UID']),
					'Password' => $_POST['Password']
				);
			$url	=	WEB_SERVICE.'v1/users/resetPassword';
		}
		if($_GET['Type'] == 2){
			$data	=	array(
					'MerchantId' => decode($_GET['UID']),
					'Password' => $_POST['Password']
				);
			$url	=	WEB_SERVICE.'v1/merchants/resetPassword';
		}
		$method	=	'POST';
		$curlResponse	=	curlRequest($url,$method,$data);
		if(isset($curlResponse) && is_array($curlResponse)  && $curlResponse['meta']['code'] == 201 ) {
			header('location:ResetPassword.php?Success=1&Type='.$_GET['Type']);
			/*if($_GET['Type'] == 1){
				$msg_class 		= "alert alert-success alert-col-sm-4";
				$class_icon   	= "fa-check";
				$responseMessage = $curlResponse['notifications']['0'].' Use app to proccessed further';
			}*/
			
		}else if(isset($curlResponse['meta']['errorMessage']) && $curlResponse['meta']['errorMessage'] != '') {
			
			header('location:404.php?UID='.$_GET['UID'].'&Type='.$_GET['Type']);
			die();
		} else {
			$responseMessage 	= 	"Bad Request";
		}
	}
}

commonHead();

?>
<body class="skin-blue" onload="fieldfocus('password');">
	 <header class="header">
			<nav class="navbar navbar-static-top no-margin" role="navigation" >
				<a  title="Tuplit" href="login" class="logo" style="white-space:nowrap;width :auto">
                <!-- Add the class icon to your logo image or logo icon to add the margining -->
                Tuplit
            </a>
			</nav>
	  </header>
	 <?php if(isset($requestMessage) && $requestMessage != '') { ?>
		<div class="<?php echo $msg_class; ?>" style="margin-top:7px"><i class="fa <?php echo $class_icon; ?>"></i>&nbsp;&nbsp;
			<?php echo $requestMessage;?>
		</div>
	<?php  } ?>
	<?php if(isset($success) && $success == 1) { ?>
	<form action="" id="admin_login_form">
		<table align="center" cellpadding="0" cellspacing="0" border="0" height="100%" width="100%" >
			<tr>
				<td>
				<div class="login">
					<table align="center" cellpadding="0" cellspacing="0" border="0" width="100%">
						<tr>
							<td colspan="3" align="center" class="login_logo" style="border : 0"></td>
						</tr>
						<tr><td colspan="3" height="10"></td></tr>
						<tr>
							<td>
								
								<section class="content">                 
									<div class="error-page success" style="padding: 0 10px;">
										<h1 style="color:#5CAC00;text-align : center;" class="headline text-info"><i class="fa fa-check" style="text-shadow:3px 2px  0 rgba(0, 0, 0, 0.15)"></i></h1>
										<div class="error-content">
											<h3 style="font-size:20px">Hey! Password Updated successfully.</h3>
											<?php if(isset($_GET['Type']) && $_GET['Type'] == 1) { ?>
											<p>You can login your App and proceed further.</p>     
											<?php } else { ?>
												<div class="" align="center"><a href="<?php echo SITE_PATH; ?>/merchant/login"><i class="fa fa-reply"></i>&nbsp;&nbsp;back to login</a></div>  
											<?php }?>             
										</div>
									</div>
								</section>
								
								
							</td>
						</tr>
						<tr><td height="30"></td></tr>
					</table>
				</div>
			</td>
		</tr>
	</table>
	</form>
	<?php } else { ?>
	
	<div class="form-box" id="login-box">
		<div class="header pad">Reset Password</div>
		<form action="" name="forget_password_form1" id="forget_password_form1"  method="post">
			<div class="body">
			<?php if(isset($responseMessage) && $responseMessage != '') { ?>
				<div class="<?php echo $msg_class; ?>" style="margin-top:7px"><i class="fa <?php echo $class_icon; ?>"></i>&nbsp;&nbsp;
					<?php echo $responseMessage;?>
				</div>
			<?php  } ?>
				
				<div class="form-group msg_hgt no-margin">
					<label>Password</label>
					<input class="form-control" type="password" name="Password"  id="Password">
				</div>
				<div class="form-group msg_hgt no-margin">
					<label>Confirm Password</label>
					<input class="form-control" type="password" name="C_Password"  id="C_Password">
				</div>
			</div>
			<div class="footer">                                                               
				<input type="submit" name="reset_password_submit" id="reset_password_submit" value="SUBMIT" class="btn btn-success btn-lg btn-block ">
			</div>
		</form>
	</div>
	</div>
	</div>
	
	<footer>&copy; <?php echo date('Y');?> Tuplit Inc. </footer>
	<?php } ?>
	<?php commonFooter(); ?>
	<script src="<?php echo ADMIN_SCRIPT_PATH; ?>jquery-latest.js" type="text/javascript"></script>
	<script src="<?php echo ADMIN_SCRIPT_PATH; ?>Util.js" type="text/javascript"></script>
	<script src="<?php echo ADMIN_SCRIPT_PATH; ?>jquery.validate.js" type="text/javascript"></script>
	<script src="<?php echo ADMIN_SCRIPT_PATH; ?>Validate.js" type="text/javascript"></script>
	
</html>