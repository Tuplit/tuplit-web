<?php 
require_once 'config.php';
require_once 'MangoPaySDK/mangoPayApi.inc';

$mangoPayApi = new \MangoPay\MangoPayApi();
$mangoPayApi->Config->ClientId = MangoPayDemo_ClientId;
$mangoPayApi->Config->ClientPassword = MangoPayDemo_ClientPassword;
$mangoPayApi->Config->TemporaryFolder = 'temp/';




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
		if(isset($createdUser->Id)) {
			return $createdUser->Id;
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
		if(isset($createdUser->Id)) {
			return $createdUser->Id;
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
* function to add credit card details
*/
function addCreditCard($userDetails){
	try{
		$cardRegister = new \MangoPay\CardRegistration();
		$cardRegister->UserId 	= $userDetails['userAccountId'];
		$cardRegister->Currency = $userDetails['userCurrency'];
		$createdCardRegister = $mangoPayApi->CardRegistrations->Create($cardRegister);
		$data
		
		$handle = curl_init();
		curl_setopt($handle, CURLOPT_URL, $url);
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





