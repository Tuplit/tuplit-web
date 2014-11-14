<?php
ob_start();
require_once('includes/CommonIncludes.php');
if(isset($_SESSION['tuplit_merchant_user_name'])){
	//header('location:UserList?cs=1');
	//die();
}
$responseErrorMessage = $responseSuccessMessage = '';

//if(isset($_POST['merchant_forgot_submit']) && $_POST['merchant_forgot_submit'] == 'SUBMIT'){
if(isset($_POST) && !empty($_POST)){
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
<body onload="fieldfocus('Email');">
	<?php top_header_before_login (); ?>
				<h2>Get the best deals!</h2>
				<h3>Tuplit Merchants have the best deals for you.<br>Get them before they expire!</h3>
				<a class="signup-button" href="Signup" title="Sign up">Sign up</a>
				<form action="" name="merchant_login_form" id="merchant_login_form"  method="post">
					<div class="login-bg">
						<div style="height:25px;clear:both;">
						<div class="col-xs-12">
				<?php if($responseErrorMessage !='') { ?><div class="alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10 clear" style="margin-top:20px;"><i class="fa fa-warning"></i>&nbsp;&nbsp;<?php echo $responseErrorMessage;?></div><?php  } ?>
				<?php if($responseSuccessMessage !='') { ?><div class="alert alert-success alert-dismissable col-lg-4 col-sm-5 col-xs-10 clear" style="margin-top:20px;"><i class="fa fa-check"></i></i>&nbsp;&nbsp;<?php echo $responseSuccessMessage;?></div><?php  } ?>
						</div>
						</div>
				<div class="form-col">
					<input class="form-control" type="Email" name="Email"  id="Email"  placeholder="Email" required >
				</div>
				<div class="form-col">                                                               
				<input type="submit" name="merchant_forgot_submit" id="merchant_forgot_submit" value="Submit" class="btn form-control ">
				</div>
			</div>
			
			<div class="" align="center" style="margin-bottom:5px;">
			
			<a href="Login" title="Back to login" class="link"><i class="fa fa-reply"></i>&nbsp;&nbsp;Back to login</a></div>
		</form>
	</div>
	<?php footerLogin(); ?>
	<?php commonFooter(); ?>
</html>