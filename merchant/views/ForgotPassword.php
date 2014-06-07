<?php
ob_start();
require_once('includes/CommonIncludes.php');
if(isset($_SESSION['tuplit_merchant_user_name'])){
	//header('location:UserList?cs=1');
	//die();
}
$responseErrorMessage = $responseSuccessMessage = '';

if(isset($_POST['merchant_forgot_submit']) && $_POST['merchant_forgot_submit'] == 'SUBMIT'){
	if(isset($_POST['Email']) && $_POST['Email']){
		$data	=	array();
		$url	=	WEB_SERVICE.'v1/merchants/forgetPassword?Email='.$_POST['Email'];
		$method	=	'GET';
		$curlResponse	=	curlRequest($url,$method,$data);		
		if(isset($curlResponse) && is_array($curlResponse)  && $curlResponse['meta']['code'] == 201 ) {
			$responseSuccessMessage = $curlResponse['merchant']['message'];
			//$success  = 1;
		}else if(isset($curlResponse['meta']['errorMessage']) && $curlResponse['meta']['errorMessage'] != '') {
			$responseErrorMessage	=	$curlResponse['meta']['errorMessage'];			
		} else {
			$responseErrorMessage 	= 	"Bad Request";
		}
	}
}
commonHead();
?>
<body class="skin-blue fixed" onload="fieldfocus('Email');">
	<?php top_header(); ?>
	<div class="form-box" id="login-box">
		<form action="" name="forget_password_form" id="forget_password_form"  method="post">
			<div class="body">
				<?php if($responseErrorMessage !='') { ?><div class="alert alert-danger alert-dismissable col-xs-12" style="margin-top:7px"><i class="fa fa-warning"></i>&nbsp;&nbsp;<?php echo $responseErrorMessage;?></div><?php  } ?>
				<?php if($responseSuccessMessage !='') { ?><div class="alert alert-success alert-dismissable col-xs-12" style="margin-top:7px"><i class="fa fa-check"></i></i>&nbsp;&nbsp;<?php echo $responseSuccessMessage;?></div><?php  } ?>
				<div class="form-group">
					<input class="form-control" type="Email" name="Email"  id="Email"  placeholder="Email" required >
				</div>
			</div>
			<div class="footer">                                                               
				<input type="submit" name="merchant_forgot_submit" id="merchant_forgot_submit" value="SUBMIT" class="btn btn-success btn-lg btn-block ">
			</div>
			
			<div class="" align="center"><a href="Login"><i class="fa fa-reply"></i>&nbsp;&nbsp;back to login</a></div>
		</form>
	</div>
	<?php footerLogin(); ?>
	<?php commonFooter(); ?>
</html>