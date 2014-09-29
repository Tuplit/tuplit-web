<?php 
require_once 'config.php';
require_once 'MangoPaySDK/mangoPayApi.inc';
require_once 'MangoPaySDK/mangoPayApi.inc';
/**/
	//demo things
	$userDetails['CompanyName']	=	'Burger';
	$userDetails['Email']		=	'burger@gmail.com';
	$userDetails['FirstName']	=	'burger';
	$userDetails['LastName']	=	'king';
	$userDetails['Email']		=	'burger@gmail.com';
	$userDetails['Address']		=	'chicago';
	$userDetails['Birthday']	=	'01-01-1989';//dd-mm-yy
	$userDetails['Country']		=	'US';
	$userDetails['Country']		=	'US';
	$userDetails['CompanyName']	=	'Burgerking';
	
	//register as legal user
	//$register = merchantRegister($userDetails);
	$merchantAccountId = 2689052;
	
	
	
	$normalUserDetails['FirstName']		=	'kalpana';
	$normalUserDetails['LastName']		=	'D';
	$normalUserDetails['Email']			=	'user@gmail.com';
	$normalUserDetails['Address']		=	'newyork';
	$normalUserDetails['Birthday']		=	'02-01-1986';
	$normalUserDetails['Nationality'] 	=	'US';
	$normalUserDetails['CountryOfResidence'] 	=	'US';
	$normalUserDetails['Occupation'] 	=	'';
	$normalUserDetails['IncomeRange'] 	=	'';
	
	//register as user
	//$normalRegister = userRegister($normalUserDetails);
	$userAccountId = 2754966;//2689185;
	
	$userDetails['merchantAccountId'] 	= $merchantAccountId;
	$userDetails['userAccountId'] 		= $userAccountId;
	$userDetails['userCurrency']		= 'USD';
	$userDetails['amount']				= '1000';
	$userDetails['userWalletId']		= '2754968';
	$userDetails['CardId']				= '2755103';
	//create wallet
	//$walletId = createWallet($userDetails['userAccountId'],$userDetails['userCurrency']);
	$userWalletId = 2692790;
	
	//$merchantWalletId = createWallet($userDetails['merchantAccountId'],$userDetails['userCurrency']);
	$merchantWalletId = 2692796;
	/*if($_SERVER['REMOTE_ADDR']=='172.21.4.81') {
	refundTransfer($userDetails);}*/
	//« EUR »,« USD »,« GBP »,« PLN »,« CHF ».
	//http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2#US
	//http://en.wikipedia.org/wiki/ISO_4217
/**/

/*
* function to login Mangopay with mangopayuserID
*/
function login($userAccountId){
	try {
		$userWallets = $mangoPayApi->Users->GetWallets($userAccountId);
		return $userWallets;
	}
	catch(Exception $e) {
		return $e;
	}
}

/*
* function to register with Mangopay as user
*/
function userRegister($userDetails){
	require_once 'mangopayAPI.php';
	$mangoPayApi = new \MangoPay\MangoPayApi();
	$mangoPayApi->Config->ClientId = MangoPayDemo_ClientId;
	$mangoPayApi->Config->ClientPassword = MangoPayDemo_ClientPassword;
	$mangoPayApi->Config->TemporaryFolder = ABS_PATH.'/admin/includes/mangopay/temp/';

	try {
		$user = new MangoPay\UserNatural();
		$user->FirstName 			= $userDetails['FirstName'];
		$user->LastName 			= $userDetails['LastName'];
		$user->Email 				= $userDetails['Email'];
		$user->Address 				= $userDetails['Address'];
		$user->Birthday 			= strtotime($userDetails['Birthday']);
		$user->Nationality 			= $userDetails['Nationality'];
		$user->CountryOfResidence 	= $userDetails['CountryOfResidence'];
		$user->Occupation 			= $userDetails['Occupation'];
		$user->IncomeRange 			= $userDetails['IncomeRange'];
		//call create function
		$createdUser 				= $mangoPayApi->Users->Create($user);
		if(isset($createdUser)) {
			return $createdUser;
		}
		else {
			return 0;
		}
	}
	catch(Exception $e) {
		return $e;//error in field values
		
	}
}

