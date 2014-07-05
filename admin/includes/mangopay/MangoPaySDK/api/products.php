<?php 
session_start();
require_once '../../../MangoPaySDK/mangoPayApi.inc';
//require_once 'config.php';
// create instance of MangoPayApi SDK
$mangoPayApi = new \MangoPay\MangoPayApi();		// include MangoPay SDK
$mangoPayApi->Config->ClientId = 'tuplit-2014';
$mangoPayApi->Config->ClientPassword = 'reG1VZFeXSvxp6AcXKWKaL9qWmYQi9fMyfizMww8BgTzmgrmBK';
$mangoPayApi->Config->TemporaryFolder = 'temp/';
	//echo '<pre>===>';print_r($_SESSION);echo '<===</pre>';
if(isset($_GET['purchase']) && $_GET['purchase'] != '' && isset($_SESSION['walletid'])) {
	if(isset($_SESSION['available']) && trim($_SESSION['available']) < trim($_GET['purchase'])){
		echo '<div style="text-align:center;color:green">Not enough fund to buy this product.</div>';
	}
	else {
		// create user for payment
		$transfer = new MangoPay\Transfer();
		$transfer->DebitedWalletId 	= $_SESSION['walletid'];
		$transfer->CreditedWalletId = '2608407';			// MERCHANT WALLET ID
		$transfer->AuthorId 		= $_SESSION['userid'];
		$transfer->CreditedUserId 	= '2608404';			// MERCHANT USER ID
		//$transfer->Type 			= "";
		$transfer->ResultMessage 	= 'Payment for '.$_GET['purchase'].' worth product';
		//$transfer->Nature 			= "";
		$transfer->Tag 				= "";
		
		$transfer->DebitedFunds = new \MangoPay\Money();
		$transfer->DebitedFunds->Amount = $_GET['purchase'];
		$transfer->DebitedFunds->Currency = $_SESSION['currency'];
		
		$transfer->CreditedFunds = new \MangoPay\Money();
		$transfer->CreditedFunds->Amount = $_GET['purchase'];
		$transfer->CreditedFunds->Currency = $_SESSION['currency'];
		
		$transfer->Fees = new \MangoPay\Money();
		$transfer->Fees->Amount = 2;			// 	COMISSION
		$transfer->Fees->Currency = $_SESSION['currency'];
		//echo '<pre>===>';print_r($transfer);echo '<===</pre>';
		$transferCreate = $mangoPayApi->Transfers->Create($transfer);
		if(!empty($transferCreate)) {
			header('Location:products.php');
			die();
		}
		else
			echo 'Unable to complete payment. Try again later. ';
	}
}

$userWalletAmount		= $userWalletCurrency	= '';
$merchentWalletAmount	= $merchentWalletCurrency	= '';
if(isset($_SESSION['walletid'])) {
	$Wallet 	= $mangoPayApi->Wallets->Get($_SESSION['walletid']);
	$userWalletAmount	= $Wallet->Balance->Amount;
	$userWalletCurrency	= $Wallet->Balance->Currency;
	$_SESSION['available'] = $Wallet->Balance->Amount;
	/*$WalletM 	= $mangoPayApi->Wallets->Get('2608407');		// MERCHANT WALLET ID
	$merchentWalletAmount	= $WalletM->Balance->Amount;
	$merchentWalletCurrency	= $WalletM->Balance->Currency;*/
}
else {
	header('Location:login.php');
	die();
}	
//echo '<pre>===>';print_r($userWallet->Balance->Amount);echo '<===</pre>';
//$userWallet	= $_SESSION['amount'];
// NEED TO CHANGE THE PRODUCTS TO POST AND DO WALLET DECREMENTS
?>
<!DOCTYPE HTML>
<html>
	<header>
		<title>Buy Products using MANGOPAY API</title>
	</header>
	<body>
		<div style="float:left">PRODUCTS</div><br>
		<div>
			<div style="color:red;float:right;">Your MangoPay User id: <?php echo $_SESSION['userid']; ?></div><br>
			<?php if($userWalletAmount <= 0) {?>
			<div>Your have no amount to buy products <a href="amount.php">click here</a> to add more amount </div>
			<?php } ?>
			<!--Create New User-->
			<div style="float:left">&nbsp;</div><div style="float:right">Your Wallet amount : <?php echo $userWalletAmount.' '.$userWalletCurrency; ?></div>
			<br>
			<div style="clear:both;float:left">&nbsp;</div><div style="float:right"><a href="amount.php">Add more amount</a></div>
			<div style="clear:both;float:left">&nbsp;</div><div style="color:blue;float:right;"><a href="merchant.php">Merchant Details</a></div>
			 <table border="0" width="870">
            <tr>
                <td style="width: 250px" valign="top"></td>
                <td style="width: 20px"></td>
                <td style="width: 600px; padding: 0px;" valign="top">
					<form name="input" action="" method="post" enctype="multipart/form-data">
						<table>
							<tr>
								<td>Icecream</td>
								<td>
									<img src="images/1.jpg" width="100" height="100"/>
								</td>
								<td>100 <?php echo $userWalletCurrency; ?></td>
							</tr>
							<tr>
								<td colspan="1">&nbsp;</td>
								<td><a href="products.php?purchase=100">BUY NOW</a></td>
							</tr>
							<tr ><td height="30"></td></tr>
							<tr>
								<td>Chocolate</td>
								<td>
									<img src="images/2.jpg" width="100" height="100"/>
								</td>
								<td>200 <?php echo $userWalletCurrency; ?></td>
							</tr>
							<tr>
								<td colspan="1">&nbsp;</td>
								<td><a href="products.php?purchase=200">BUY NOW</a></td>
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