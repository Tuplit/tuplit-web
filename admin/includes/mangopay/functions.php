<?php 
require_once 'config.php';
require_once 'MangoPaySDK/mangoPayApi.inc';

/**/
//demo things
		$userDetails['CompanyName']	=	'Burger';
		$userDetails['Email']		=	'burger@gmail.com';
		$userDetails['FirstName']	=	'burger';
		$userDetails['LastName']	=	'king';
		$userDetails['Email']		=	'burger@gmail.com';
		$userDetails['Address']		=	'chicago';
		$userDetails['Birthday']	=	'01-01-1989';//mm-dd-yy
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
		$normalRegister = userRegister($normalUserDetails);
		$userAccountId = 2689185;
		
		$userDetails['merchantAccountId'] 	= $merchantAccountId;
		$userDetails['userAccountId'] 		= $userAccountId;
		$userDetails['userCurrency']		= 'USD';
		
		//create wallet
		//$walletId = createWallet($userDetails['userAccountId'],$userDetails['userCurrency']);
		$userWalletId = 2692790;
		
		//$merchantWalletId = createWallet($userDetails['merchantAccountId'],$userDetails['userCurrency']);
		$merchantWalletId = 2692796;
		
		$cardID = addCreditCard($userDetails);
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
		/*if(isset($createdUser->Id)) {
			return $createdUser->Id;
		}
		else {
			return 0;
		}*/
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
		$createdUser 				= $mangoPayApi->Users->Create($user);
		/*echo "<pre>"; print_r($createdUser  ); echo "</pre>";
		die();
		if(isset($createdUser->Id)) {
			return $createdUser->Id;
		}
		else {
			return 0;
		}*/
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
	$mangoPayApi = new \MangoPay\MangoPayApi();
	$mangoPayApi->Config->ClientId = MangoPayDemo_ClientId;
	$mangoPayApi->Config->ClientPassword = MangoPayDemo_ClientPassword;
	$mangoPayApi->Config->TemporaryFolder = 'temp/';
	try{
		$cardRegister = new \MangoPay\CardRegistration();
		$cardRegister->UserId 	= $userDetails['userAccountId'];
		$cardRegister->Currency = $userDetails['userCurrency'];
		$createdCardRegister = $mangoPayApi->CardRegistrations->Create($cardRegister);
		echo'<pre>';print_r($createdCardRegister);echo'</pre>';
		//$data
		
		$handle = curl_init();
		curl_setopt($handle, CURLOPT_URL, $createdCardRegister['']);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($handle, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); 
		curl_setopt($handle, CURLOPT_POST, true);
		curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
		$response = curl_exec($handle);
		
		
		
	}
	catch(Exception $e) {
		return $e;//error in field values
	}

}





