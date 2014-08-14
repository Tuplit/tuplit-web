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

if(isset($_POST['merchant_signup_submit']) && $_POST['merchant_signup_submit'] == 'SIGN UP'){
	
	$FirstName 							= 	$_POST['FirstName'];
	$LastName 							= 	$_POST['LastName'];
	$Email 								= 	$_POST['Email'];
	$PhoneNumber 						= 	$_POST['MobileNumber'];
	$BusinessName 						= 	$_POST['BusinessName'];
	$BusinessTypeval 					= 	$_POST['BusinessType'];
	$CompanyName 						= 	$_POST['CompanyName'];
	$RegisterCompanyNumber 				= 	$_POST['CompanyNumber'];
	$Address 							= 	$_POST['Address'];
	$Country 							= 	$_POST['Country'];
	$PostCode 							= 	$_POST['Postcode'];
	$Currency 							= 	$_POST['Currency'];
	$HowHeared 							= 	$_POST['ReferedBy'];
	$data	=	array(
					'FirstName' 		=> 	$_POST['FirstName'],
					'LastName' 			=> 	$_POST['LastName'],
					'Email' 			=> 	$_POST['Email'],
					'PhoneNumber' 		=> 	$_POST['MobileNumber'],
					'BusinessName' 		=> 	$_POST['BusinessName'],
					'BusinessType' 		=> 	$_POST['BusinessType'],
					'CompanyName' 		=> 	$_POST['CompanyName'],
					'RegisterCompanyNumber' => $_POST['CompanyNumber'],
					'Address' 			=> 	$_POST['Address'],
					'Country' 			=> 	$countries[$_POST['Country']]['Location'],
					'PostCode' 			=> 	$_POST['Postcode'],
					'Password' 			=> 	$_POST['Password'],
					'Currency' 			=> 	$currencies[$_POST['Currency']]['Code'],
					'HowHeared' 		=> 	$_POST['ReferedBy']
				);
	$url					=	WEB_SERVICE.'v1/merchants/signup';
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

<body class="skin-blue fixed" onload="fieldfocus('user_name');">
		
		<?php top_header(); ?>
		
		<div class="form-box" id="login-box">
			<form action="#" name="add_merchant_form_new" id="add_merchant_form_new"  method="post">
				<div class="body">
					<?php if(isset($responseMessage) && $responseMessage != '') { ?>
						<div class="alert alert-danger alert-dismissable col-xs-12" style="margin-top:7px"><i class="fa fa-warning"></i>&nbsp;&nbsp;
							<?php echo $responseMessage;?>
						</div>
					<?php  } ?>
					
					<div class="form-group">
						<input class="form-control" type="text" name="FirstName"  id="FirstName"  placeholder="First Name" value="<?php if(isset($FirstName) && !empty($FirstName)) echo $FirstName; ?>"  required>
					</div>
					<div class="form-group">
						<input class="form-control" type="text" name="LastName"  id="LastName"  placeholder="Last Name" value="<?php if(isset($LastName) && !empty($LastName)) echo $LastName; ?>" required >
					</div>
					<div class="form-group">
						<input class="form-control" type="email" name="Email"  id="Email"  placeholder="Email" value="<?php if(isset($Email) && !empty($Email)) echo $Email; ?>" required>
					</div>
					<div class="form-group">
						<input class="form-control" type="text" name="MobileNumber"  id="MobileNumber" onkeypress="return isNumberKey(event);"  placeholder="Mobile Number" value="<?php if(isset($PhoneNumber) && !empty($PhoneNumber)) echo $PhoneNumber; ?>" required>
					</div>
					<div class="form-group">
						<input type="password" name="Password" id="Password" class="form-control" placeholder="Password" required />
					</div>    
					<div class="form-group">
						<input type="password" name="C_Password" id="C_Password" class="form-control" placeholder="Confirm Password" required />
					</div>
					<div class="form-group">
						<input class="form-control" type="text" name="BusinessName"  id="BusinessName"  placeholder="Business Name" value="<?php if(isset($BusinessName) && !empty($BusinessName)) echo $BusinessName; ?>" required  >
					</div>
					<div class="form-group">
						<label>Business Type</label>&nbsp;&nbsp;&nbsp;
						<select class="form-control" name="BusinessType" id="BusinessType" placeholder="Business Type"  required />
							<option value="">Select</option>
							<?php foreach($BusinessType as $busi_key=>$busi_type){ ?>
								<option value="<?php echo $busi_key; ?>" <?php if(isset($BusinessTypeval) && $BusinessTypeval == $busi_key) echo "selected"; ?>><?php echo $busi_type; ?></option>
							<?php } ?>
						</select>
					</div>
					<div class="form-group">
						<label>Company details</label>
						<input class="form-control" type="text" name="CompanyName"  id="CompanyName"  placeholder="Company Name" value="<?php if(isset($CompanyName) && !empty($CompanyName)) echo $CompanyName; ?>" required  >
						<input class="form-control" type="text" name="CompanyNumber"  id="CompanyNumber"  placeholder="Registered Company Number" value="<?php if(isset($RegisterCompanyNumber) && !empty($RegisterCompanyNumber)) echo $RegisterCompanyNumber; ?>" required  >
						<textarea class="form-control" id="Address" name="Address" placeholder="Address" cols="5"><?php if(isset($Address) && !empty($Address)) echo $Address; ?></textarea>
						<input class="form-control" type="text" name="Postcode" onkeypress="return isNumberKey(event);"  id="Postcode"  placeholder="Postcode" value="<?php if(isset($PostCode) && !empty($PostCode)) echo $PostCode; ?>" required >
						<select class="form-control" name="Country" id="Country" />
							<option value="">Choose Country</option>
							<?php if(isset($countries) && !empty($countries) && count($countries)>0) { foreach($countries as $code){ ?>
								<option value="<?php echo $code['id']; ?>" <?php if(isset($Currency) && $Currency == $code['id']) echo "selected"; ?>><?php echo $code['Location']; ?></option>
							<?php } }?>
						</select>
						
					</div>					
					<div class="form-group">
						<select class="form-control" name="Currency1" id="Currency1" disabled/>
							<option value="">Choose Currency</option>
							<?php if(isset($currencies) && !empty($currencies) && count($currencies)>0) { foreach($currencies as $code){ ?>
								<option  value="<?php echo $code['fkLocationId']; ?>" <?php if(isset($Currency) && $Currency == $code['fkLocationId']) echo "selected"; ?>><?php echo $code['Code']; ?></option>
							<?php } }?>
						</select>
						<input type="hidden" name="Currency" id="Currency" value="<?php if(isset($Currency) && !empty($Currency)) echo $Currency; ?>">
					</div>
					<div class="form-group">
						<label>How did you hear about us?</label>&nbsp;&nbsp;&nbsp;
						<select class="form-control" name="ReferedBy" id="ReferedBy" required />
							<option value="">Select an option</option>
							<?php foreach($HowyouHeared as $refer_key=>$referer){ ?>
								<option value="<?php echo $refer_key; ?>" <?php if(isset($HowHeared) && $HowHeared == $refer_key) echo "selected"; ?>><?php echo $referer; ?></option>
							<?php } ?>
						</select>
					</div>
					<div class="form-group chk-box-error" >
						<label for="RememberMe"><input type="checkbox"  name="RememberMe" id="RememberMe" >&nbsp;&nbsp;Remember Me</label>
						<label for="Terms"><input type="checkbox"  name="Terms" id="Terms" required>&nbsp;&nbsp;Agree to Terms & Conditions</label>
					</div>
				</div>
				<div class="footer">                                                               
					<input type="submit" name="merchant_signup_submit" id="merchant_signup_submit" value="SIGN UP" class="btn btn-success btn-lg btn-block ">
				</div>
				<div class="" align="center"><a href="Login"><i class="fa fa-long-arrow-right"></i>&nbsp;&nbsp;Login</a></div>
			</form>
		</div>
		<?php footerLogin(); ?>
	<?php commonFooter(); ?>
	<script type="text/javascript">
		$( "#Country" ).change(function() {
			value	=	$('#Country').val();
			$("#Currency1").val(value);
			$("#Currency").val(value);
		});
	</script>
</html>
