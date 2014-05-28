<?php
require_once('includes/CommonIncludes.php');

if(isset($_SESSION['tuplit_merchant_user_name'])){
	//header('location:UserList?cs=1');
	//die();
}
$error = '';
if(isset($_POST['merchant_signup_submit']) && $_POST['merchant_signup_submit'] == 'SIGN UP'){
	$data	=	array(
					'FirstName' => $_POST['FirstName'],
					'LastName' => $_POST['LastName'],
					'Email' => $_POST['Email'],
					'Password' => $_POST['Password'],
					'CompanyName' => $_POST['CompanyName']
				);
	$url	=	WEB_SERVICE.'v1/merchants/';
	$method	=	'POST';
	$curlResponse	=	curlRequest($url,$method,$data);
	if(isset($curlResponse) && is_array($curlResponse) && $curlResponse['meta']['code'] == 201) {
		header("Location:Login?type=1");
		die();//
	} else if(isset($curlResponse['meta']['errorMessage']) && $curlResponse['meta']['errorMessage'] != '') {
		$responseMessage	=	$curlResponse['meta']['errorMessage'];
	} else {
		$responseMessage 	= 	"Bad Request";
	}
}

commonHead();
?>

<body class="skin-blue" onload="fieldfocus('user_name');">
		
		<?php top_header(); ?>
		
		<div class="form-box" id="login-box">
			<form action="#" name="add_merchant_form" id="add_merchant_form"  method="post">
				<div class="body">
					<?php if(isset($responseMessage) && $responseMessage != '') { ?>
						<div class="alert alert-danger alert-dismissable col-xs-12" style="margin-top:7px"><i class="fa fa-warning"></i>&nbsp;&nbsp;
							<?php echo $responseMessage;?>
						</div>
					<?php  } ?>
					
					<div class="form-group">
						<input class="form-control" type="text" name="FirstName"  id="FirstName"  placeholder="First Name" value="<?php if(isset($_POST['FirstName']) && $_POST['FirstName'] != '') echo $_POST['FirstName'];?>" required>
					</div>
					<div class="form-group">
						<input class="form-control" type="text" name="LastName"  id="LastName"  placeholder="Last Name" value="<?php if(isset($_POST['LastName']) && $_POST['LastName'] != '') echo $_POST['LastName'];?>" required >
					</div>
					<div class="form-group">
						<input class="form-control" type="email" name="Email"  id="Email"  placeholder="Email" value="<?php if(isset($_POST['Email']) && $_POST['Email'] != '') echo $_POST['Email'];?>"  required>
					</div>
					
					<div class="form-group">
						<input type="password" name="Password" id="Password" class="form-control" placeholder="Password" required />
					</div>    
					<div class="form-group">
						<input type="password" name="C_Password" id="C_Password" class="form-control" placeholder="Confirm Password" required />
					</div>
					
					<div class="form-group">
						<input class="form-control" type="text" name="CompanyName"  id="CompanyName"  placeholder="Company Name" required value="<?php if(isset($_POST['CompanyName']) && $_POST['CompanyName'] != '') echo $_POST['CompanyName'];?>" >
					</div>
					      
					<div class="form-group chk-box-error" style="line-height : 12px;height:35px">
						<label for="RememberMe"><input type="checkbox"  name="RememberMe" id="RememberMe" required>&nbsp;&nbsp;Agree to Terms & Conditions</label>
						
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
</html>