/*
* function to register with Mangopay as merchant
*/
function merchantRegister($userDetails){
	
	require_once 'mangopayAPI.php';
	$mangoPayApi = new \MangoPay\MangoPayApi();
	$mangoPayApi->Config->ClientId = MangoPayDemo_ClientId;
	$mangoPayApi->Config->ClientPassword = MangoPayDemo_ClientPassword;
	$mangoPayApi->Config->TemporaryFolder = ABS_PATH.'/admin/includes/mangopay/temp/';
	
	try {
		$user = new MangoPay\UserLegal();
		
		$user->Name 									= $userDetails['CompanyName'];
		$user->Email 									= $userDetails['Email'];
		$user->LegalPersonType 							= "BUSINESS";
		$user->LegalRepresentativeFirstName				= $userDetails['FirstName'];
		$user->LegalRepresentativeLastName 				= $userDetails['LastName'];
		$user->LegalRepresentativeEmail					= $userDetails['Email'];
		$user->HeadquartersAddress						= $userDetails['Address'];
		$user->LegalRepresentativeBirthday 				= strtotime($userDetails['Birthday']);
		$user->LegalRepresentativeNationality			= $userDetails['Country'];
		$user->LegalRepresentativeCountryOfResidence	= $userDetails['Country'];
		$user->Tag										= 'Merchant - ' . $userDetails['CompanyName'];
		//call create function
		$createdUser 									= $mangoPayApi->Users->Create($user);
		
		if(isset($createdUser)) {
			return $createdUser;
		}
		else {
			return 0;
		}
	}
	catch(Exception $e) {
		return $e;//error in field values
	}
}

/*
* function to create wallet
*/
function createWallet($uniqueId,$currency){
	$mangoPayApi = new \MangoPay\MangoPayApi();
	$mangoPayApi->Config->ClientId = MangoPayDemo_ClientId;
	$mangoPayApi->Config->ClientPassword = MangoPayDemo_ClientPassword;
	$mangoPayApi->Config->TemporaryFolder = ABS_PATH.'/admin/includes/mangopay/temp/';
	try {
		$wallet = new \MangoPay\Wallet();
		$wallet->Owners = array($uniqueId);
		$wallet->Currency = $currency;
		$wallet->Description = 'Wallet for tuplit user,merchant';
		$createdWallet = $mangoPayApi->Wallets->Create($wallet);
		$walletid	= $createdWallet->Id;
		return $walletid;
	}
	catch(Exception $e) {
		return $e;//error in field values
	}
}

/*
* function to add credit card details
*/
function addCreditCard($userDetails){
	$mangoPayApi 							= 	new \MangoPay\MangoPayApi();
	$mangoPayApi->Config->ClientId 			= 	MangoPayDemo_ClientId;
	$mangoPayApi->Config->ClientPassword 	= 	MangoPayDemo_ClientPassword;
	$mangoPayApi->Config->TemporaryFolder 	= 	ABS_PATH.'/admin/includes/mangopay/temp/';
	$errorCodeArray = array();
	try{
	
		$cardRegister 						= 	new \MangoPay\CardRegistration();
		$cardRegister->UserId 				= 	$userDetails['userAccountId'];
		$cardRegister->Currency 			= 	$userDetails['userCurrency'];
		$cardRegister						= 	$mangoPayApi->CardRegistrations->Create($cardRegister);
		$cardRegister->RegistrationData 	= 	getPaylineCorrectRegistartionData($cardRegister,$userDetails);
	    $cardRegister 						= 	$mangoPayApi->CardRegistrations->Update($cardRegister);
		if($cardRegister->Status != 'ERROR'){
     	   $card 							= 	$mangoPayApi->Cards->Get($cardRegister->CardId);
		}
		if($userDetails['amount'] > 0){
			$payIn 								= 	new \MangoPay\PayIn();
			$payIn->CreditedWalletId 			= 	$userDetails['walletId'];
			$payIn->AuthorId 					= 	$cardRegister->UserId;
			$payIn->DebitedFunds 				= 	new \MangoPay\Money();
			$payIn->DebitedFunds->Amount 		= 	getCents($userDetails['amount']);
			$payIn->DebitedFunds->Currency 		= 	$userDetails['userCurrency'];
		
			$payIn->Fees 						= 	new \MangoPay\Money();
			$payIn->Fees->Amount 				= 	0;
			$payIn->Fees->Currency 				= 	$userDetails['userCurrency'];

	    // payment type as CARD
			$payIn->PaymentDetails 				= 	new \MangoPay\PayInPaymentDetailsCard();
			if ($card->CardType == 'CB' || $card->CardType == 'VISA' || $card->CardType == 'MASTERCARD')
				$payIn->PaymentDetails->CardType = 	'CB_VISA_MASTERCARD';
			else if ($card->CardType == 'AMEX')
				$payIn->PaymentDetails->CardType = 	'AMEX';
			
	    // execution type as DIRECT
			$payIn->ExecutionDetails 			= 	new \MangoPay\PayInExecutionDetailsDirect();
			$payIn->ExecutionDetails->CardId 	= 	$card->Id;
			$payIn->ExecutionDetails->SecureModeReturnURL = 'http://test.com';
		
	    // create Pay-In
			$createdPayIn 						= 	$mangoPayApi->PayIns->Create($payIn);
		//	echo "<pre>"; print_r( $createdPayIn); echo "</pre>";
		}
		return $cardRegister;
	}
	catch(Exception $e) {
		return $e;//error in field values
	}
	
}
function getWalletDetails($walletid){
	$mangoPayApi 							= 	new \MangoPay\MangoPayApi();
	$mangoPayApi->Config->ClientId 			= 	MangoPayDemo_ClientId;
	$mangoPayApi->Config->ClientPassword 	= 	MangoPayDemo_ClientPassword;
	$mangoPayApi->Config->TemporaryFolder 	= 	ABS_PATH.'/admin/includes/mangopay/temp/';
	try{
		$details = $mangoPayApi->Wallets->Get($walletid);
		return $details;
	}
	catch(Exception $e) {
		return $e;//error in field values
	}
}
/**
 * Get registration data from Payline service
 * @param \MangoPay\CardRegistration $cardRegistration
 * @return string
 */
