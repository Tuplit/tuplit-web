<?php
require_once('includes/CommonIncludes.php');
//cookies check
$msg_class 		= "alert alert-danger alert-dismissable col-xs-12";
$class_icon   	= "fa-warning";
$doLogin  = 0;

//echo'<pre>';print_r($_SESSION);echo'</pre>';die();
if(isset($_COOKIE['tuplit_merchant_email']) && $_COOKIE['tuplit_merchant_email'] != '' && isset($_COOKIE['tuplit_merchant_password']) && $_COOKIE['tuplit_merchant_password'] != '' && isset($_COOKIE['tuplit_merchant_logout']) && $_COOKIE['tuplit_merchant_logout'] != '') {
	$cookie_email 		= 	$_COOKIE['tuplit_merchant_email'];
	$cookie_password 	= 	decryption($_COOKIE['tuplit_merchant_password']);
	if($_COOKIE['tuplit_merchant_logout'] == 'login') {
			$data		=	array(
							'ClientId' => CLIENT_ID,
							'ClientSecret' => CLIENT_SECRET,
							'Email' => $cookie_email,
							'Password' => $cookie_password
						);
			$doLogin  	= 	1;
	}
	else{
		if(!empty($cookie_email) || !empty($cookie_password) )
		$cookie_rem 	= 	1;
	}
}

//login after sign up process
if(isset($_GET['type']) && $_GET['type'] == 1){
	$responseMessage 	= 	'You have registered successfully.Please wait till you get approval mail.';
	$msg_class 		 	= 	"alert alert-success alert-col-xs-4";
	$class_icon   		= 	"fa-check";
}

if (isset($_SESSION['merchantInfo']['AccessToken']) && $_SESSION['merchantInfo']['AccessToken'] != ''){
	header("Location:Dashboard");
	die();
}
if(isset($_SESSION['ErrorMessages']) && $_SESSION['ErrorMessages'] !=''){
	$responseMessage 	= 	$_SESSION['ErrorMessages'];
	unset($_SESSION['ErrorMessages']);
}

$error = '';
if(isset($_POST['merchant_login_submit']) && $_POST['merchant_login_submit'] == 'LOG IN'){
	$data				=	array(
							'ClientId' => CLIENT_ID,
							'ClientSecret' => CLIENT_SECRET,
							'Email' => $_POST['Email'],
							'Password' => $_POST['Password']
						);
	$doLogin  			= 	1;
}
if($doLogin){
	$url							=	WEB_SERVICE.'oauth2/password/token/merchants/';
	$method							=	'POST';
	$curlResponse					=	curlRequest($url,$method,$data);
	if(isset($curlResponse) && is_array($curlResponse) && $curlResponse['meta']['code'] == 201 && $curlResponse['login']['Status'] == 'success' ) {
		$_SESSION['merchantInfo']   =	$curlResponse['login'];
		
		//cookie assign		
		if (isset($_POST['remember_me']) && $_POST['remember_me'] != '' && $_POST['remember_me'] == 'on') {			
			setCookies($_POST);
		} else {
			destroyCookies();
		}
		
		//login after sign up process		
		$merchantId					= 	$_SESSION['merchantInfo']['MerchantId'];
		$url						=	WEB_SERVICE.'v1/merchants/'.$merchantId.'?From=0';
		$curlMerchantResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
		if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201 && $curlMerchantResponse['merchant']['MerchantId'] != '' ) {
			$_SESSION['merchantDetailsInfo']   =	$curlMerchantResponse['merchant'];
			if(isset($curlMerchantResponse['merchant']['Address']) && !empty($curlMerchantResponse['merchant']['Address'])){
				header("Location:Dashboard");
				die();
			}
			else{
				header("Location:Myaccount");	
				die();
			}	
		}
		else{
			$responseMessage 	= 	$curlMerchantResponse['meta']['errorMessage'];
		}
	} else if(isset($curlResponse['meta']['errorMessage']) && $curlResponse['meta']['errorMessage'] != '') {
		$responseMessage		=	$curlResponse['meta']['errorMessage'];
	} else {
		$responseMessage 		= 	"Bad Request";
	}
}
commonHead();
?>
<body class="skin-blue fixed" onload="fieldfocus('user_name');">
		<?php top_header(); ?>
		<div class="banner bg-gray" style="margin-top:-25px;"><img src="webresources/images/banner1.jpg" width="100%" height="250" alt=""></div>
		
		<div class="form-box" id="login-box">
			<form action="" name="merchant_login_form" id="merchant_login_form"  method="post">
				<div class="body">
					<?php if(isset($responseMessage) && $responseMessage != '') { ?>
						<div class="<?php echo $msg_class; ?>" style="margin-top:7px"><i class="fa <?php echo $class_icon; ?>"></i>&nbsp;&nbsp;
							<?php echo $responseMessage;?>
						</div>
					<?php  } ?>
					
					<div class="form-group">
						<input class="form-control" type="email" name="Email"  id="Email"  placeholder="Email" required value="<?php if(isset($_POST['Email']) && $_POST['Email'] != '') echo $_POST['Email']; else if(isset($cookie_email) && !empty($cookie_email)) echo $cookie_email;?>">
					</div>
					<div class="form-group">
						<input type="password" name="Password" id="Password" class="form-control" placeholder="Password" value="<?php if(isset($_POST['Password']) && $_POST['Password'] != '') echo $_POST['Password']; else if(isset($cookie_password) && !empty($cookie_password)) echo $cookie_password;?>"/>
					</div>          
					<div class="form-group">
						<div class="col-xs-6 no-padding"><input type="checkbox" id='remember_me'  name="remember_me" class="fleft no-margin"<?php if(isset($_POST['remember_me']) && $_POST['remember_me']  == 'on') echo "checked";  else if(!isset($_POST['Email']) && isset($cookie_rem) && $cookie_rem == '1')  echo "checked"; ?>><label for='remember_me' class="fleft">&nbsp;&nbsp;Remember me</label></div>
						<div class="col-xs-6 no-padding" align="right"><a href="ForgotPassword" title="Forgot password"><i class="fa fa-lock"></i>&nbsp;&nbsp;Forgot password</a></div>
						
					</div>
				</div>
				<div class="footer">                                                               
					<input type="submit" name="merchant_login_submit" id="merchant_login_submit" title="LOG IN" value="LOG IN" class="btn btn-success btn-lg btn-block top-margin">
				</div>
				
				<div class="" align="center"><a href="Signup" title="Sign Up"><i class="fa fa-long-arrow-right"></i>&nbsp;&nbsp;Sign Up</a></div>
			</form>
		</div>
		<?php footerLogin(); ?>
	<?php commonFooter(); ?>
</html>