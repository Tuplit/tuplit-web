<?php 
session_start();
$_SESSION = array();
if(isset($_POST['submit']) && $_POST['submit'] != '') {
	// include MangoPay SDK
	require_once '../../../MangoPaySDK/mangoPayApi.inc';
	//require_once 'config.php';

	// sample payment data
	//$_SESSION['amount'] = 3300;
	//$_SESSION['currency'] = 'EUR';

	// create instance of MangoPayApi SDK
	$mangoPayApi = new \MangoPay\MangoPayApi();
	$mangoPayApi->Config->ClientId = 'tuplit-2014';
	$mangoPayApi->Config->ClientPassword = 'reG1VZFeXSvxp6AcXKWKaL9qWmYQi9fMyfizMww8BgTzmgrmBK';
	$mangoPayApi->Config->TemporaryFolder = 'temp/';

	// create user for payment
	$user = new MangoPay\UserNatural();
	$user->FirstName 	= $_POST['FirstName'];
	$user->LastName 	= $_POST['LastName'];
	$user->Email 		= $_POST['Email'];
	$user->Address 		= $_POST['Address'];
	$user->Birthday 	= strtotime($_POST['Birthday']);
	$user->Nationality 	= $_POST['Nationality'];
	$user->CountryOfResidence 	= $_POST['CountryOfResidence'];
	$user->Occupation 			= $_POST['Occupation'];
	$user->IncomeRange 			= $_POST['IncomeRange'];
	$createdUser = $mangoPayApi->Users->Create($user);
	
	if(isset($createdUser->Id)) {
		$_SESSION['userid'] = $createdUser->Id;
		header('Location:amount.php?userid='.$createdUser->Id);
		die();
	}
	else
		echo 'Unable to create new user. Try again later. ';
	//echo '<pre>===>';print_r($createdUser->Id);echo '<===</pre>';
}

?>
<!DOCTYPE HTML>
<html>
	<header>
		<title>Buy Products using MANGOPAY API</title>
	</header>
	<body>
		<div>
			<!--<a href="index.php?module=UserNatural_Users_Create">Create New User</a>-->
			CREATION OF NEW USER
			 <table border="0" width="870">
            <tr>
                <td style="width: 250px" valign="top"></td>
                <td style="width: 20px"></td>
                <td style="width: 600px; padding: 0px;" valign="top">
					<form name="input" action="" method="post" enctype="multipart/form-data">
						<table>
							<tr>
								<td>FirstName:</td>
								<td>
									<input type="text" required name="FirstName" value=""/>
								</td>
							</tr>
							<tr>
								<td>LastName:</td>
								<td>
									<input type="text" required name="LastName" value=""/>
								</td>
							</tr>
							<tr>
								<td>Address:</td>
								<td>
									<input type="text" name="Address" value=""/>
								</td>
							</tr>
							<tr>
								<td>Birthday:</td>
								<td>
									<input type="text" required placeholder="eg: DD-MM-YYYY" name="Birthday" value=""/>
								</td>
							</tr>
							<tr>
								<td>Nationality:</td>
								<td>
									<input type="text" required name="Nationality" placeholder="eg: FR"  value=""/>
								</td>
							</tr>
							<tr>
								<td>CountryOfResidence:</td>
								<td>
									<input type="text" required name="CountryOfResidence" placeholder="eg: FR" value=""/>
								</td>
							</tr>
							<tr>
								<td>Occupation:</td>
								<td>
									<input type="text" name="Occupation" value=""/>
								</td>
							</tr>
							<tr><td>IncomeRange:</td>
								<td>
									<input type="text" name="IncomeRange" placeholder="eg: 3" value=""/>
								</td>
							</tr>
							<tr>
								<td>Email:</td>
								<td>
									<input type="text" required name="Email" value=""/>
								</td>
							</tr>
							<tr>
								<td>Tag:</td>
								<td>
									<input type="text" name="Tag" value=""/>
								</td>
							</tr>
							<!--<tr>
								<td>Card Number:</td>
								<td>
									<input type="text" name="cardNumber" value=""/>
								</td>
							</tr>
							<tr>
								<td>Expiration Date:</td>
								<td>
									<input type="text" name="cardExpirationDate" value=""/>
								</td>
							</tr>
							<tr>
								<td>CVV:</td>
								<td>
									<input type="text" name="cardCvx" value=""/>
								</td>
							</tr>-->
							<tr>
								<td>
								</td>
								<td>
									<input type="submit" name="submit" value="Create" />
								</td>
							</tr>
						</table>
						<input type="hidden" name="_postback" value="1"/>
				</form>
				</td>
            </tr>
        </table>
		</div>
	</body>
</html>