function getPaylineCorrectRegistartionData($cardRegistration,$userDetails) {

		/*$cardNumber 		=	'4970100000000162';	//4970100000000154 // 4970101122334463
		$cardExpirationDate	=	'0220';
		$cardCvx			=	'123';*/
		
		$cardNumber 		=	$userDetails['cardNumber'];
		$cardExpirationDate	=	$userDetails['cardExpirationDate'];
		$cardCvx			=	$userDetails['cvv'];

      $data = 'data=' . $cardRegistration->PreregistrationData .
              '&accessKeyRef=' . $cardRegistration->AccessKey .
              '&cardNumber='.$cardNumber.'' .
              '&cardExpirationDate='.$cardExpirationDate.'' .
              '&cardCvx='.$cardCvx.'';
			  
      $curlHandle = curl_init($cardRegistration->CardRegistrationURL);
      curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($curlHandle, CURLOPT_POST, true);
      curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $data);
      $response = curl_exec($curlHandle);
      if ($response === false && curl_errno($curlHandle) != 0)
          throw new \Exception('cURL error: ' . curl_error($curlHandle));

      curl_close($curlHandle);
      return $response;
}
		
/**
    * Get card of user
    */
function getUserCards($UserId){
	//Create an instance of MangoPayApi SDK
	$mangoPayApi = new \MangoPay\MangoPayApi();
	$mangoPayApi->Config->ClientId = MangoPayDemo_ClientId;
	$mangoPayApi->Config->ClientPassword = MangoPayDemo_ClientPassword;
	$mangoPayApi->Config->TemporaryFolder = ABS_PATH.'/admin/includes/mangopay/temp/';
	//Build the parameters for the request
	try{
		$Pagination = new \MangoPay\Pagination();
		 
		//Send the request
		
		$Pagination->ItemsPerPage = 100;
		
		$result = $mangoPayApi->Users->GetCards($UserId, $Pagination);
	
		//Analyse the request
		return $result;
	}
	catch(Exception $e) {
		return $e;//error in field values
	}
}
		
/**
 * Creates Pay-Out object
 * @return \MangoPay\Transfer
 */
function transfer($userDetails) {
//Create an instance of MangoPayApi SDK
	$mangoPayApi 							= 	new \MangoPay\MangoPayApi();
	$mangoPayApi->Config->ClientId 			= 	MangoPayDemo_ClientId;
	$mangoPayApi->Config->ClientPassword 	= 	MangoPayDemo_ClientPassword;		
	$mangoPayApi->Config->TemporaryFolder 	= 	ABS_PATH.'/admin/includes/mangopay/temp/';

	try{
		//Build the parameters for the request
       $transfer 								= 	new \MangoPay\Transfer();
       $transfer->Tag 							= 	'Transfer';
       $transfer->AuthorId 						= 	$userDetails['AuthorId'];//'2712673';
	   $transfer->CreditedUserId  				= 	$userDetails['CreditedUserId'];//'2754966';
	
       $transfer->DebitedFunds 					= 	new \MangoPay\Money();
       $transfer->DebitedFunds->Currency 		= 	$userDetails['Currency'];
       $transfer->DebitedFunds->Amount 			= 	getCents($userDetails['Amount']);
	
       $transfer->Fees 							= 	new \MangoPay\Money();
       $transfer->Fees->Currency 				= 	$userDetails['Currency'];
       $transfer->Fees->Amount 					= 	0;
       $transfer->DebitedWalletId 				= 	$userDetails['DebitedWalletId'];//'2712674';
       $transfer->CreditedWalletId 				= 	$userDetails['CreditedWalletId'];//'2754968';
	
		//Send the request
		$result 								= 	$mangoPayApi->Transfers->Create($transfer);
	
		return $result;
	}
	catch(Exception $e) {
		return $e;//error in field values
	}
}
	
