<?php
require_once('includes/CommonIncludes.php');

if(isset($_SESSION['tuplit_merchant_user_name'])){
	//header('location:UserList?cs=1');
	//die();
}
$error = '';

//countries list
$url						=	WEB_SERVICE.'v1/contents/countries/';
$curlLocationResponse 		= 	curlRequest($url, 'GET', null);
if(isset($curlLocationResponse) && is_array($curlLocationResponse) && $curlLocationResponse['meta']['code'] == 200 && is_array($curlLocationResponse['countries']) ) {
	if(isset($curlLocationResponse['countries']))
		$countries 			= 	$curlLocationResponse['countries'];
} else if(isset($curlLocationResponse['meta']['errorMessage']) && $curlLocationResponse['meta']['errorMessage'] != '')
	$errorMessage			=	$curlLocationResponse['meta']['errorMessage'];
else
	$errorMessage			= 	"Bad Request for countries";

//currencies list
$url						=	WEB_SERVICE.'v1/contents/currencies/';
$curlCurrenciesResponse 	= 	curlRequest($url, 'GET', null);
if(isset($curlCurrenciesResponse) && is_array($curlCurrenciesResponse) && $curlCurrenciesResponse['meta']['code'] == 200 && is_array($curlCurrenciesResponse['currencies']) ) {
	if(isset($curlCurrenciesResponse['currencies']))
		$currencies 		= 	$curlCurrenciesResponse['currencies'];
} else if(isset($curlCurrenciesResponse['meta']['errorMessage']) && $curlCurrenciesResponse['meta']['errorMessage'] != '')
	$errorMessage			=	$curlCurrenciesResponse['meta']['errorMessage'];
else
	$errorMessage			= 	"Bad Request for currencies";

