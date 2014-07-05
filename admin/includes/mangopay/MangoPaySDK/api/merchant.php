<?php 
session_start();
require_once '../../../MangoPaySDK/mangoPayApi.inc';
//require_once 'config.php';
// create instance of MangoPayApi SDK
$mangoPayApi = new \MangoPay\MangoPayApi();		// include MangoPay SDK
$mangoPayApi->Config->ClientId = 'tuplit-2014';
$mangoPayApi->Config->ClientPassword = 'reG1VZFeXSvxp6AcXKWKaL9qWmYQi9fMyfizMww8BgTzmgrmBK';
$mangoPayApi->Config->TemporaryFolder = 'temp/';
/*
if(isset($_GET['purchase']) && $_GET['purchase'] != '' && isset($_SESSION['walletid'])) {
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
	if(empty($transferCreate))
		echo 'Unable to complete payment. Try again later. ';
	//echo '<pre>===>';print_r($createdUser->Id);echo '<===</pre>';
	//echo '<pre>===>';print_r($_SESSION);echo '<===</pre>';
}
*/
$WalletTransactions	= '';
$userWalletAmount	= $userWalletCurrency	= '';
$merchentWalletAmount	= $merchentWalletCurrency	= '';
if(isset($_SESSION['userid'])) {
	/*$Wallet 	= $mangoPayApi->Wallets->Get($_SESSION['walletid']);
	$userWalletAmount	= $Wallet->Balance->Amount;
	$userWalletCurrency	= $Wallet->Balance->Currency;*/
	
	$WalletM 	= $mangoPayApi->Wallets->Get(Merchant_Wallet);		// MERCHANT WALLET ID
	$merchentWalletAmount	= $WalletM->Balance->Amount;
	$merchentWalletCurrency	= $WalletM->Balance->Currency;
	
	// GET USERS LIST: GET /users
    $pagination = new MangoPay\Pagination(1, 100);
    //$users = $api->Users->GetAll($pagination);
	
	$WalletTransactions 	= $mangoPayApi->Wallets->GetTransactions(Merchant_Wallet,$pagination);		// MERCHANT WALLET ID
	
	//echo '<pre>===>';print_r($WalletTransactions);echo '<===</pre>';
}
else {
	header('Location:login.php');
	die();
}	
//echo '<pre>===>';print_r($WalletTransactions);echo '<===</pre>';
//$userWallet	= $_SESSION['amount'];
// NEED TO CHANGE THE PRODUCTS TO POST AND DO WALLET DECREMENTS
?>
<!DOCTYPE HTML>
<html>
	<header>
		<title>Buy Products using MANGOPAY API</title>
	</header>
	<body>
		<!--<div style="">PRODUCTS</div><br>-->
		<div>
			<div style="float:left"><a href="products.php">Go Back</a></div>
			<!--<div style="color:red;float:right;">Your MangoPay User id: <?php //echo $_SESSION['userid']; ?></div><br><br>-->
			<!--Create New User-->
			<div style="float:left">&nbsp;</div><div style="float:right">Your Total amount : <?php echo $merchentWalletAmount.' '.$merchentWalletCurrency; ?></div>
			<br>
			<table border="0" width="870">
            <tr>
                <td style="width: 250px" valign="top"></td>
                <td style="width: 20px"></td>
                <td style="width: 600px; padding: 0px;" valign="top">
					<form name="input" action="" method="post" enctype="multipart/form-data">
						<table cellpadding='5' cellspacing='1' border='2'>
							<tr>
								<th>User</th>
								<th>Transaction Id</th>
								<th>Fund Transferred</th>
								<th>Commission Fees</th>
								<th>Credited Funds</th>
							</tr>
							<?php if(!empty($WalletTransactions)) { 
									foreach($WalletTransactions as $key=>$value) { 
										if($value->Status == 'SUCCEEDED') { ?>
										<tr>
											<td><?php echo $value->AuthorId; ?></td>
											<td><?php echo $value->Id; ?></td>
											<td><?php echo $value->DebitedFunds->Amount; ?></td>
											<td><?php echo $value->Fees->Amount; ?></td>
											<td><?php echo $value->CreditedFunds->Amount; ?></td>
										</tr>
							<?php	} }
								} ?>
						</table>
					</form>
				</td>
            </tr>
        </table>
		</div>
	</body>
</html>