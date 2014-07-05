<?php

session_start();

// include MangoPay SDK
require_once '../../../MangoPaySDK/mangoPayApi.inc';
require_once 'config.php';

// check if payment has been initialized
if (!isset($_SESSION['amount'])) {
    die('<div style="color:red;">No payment has been started<div>');
}

// create instance of MangoPayApi
$mangoPayApi = new \MangoPay\MangoPayApi();
$mangoPayApi->Config->ClientId = 'tuplit-2014';
$mangoPayApi->Config->ClientPassword = 'reG1VZFeXSvxp6AcXKWKaL9qWmYQi9fMyfizMww8BgTzmgrmBK';
$mangoPayApi->Config->TemporaryFolder = 'temp/';

try {
    // update register card with registration data from Payline service
    $cardRegister = $mangoPayApi->CardRegistrations->Get($_SESSION['cardRegisterId']);
    $cardRegister->RegistrationData = isset($_GET['data']) ? 'data=' . $_GET['data'] : 'errorCode=' . $_GET['errorCode'];
   // echo '<pre>===>';print_r($cardRegister);echo '<===</pre>';
	$updatedCardRegister = $mangoPayApi->CardRegistrations->Update($cardRegister);
echo '<pre>===>';print_r($updatedCardRegister);echo '<===</pre>';
    if ($updatedCardRegister->Status != 'VALIDATED' || !isset($updatedCardRegister->CardId))
        die('<div style="color:red;">Cannot create virtual card. Payment has not been created.<div>');

    // get created virtual card object
    $card = $mangoPayApi->Cards->Get($updatedCardRegister->CardId);

    // create temporary wallet for user
	if(!isset($_SESSION['walletid'])) {
		$wallet = new \MangoPay\Wallet();
		$wallet->Owners = array( $updatedCardRegister->UserId );
		$wallet->Currency = $_SESSION['currency'];
		$wallet->Description = 'Temporary wallet for payment demo';
		$createdWallet = $mangoPayApi->Wallets->Create($wallet);
		$walletid	= $createdWallet->Id;
	}
	else
		$walletid	= $_SESSION['walletid'];
    // create pay-in CARD DIRECT
    $payIn = new \MangoPay\PayIn();
    $payIn->CreditedWalletId = $walletid;
    $payIn->AuthorId = $updatedCardRegister->UserId;
    $payIn->DebitedFunds = new \MangoPay\Money();
    $payIn->DebitedFunds->Amount = $_SESSION['amount'];
    $payIn->DebitedFunds->Currency = $_SESSION['currency'];
    $payIn->Fees = new \MangoPay\Money();
    $payIn->Fees->Amount = 0;
    $payIn->Fees->Currency = $_SESSION['currency'];

    // payment type as CARD
    $payIn->PaymentDetails = new \MangoPay\PayInPaymentDetailsCard();
    if ($card->CardType == 'CB' || $card->CardType == 'VISA' || $card->CardType == 'MASTERCARD')
        $payIn->PaymentDetails->CardType = 'CB_VISA_MASTERCARD';
    elseif ($card->CardType == 'AMEX')
        $payIn->PaymentDetails->CardType = 'AMEX';

    // execution type as DIRECT
    $payIn->ExecutionDetails = new \MangoPay\PayInExecutionDetailsDirect();
    $payIn->ExecutionDetails->CardId = $card->Id;
    $payIn->ExecutionDetails->SecureModeReturnURL = 'http://test.com';

    // create Pay-In
    $createdPayIn = $mangoPayApi->PayIns->Create($payIn);

    // if created Pay-in object has status SUCCEEDED it's mean that all is fine
    if ($createdPayIn->Status == 'SUCCEEDED') {
        print '<div style="color:green;">'.
                    'Pay-In has been created successfully. '
                    .'Pay-In Id = ' . $createdPayIn->Id 
                    . ', Wallet Id = ' . $walletid
                . '</div>';
    }
    else {
        // if created Pay-in object has status different than SUCCEEDED 
        // that occurred error and display error message
        print '<div style="color:red;">'.
                    'Pay-In has been created with status: ' 
                    . $createdPayIn->Status . ' (result code: '
                    . $createdPayIn->ResultCode . ')'
                .'</div>';
    }

} catch (\MangoPay\ResponseException $e) {
    
    print '<div style="color: red;">'
                .'\MangoPay\ResponseException: Code: ' 
                . $e->getCode() . '<br/>Message: ' . $e->getMessage()
                .'<br/><br/>Details: '; print_r($e->GetErrorDetails())
        .'</div>';
}
if(isset($walletid)) {
	$_SESSION['payinid'] = $createdPayIn->Id;
	$_SESSION['walletid'] = $walletid;
	header('Location:products.php');
	die();
}
else
	echo 'Unable to create new user. Try again later. ';
// clear data in session to protect against double processing
//$_SESSION = array();