function topupWallet($userDetails){
	//Create an instance of MangoPayApi SDK
	$mangoPayApi = new \MangoPay\MangoPayApi();
	$mangoPayApi->Config->ClientId = MangoPayDemo_ClientId;
	$mangoPayApi->Config->ClientPassword = MangoPayDemo_ClientPassword;
	$mangoPayApi->Config->TemporaryFolder 	= 	ABS_PATH.'/admin/includes/mangopay/temp/';
	//Build the parameters for the request
	try{
		// create pay-in CARD DIRECT
        $payIn = new \MangoPay\PayIn();
        $payIn->CreditedWalletId 		= $userDetails['userWalletId']	;
        $payIn->AuthorId 				= $userDetails['userAccountId'];
        $payIn->DebitedFunds 			= new \MangoPay\Money();
        $payIn->DebitedFunds->Amount 	= getCents($userDetails['amount']);
        $payIn->DebitedFunds->Currency 	= $userDetails['userCurrency'];
        $payIn->Fees 					= new \MangoPay\Money();
        $payIn->Fees->Amount 			= 0;
        $payIn->Fees->Currency 			= $userDetails['userCurrency'];
        // payment type as CARD
        $payIn->PaymentDetails 			= new \MangoPay\PayInPaymentDetailsCard();
        $payIn->PaymentDetails->CardId 	= $userDetails['cardId'];
		$card 							= $mangoPayApi->Cards->Get($userDetails['cardId']);
        if ($card->CardType == 'CB' || $card->CardType == 'VISA' || $card->CardType == 'MASTERCARD')
            $payIn->PaymentDetails->CardType = 'CB_VISA_MASTERCARD';
        elseif ($card->CardType == 'AMEX')
           $payIn->PaymentDetails->CardType = 'AMEX';
		   
        // execution type as DIRECT
		
        $payIn->ExecutionDetails 		= new \MangoPay\PayInExecutionDetailsDirect();
        $payIn->ExecutionDetails->SecureModeReturnURL = 'http://test.com';
        $result =  $mangoPayApi->PayIns->Create($payIn);
		return  $result;
	}
	catch(Exception $e) {
		return $e;//error in field values
	}
}
function payment($userDetails){
	//Create an instance of MangoPayApi SDK
	$mangoPayApi 							= 	new \MangoPay\MangoPayApi();
	$mangoPayApi->Config->ClientId 			= 	MangoPayDemo_ClientId;
	$mangoPayApi->Config->ClientPassword 	= 	MangoPayDemo_ClientPassword;		
	$mangoPayApi->Config->TemporaryFolder 	= 	ABS_PATH.'/admin/includes/mangopay/temp/';
	
	try{
		//Build the parameters for the request
        $transfer 								= 	new \MangoPay\Transfer();
        $transfer->Tag 							= 	'Payment';
        $transfer->AuthorId 					= 	$userDetails['AuthorId'];
		$transfer->CreditedUserId  				= 	$userDetails['CreditedUserId'];
		
		$amt = getCents($userDetails['Amount']);
        $transfer->DebitedFunds 				= 	new \MangoPay\Money();
        $transfer->DebitedFunds->Currency 		= 	$userDetails['Currency'];
        $transfer->DebitedFunds->Amount 		= 	$amt;
		
		$fees = ($userDetails['FeesAmount']/100)*$amt;
        $transfer->Fees 						= 	new \MangoPay\Money();
        $transfer->Fees->Currency 				= 	$userDetails['Currency'];
        $transfer->Fees->Amount 				= 	$fees;
        $transfer->DebitedWalletId 				= 	$userDetails['DebitedWalletId'];
        $transfer->CreditedWalletId 			= 	$userDetails['CreditedWalletId'];
		//Send the request
		$result 								= 	$mangoPayApi->Transfers->Create($transfer);
		return $result;
	}
	catch(Exception $e) {
		return $e;//error in field values
	}
}
function deleteCard($cardId){

 	//Create an instance of MangoPayApi SDK
	$mangoPayApi 							= 	new \MangoPay\MangoPayApi();
	$mangoPayApi->Config->ClientId 			= 	MangoPayDemo_ClientId;
	$mangoPayApi->Config->ClientPassword 	= 	MangoPayDemo_ClientPassword;		
	$mangoPayApi->Config->TemporaryFolder 	= 	ABS_PATH.'/admin/includes/mangopay/temp/';
	
	try{
		//Build the parameters for the request
		$getCard    		= $mangoPayApi->Cards->Get($cardId);
		if( $getCard->Active != ''){
			$getCard->Active	= 'false';
			//Send the request
			$result = $mangoPayApi->Cards->Update($getCard);
			return $result;
		}
		else if($getCard->Active == ''){		
			$result			=	new stdClass;
			$result->Id		=	$getCard->Id;
			$result->msg	=	'Card already deleted';
			return $result;
		} else {
			return 0;
		}
	}
	catch(Exception $e) {
		return $e;//error in field values
	}

}

