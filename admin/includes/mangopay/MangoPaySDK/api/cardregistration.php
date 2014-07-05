<?php
session_start();
// include MangoPay SDK
require_once '../../../MangoPaySDK/mangoPayApi.inc';
//require_once 'config.php';
if (!isset($_GET['userid'])) {
    die('<div style="color:red;">No payment has been started<div>');
}
if (!isset($_GET['amount'])) {
    die('<div style="color:red;">No payment has been started<div>');
}
// sample payment data
$_SESSION['userid'] = $_GET['userid'];
$_SESSION['amount'] = $_GET['amount'];
$_SESSION['currency'] = $_GET['currency'];

// create instance of MangoPayApi SDK
$mangoPayApi = new \MangoPay\MangoPayApi();
$mangoPayApi->Config->ClientId = 'tuplit-2014';
$mangoPayApi->Config->ClientPassword = 'reG1VZFeXSvxp6AcXKWKaL9qWmYQi9fMyfizMww8BgTzmgrmBK';
$mangoPayApi->Config->TemporaryFolder = 'temp/';
/*
// create user for payment
$user = new MangoPay\UserNatural();
$user->FirstName = 'John';
$user->LastName = 'Smith';
$user->Email = 'email@domain.com';
$user->Address = "Some Address";
$user->Birthday = time();
$user->Nationality = 'FR';
$user->CountryOfResidence = 'FR';
$user->Occupation = "programmer";
$user->IncomeRange = 3;
$createdUser = $mangoPayApi->Users->Create($user);
*/
// register card
$cardRegister = new \MangoPay\CardRegistration();
$cardRegister->UserId = $_GET['userid'];
$cardRegister->Currency = $_SESSION['currency'];
$createdCardRegister = $mangoPayApi->CardRegistrations->Create($cardRegister);
$_SESSION['cardRegisterId'] = $createdCardRegister->Id;
//echo '<pre>===>';print_r($createdCardRegister);echo '<===</pre>';
// build the return URL to capture token response
$returnUrl = 'http' . ( isset($_SERVER['HTTPS']) ? 's' : '' ) . '://' . $_SERVER['HTTP_HOST'];
$returnUrl .= substr($_SERVER['REQUEST_URI'], 0, strripos($_SERVER['REQUEST_URI'], '/') + 1);
$returnUrl .= 'payment.php';

?>

<p>
  <i>
    Shows how to register the card without using JavaScript <br />
    and process payments with page reload.
  </i>
</p>

<!--<label>Full Name</label>
<label><?php //print $createdUser->FirstName . ' ' . $createdUser->LastName; ?></label>-->
<div class="clear"></div>
<div style="color:red;float:right;">Your MangoPay User id: <?php echo $_SESSION['userid']; ?></div>
<form action="<?php print $createdCardRegister->CardRegistrationURL; ?>" method="post">
    <input type="hidden" name="data" value="<?php print $createdCardRegister->PreregistrationData; ?>" />
    <input type="hidden" name="accessKeyRef" value="<?php print $createdCardRegister->AccessKey; ?>" />
    <input type="hidden" name="returnURL" value="<?php print $returnUrl; ?>" />
	
	<!--<label for="cardCvx">Amount</label>
    <input type="text" name="amount" value="" />
    <div class="clear"></div>-->
	
    <label for="cardNumber">Card Number</label>
    <input type="text" name="cardNumber" required value="" />
    <div class="clear"></div>

    <label for="cardExpirationDate">Expiration Date</label>
    <input type="text" name="cardExpirationDate" required value="" />
    <div class="clear"></div>

    <label for="cardCvx">CVV</label>
    <input type="text" name="cardCvx" required value="" />
    <div class="clear"></div>

    <input type="submit" value="Pay" />
</form>