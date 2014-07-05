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
	
	$userWallets = $mangoPayApi->Users->GetWallets($_POST['userid']);
	if(!empty($userWallets)) {
		$_SESSION['userid'] 	= $_POST['userid'];
		$_SESSION['amount'] 	= $userWallets[0]->Balance->Amount;
		$_SESSION['currency'] 	= $userWallets[0]->Balance->Currency;
		$_SESSION['walletid'] 	= $userWallets[0]->Id;
		
		if(isset($userWallets[0]->Id)) {
			header('Location:products.php');
			die();
		}
		else
			echo 'Unable to create new user. Try again later. ';
	}
	else
		echo 'User not found.';
	// create user for payment
	//$user = new MangoPay\UserNatural();
	//$createdUser = $mangoPayApi->Users->Get($_POST['userid']);
	
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
								<td>MangoPay User id:</td>
								<td>
									<input type="text" name="userid" required value=""/>
								</td>
							</tr>
							<tr>
								<td>
								</td>
								<td>
									<input type="submit" name="submit" value="Login" />
								</td>
							</tr>
							<tr><td height="30"><a href="demo.php">New user registration</a></td></tr>
						</table>
						<input type="hidden" name="_postback" value="1"/>
				</form>
				</td>
            </tr>
        </table>
		</div>
	</body>
</html>