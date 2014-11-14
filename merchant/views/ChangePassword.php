<?php
require_once('includes/CommonIncludes.php');
//cookies check
$msg_class 		= "alert alert-danger alert-dismissable col-xs-12";
$class_icon   	= "fa-warning";

if(isset($_SESSION['ErrorMessages']) && $_SESSION['ErrorMessages'] !=''){
	$responseMessage 	= $_SESSION['ErrorMessages'];
	unset($_SESSION['ErrorMessages']);
}
$error = '';
if(isset($_POST['password_change_submit']) && $_POST['password_change_submit'] == 'CHANGE PASSWORD'){
	$data	=	array(					
					'Password' 		=> $_POST['Password'],
					'OldPassword' 	=> $_POST['OldPassword'],
					'MerchantId' 	=> $_SESSION['merchantInfo']['MerchantId'],
				);
	$url			=	WEB_SERVICE.'v1/merchants/resetPassword';
	$method			=	'PUT';
	$curlResponse	=	curlRequest($url,$method,json_encode($data));
	if(isset($curlResponse) && is_array($curlResponse) && $curlResponse['meta']['code'] == 201 ) {
		$responseMessage 	= $curlResponse['notifications'][0];
		$msg_class 			= "alert alert-success alert-col-xs-4";
		$class_icon 		= "fa-check";
		if(isset($_COOKIE['tuplit_merchant_email']) && $_COOKIE['tuplit_merchant_email'] != '' && isset($_COOKIE['tuplit_merchant_password']) && $_COOKIE['tuplit_merchant_password'] != '' && isset($_COOKIE['tuplit_merchant_logout']) && $_COOKIE['tuplit_merchant_logout'] != '') {
			if($_COOKIE['tuplit_merchant_logout'] == 'login') {
				$cook['Email'] = $_COOKIE['tuplit_merchant_email'];
				$cook['Password'] = $_POST['Password'];
				setCookies($cook);
			}
		}
	
	} else if(isset($curlResponse['meta']['errorMessage']) && $curlResponse['meta']['errorMessage'] != '') {
		$responseMessage	=	$curlResponse['meta']['errorMessage'];
	} else {
		$responseMessage 	= 	"Bad Request";
	}
}
commonHead();
?>
<body class="skin-blue fixed popup_bg" onload="fieldfocus('user_name');">
	<div class="popup_white">
		<section class="content-header col-xs-12 text-left">
			<h1 style="margin-bottom:15px;">Change Password</h1>
		</section>
		
			<form action="" name="change_password_form" id="change_password_form"  method="post">
		<div class="form-box" id="login-box">
				<div class="body">
					<?php if(isset($responseMessage) && $responseMessage != '') { ?>
						<div class="<?php echo $msg_class; ?> clear" style="margin-top:7px;"><i class="fa <?php echo $class_icon; ?>"></i>&nbsp;&nbsp;<?php echo $responseMessage;?></div>
					<?php  } ?>

					<div class="col-xs-12 no-padding clear">
					
					<div class="form-group"  style="height:58px;margin-bottom:0px;" >
						<input type="password" name="OldPassword" id="OldPassword" class="form-control" required placeholder="Old Password" value=""/>
						
					</div>
					<div class="form-group"  style="height:58px;margin-bottom:0px;">
						<input type="password" name="Password" id="Password" class="form-control" required placeholder="New Password" value=""/>
					</div> 
					<div class="form-group"  style="height:58px;margin-bottom:0px;">
						<input type="password" name="C_Password" id="C_Password" class="form-control"  required placeholder="Confirm Password" value=""/>
					</div>          
					</div>
				</div>
				<div class="footer" style="padding-bottom:20px;">                                                               
					<input type="submit" name="password_change_submit" id="password_change_submit" title="CHANGE PASSWORD" value="CHANGE PASSWORD" class="form-control btn btn-success btn-lg btn-block ">
				</div>
				
		</div>
			</form>
</div>

	<?php commonFooter(); ?>
</html>