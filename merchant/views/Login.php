<?php
require_once('includes/CommonIncludes.php');
//cookies check
$msg_class 		= "alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10 clear";
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
	$responseMessage 	= 	'You have registered successfully. Please wait till you get approval mail.';
	//$responseMessage 	= 	'You have registered successfully. An email is sent with activation link';
	
	$msg_class 		 	= 	"alert alert-success col-xs-8 col-md-6 col-lg-5";
	$class_icon   		= 	"fa-check";
}

if (isset($_SESSION['merchantInfo']['AccessToken']) && $_SESSION['merchantInfo']['AccessToken'] != ''){
	$_SESSION['MerchantPortalAccessTime']   =	time();
	$_SESSION['MerchantPortalAskPin']   	=	0;
	header("Location:Dashboard");
	die();
}
if(isset($_SESSION['ErrorMessages']) && $_SESSION['ErrorMessages'] !=''){
	$responseMessage 	= 	$_SESSION['ErrorMessages'];
	unset($_SESSION['ErrorMessages']);
}

$error = '';
//if(isset($_POST['merchant_login_submit']) && $_POST['merchant_login_submit'] == 'LOG IN'){
if(isset($_POST) && !empty($_POST)){
	$data				=	array(
							'ClientId' => CLIENT_ID,
							'ClientSecret' => CLIENT_SECRET,
							'Email' => $_POST['Email'],
							'Password' => $_POST['Password']
						);
	$doLogin  			= 	1;
}
if($doLogin){
	unset($_SESSION['merchantSubuser']);
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

			if(isset($curlMerchantResponse['merchant']['UserType']) && !empty($curlMerchantResponse['merchant']['UserType']) && $curlMerchantResponse['merchant']['UserType'] == 2){
				//Login as sub user
				$merchantId					= 	$curlMerchantResponse['merchant']['MainMerchantId'];
				$url						=	WEB_SERVICE.'v1/merchants/'.$merchantId.'?From=0';
				$curlMerchantResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
				if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201 && $curlMerchantResponse['merchant']['MerchantId'] != '' ) {
					$_SESSION['merchantSubuser']		=	'1';
					$_SESSION['merchantDetailsInfo']   	=	$curlMerchantResponse['merchant'];
					
					$url				=	WEB_SERVICE.'oauth2/password/token/merchants/';
					$method				=	'POST';
					$data				=	array(
												'ClientId' => CLIENT_ID,
												'ClientSecret' => CLIENT_SECRET,
												'Email' => $curlMerchantResponse['merchant']['Email'],
												'Password' => ''
											);
					$curlResponse		=	curlRequest($url,$method,$data);
					if(isset($curlResponse) && is_array($curlResponse) && $curlResponse['meta']['code'] == 201 && $curlResponse['login']['Status'] == 'success' ) {
						$_SESSION['merchantInfo']   =	$curlResponse['login'];
						$_SESSION['MerchantPortalAccessTime']   =	time();
						$_SESSION['MerchantPortalAskPin']   	=	0;
						header("Location:Dashboard");
						die();
					}
				}
				else{
					$responseMessage 	= 	$curlMerchantResponse['meta']['errorMessage'];
				}
			} else {
				$_SESSION['merchantDetailsInfo']   		=	$curlMerchantResponse['merchant'];
				$_SESSION['MerchantPortalAccessTime']   =	time();
				$_SESSION['MerchantPortalAskPin']   	=	0;
				if(isset($curlMerchantResponse['merchant']['Address']) && !empty($curlMerchantResponse['merchant']['Address'])){
					header("Location:Dashboard");
					die();
				}
				else{
					header("Location:MyStore");	
					die();
				}	
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
<body onload="fieldfocus('user_name');">
		<?php top_header_before_login(); ?>
				<h2>Get the best deals!</h2>
				<h3>Tuplit Merchants have the best deals for you.<br><strong>Get them before they expire!</strong></h3>
				<a class="signup-button" href="Signup" title="Sign up">Sign up</a>
				<form action="" name="merchant_login_form" id="merchant_login_form"  method="post">
					<div class="login-bg">
						<div style="height:25px;clear:both;" >
						<div class="col-xs-12">
						<?php if(isset($responseMessage) && $responseMessage != '') { ?>
							<div class="<?php echo $msg_class; ?>" style="margin-top:20px;"><i class="fa <?php echo $class_icon; ?>"></i>&nbsp;&nbsp;
								<?php echo $responseMessage;?>
							</div>
						<?php  } ?>
						</div>
						</div>
						<div class="form-col">
							<input class="form-control" type="email" name="Email"  id="Email"  placeholder="Email" required value="<?php if(isset($_POST['Email']) && $_POST['Email'] != '') echo $_POST['Email']; else if(isset($cookie_email) && !empty($cookie_email)) echo $cookie_email;?>">
						</div>
						<div class="form-col">
							<input type="password" name="Password" id="Password" class="form-control" placeholder="Password" value="<?php if(isset($_POST['Password']) && $_POST['Password'] != '') echo $_POST['Password']; else if(isset($cookie_password) && !empty($cookie_password)) echo $cookie_password;?>"/>
						</div>
						<div class="form-col">
							<input type="submit" name="merchant_login_submit" id="merchant_login_submit" title="Log in" value="Log in" class="btn form-control">
						</div>
					</div>
					<div class="login_check" align="center" style="display:table;margin:auto;">
						<label for='remember_me' class="fleft link"><input type="checkbox" id='remember_me'  name="remember_me" class="fleft no-margin"<?php if(isset($_POST['remember_me']) && $_POST['remember_me']  == 'on') echo "checked";  else if(!isset($_POST['Email']) && isset($cookie_rem) && $cookie_rem == '1')  echo "checked"; ?> />
						Remember me</label>
						<br><a href="ForgotPassword" title="I forgot my password" class="link">I forgot my password</a>
					</div>
				</form>
			</div><!-- /content -->
		
		<?php footerLogin(); ?>
		
	<?php commonFooter(); ?>
</html>