//if(isset($_POST['merchant_signup_submit']) && $_POST['merchant_signup_submit'] == 'SIGN UP'){
if(isset($_POST) && !empty($_POST)){
	
	$FirstName 						= 	$_POST['FirstName'];
	$LastName 						= 	$_POST['LastName'];
	$BusinessName 					= 	$_POST['BusinessName'];
	$Email 							= 	$_POST['Email'];
	$PhoneNumber 					= 	$_POST['MobileNumber'];
	$Website 						= 	$_POST['Website'];
	$HowHeared 						= 	$_POST['ReferedBy'];
	$Describe 						= 	$_POST['Describe'];
	$data	=	array(
					'FirstName' 	=> 	$_POST['FirstName'],
					'LastName' 		=> 	$_POST['LastName'],
					'BusinessName' 	=> 	$_POST['BusinessName'],
					'Email' 		=> 	$_POST['Email'],
					'PhoneNumber' 	=> 	$_POST['MobileNumber'],
					'WebsiteUrl'	=> 	$_POST['Website'],
					'HowHeared' 	=> 	$_POST['ReferedBy'],
					'Describe' 		=> 	$_POST['Describe'],
					'Password' 		=> 	$_POST['Password']
				);
	$url					=	WEB_SERVICE.'v1/merchants/';
	$method					=	'POST';
	$curlResponse			=	curlRequest($url,$method,$data);
	if(isset($curlResponse) && is_array($curlResponse) && $curlResponse['meta']['code'] == 201) {
		header("Location:Login?type=1");
		die();
	} else if(isset($curlResponse['meta']['errorMessage']) && $curlResponse['meta']['errorMessage'] != '')
		$responseMessage	=	$curlResponse['meta']['errorMessage'];
	else
		$responseMessage 	= 	"Bad Request";
}
commonHead();
?>
<body onload="fieldfocus('user_name');">
	<?php top_header_before_login(); ?>
				<form action="" name="add_merchant_form_new" id="add_merchant_form_new"  method="post">
					<div class="signup-bg">
						<?php if(isset($responseMessage) && $responseMessage != '') { ?>
						<div class="alert alert-danger alert-dismissable col-xs-10 col-md-9 col-lg-9" style="margin-top:7px"><i class="fa fa-warning"></i>&nbsp;&nbsp;
							<?php echo $responseMessage;?>
						</div>
					<?php  } ?>
					<div class="col-xs-12 no-padding">
						<h3>Sign up</h3>
					</div>
					<div>
					<div class="col-xs-9 no-padding box-center">
						<div class="form-group pull-left" style="margin-right:5px;">
							<input class="form-control text-left" type="text" name="FirstName"  id="FirstName"  placeholder="*First Name" value="<?php if(isset($FirstName) && !empty($FirstName)) echo $FirstName; ?>"  required>
						</div>
						<div class="pull-right">
							<input class="form-control text-left" type="text" name="LastName"  id="LastName"  placeholder="*Last Name" value="<?php if(isset($LastName) && !empty($LastName)) echo $LastName; ?>" required >
						</div>
					</div>
					<div class="col-xs-9 no-padding box-center">
						<div class="form-group pull-left">
							<input class="form-control text-left" type="text" name="BusinessName"  id="BusinessName"  placeholder="*Business Name" value="<?php if(isset($BusinessName) && !empty($BusinessName)) echo $BusinessName; ?>" required  >
						</div>
						<div class="pull-right">
							<input class="form-control text-left" type="text" name="Email"  id="Email"  placeholder="*Email address" value="<?php if(isset($Email) && !empty($Email)) echo $Email; ?>" required>
						</div>
					</div>
					<div class="col-xs-9 no-padding box-center">
						<div class="form-group pull-left">
							<input class="form-control text-left" type="text" maxlength="15" name="MobileNumber"  id="MobileNumber" onkeypress="return isNumberKey(event);"  placeholder="*Mobile Number" value="<?php if(isset($PhoneNumber) && !empty($PhoneNumber)) echo $PhoneNumber; ?>" required>
						</div>
						<div class="pull-right">
							<input class="form-control text-left" type="text" name="Website"  id="Website" placeholder="Website address" value="<?php if(isset($Website) && !empty($Website)) echo $Website; ?>">
						</div>
					</div>
					<div class="col-xs-9 no-padding box-center">
						<div class="form-group pull-left">
							<input class="form-control text-left" type="password" name="Password"  id="Password" placeholder="*Password" value="" required>
						</div>
						<div class="pull-right">
							<input class="form-control text-left" type="password" name="CPassword"  id="CPassword" placeholder="*Confirm Password" value="" required>
						</div>
					</div>
					<div class="col-xs-9 no-padding box-center"><!-- select_other -->
						<div class="form-group pull-left" style="text-align:left;">
							<label>How did you hear about us?</label>
							<select class="form-control text-left" name="ReferedBy" id="ReferedBy">
								<option value="0">Other</option>
								<?php foreach($HowyouHeared as $refer_key=>$referer){ ?>
									<option value="<?php echo $refer_key; ?>" <?php if(isset($HowHeared) && $HowHeared == $refer_key) echo "selected"; ?>><?php echo $referer; ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="pull-right describe">
							<label>Describe shortly</label>
							<input class="form-control text-left" type="text" maxlength="15" name="Describe"  id="Describe" placeholder="Friend, TV, Newspaper..." value="<?php if(isset($Describe) && !empty($Describe)) echo  $Describe; ?>">
						</div>
					</div>
					</div>
						<div class="chk-box-error error-req box-center login_check" align="center">
							<label for="Terms"><input type="checkbox"  name="Terms" id="Terms" required>&nbsp;&nbsp;I agree with <a href="privacy_policy" target="_blank" title="Privacy Policy">Privacy Policy</a> and <a href="terms_of_service" target="_blank" title="Terms of Service">Terms of Service</a></label>
						</div>
						<div class="form-group" style="height:auto;">
							<input type="submit" name="merchant_signup_submit" id="merchant_signup_submit" value="Sign up" class="btn form-control">
							<p class="help-block text-center" style="margin-top:25px;">* Mandatory fields</p>
						</div>
					</div>
					<div class="login-button"><a href="Login">Login</a></div>
					
				</form>
			</div><!-- /content -->
		<?php footerLogin(); ?>
	<?php commonFooter(); ?>
</html>