function GetTransactionsNew($inputarray){
 //Create an instance of MangoPayApi SDK
	$mangoPayApi 							= 	new \MangoPay\MangoPayApi();
	$mangoPayApi->Config->ClientId 			= 	MangoPayDemo_ClientId;
	$mangoPayApi->Config->ClientPassword 	= 	MangoPayDemo_ClientPassword;		
	$mangoPayApi->Config->TemporaryFolder 	= 	ABS_PATH.'/admin/includes/mangopay/temp/';
	
	try{
		//parameters
		$ID									=	$inputarray['Id'];
		
		$Pagination 						= 	new \MangoPay\Pagination();
		//$Pagination->Page 				= 	1;
		$Pagination->ItemsPerPage 			= 	100;
		
		$Filter 							= 	new \MangoPay\FilterTransactions();		
		if(isset($inputarray['Start']) && !empty($inputarray['Start']))	
			$Filter->AfterDate 				= 	$inputarray['Start'];
		if(isset($inputarray['End']) && !empty($inputarray['End']))		
			$Filter->BeforeDate 			= 	$inputarray['End'];
		if(isset($inputarray['Status']) && !empty($inputarray['Status']))		
			$Filter->Status 				= 	$inputarray['Status'];
		if(isset($inputarray['Nature']) && !empty($inputarray['Nature']))		
			$Filter->Nature 				= 	$inputarray['Nature'];
		//$Filter->Type 					= 	"TRANSFER";
		//$Filter->Status 					= 	"SUCCEEDED";
		//$Filter->Nature 					= 	"REGULAR";
		//$Filter->Direction 				= 	"CREDIT";
		//$result								=	array();
		
		//Send the request
		$result 							= 	$mangoPayApi->Wallets->GetTransactions($ID, $Pagination, $Filter);
		return $result;
	}
	catch(Exception $e) {
		return $e;//error in field values
	}

}

function refundTransfer($userDetails){
	//Create an instance of MangoPayApi SDK
	$mangoPayApi 							= 	new \MangoPay\MangoPayApi();
	$mangoPayApi->Config->ClientId 			= 	MangoPayDemo_ClientId;
	$mangoPayApi->Config->ClientPassword 	= 	MangoPayDemo_ClientPassword;		
	$mangoPayApi->Config->TemporaryFolder 	= 	ABS_PATH.'/admin/includes/mangopay/temp/';
	
	try{
		$fees = ($userDetails['FeeAmount']/100)*$userDetails['Amount'];
		
		$TransferID 						= 	$userDetails['TransferID'];
		$Refund 							= 	new \MangoPay\Refund();
		$Refund->AuthorId 					= 	$userDetails['AuthorId'];
		$Refund->DebitedFunds 				= 	new \MangoPay\Money();
		$Refund->DebitedFunds->Currency 	= 	"USD";
		$Refund->DebitedFunds->Amount 		= 	$userDetails['Amount'];
		$Refund->Fees 						= 	new \MangoPay\Money();
		$Refund->Fees->Currency 			= 	"USD";
		$Refund->Fees->Amount 				= 	$fees;
		//Send the request
		$result 							= 	$mangoPayApi->Transfers->CreateRefund($TransferID, $Refund);
		return $result;
	}
	catch(Exception $e) {
		return $e;//error in field values
	}
}


