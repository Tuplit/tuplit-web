<?php

/**
 * Description of Cards
 *
 * @author 
 */

use RedBean_Facade as R;
use Helpers\PasswordHelper as PasswordHelper;
use Enumerations\HttpStatusCode as HttpStatusCode;
use Enumerations\AccountType as AccountType;
use Enumerations\StatusType as StatusType;
use Enumerations\ErrorCodeType as ErrorCodeType;
use Exceptions\ApiException as ApiException;
use Valitron\Validator as Validator;
use Helpers\RedBeanHelper as RedBeanHelper;

//Require needed models
require_once '../../admin/includes/mangopay/functions.php';
require_once '../../admin/includes/mangopay/config.php';
require_once '../../admin/includes/mangopay/MangoPaySDK/mangoPayApi.inc';
require_once '../../admin/includes/mangopay/MangoPaySDK/tools/restTool.inc';


class Cards extends RedBean_SimpleModel implements ModelBaseInterface {

	/**
	* Constructor
	*/
    public function __construct() {

    }

   
	/**
	* Create cards 
	*/
    public function create(){ 
		/**
		* Get the bean
		* @var $bean Cards
		*/
        $bean 						= 	$this->bean;
		
		// validate the model
        $this->validate();

        // validate the creation
        $userInfo							=	$this->validateCreate();
		
		$UserDetails['userAccountId']		=	$userInfo->MangoPayUniqueId;
		$UserDetails['userCurrency']		=	$bean->Currency;
		$UserDetails['walletId']			=	$userInfo->WalletId;
		$UserDetails['userWalletId']		=	$userInfo->WalletId;
		if(!isset($bean->Amount) || $bean->Amount == '')
			$UserDetails['amount']				=	1000;		
		else
			$UserDetails['amount']				=	$bean->Amount;		
		$UserDetails['cardNumber']			=	$bean->CardNumber;		
		$UserDetails['cardExpirationDate']	=	$bean->CardExpirationDate;		
		$UserDetails['cvv']					=	$bean->CVV;		
		$result								=	addCreditCard($UserDetails);
		if($result->CardId){
			$UserDetails['cardId']				=	$result->CardId;		
			$amountAdd 							=   topupWallet($UserDetails);
		}
		return $result;
    }

    /**
    * Validate the model
    * @throws ApiException if the models fails to validate required fields
    */
    public function validate()
    {
		$bean 		= 	$this->bean;		
		$rules 		= 	[
							'required' => [
								 ['Currency'],['CardNumber'],['CardExpirationDate'],['CVV']
							]
						];
		
        $v = new Validator($this->bean);//,['Amount']
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
            throw new ApiException("Please check the card's properties. Fill Currency,CardNumber,CardExpirationDate,CVV with correct values." ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
    }
	
	/**
	* Validate the creation
	*/
    public function validateCreate(){

		/**
		* Get the bean
		*/
        $bean 				= 	$this->bean;	
        $usersInfo		 	= 	R::findOne('users', 'id = ? and Status = ?', array($bean->UserId,StatusType::ActiveStatus));
        if (!$usersInfo) {
            // user not found
            throw new ApiException("The user was not in active status.", ErrorCodeType::UserNotInActiveStatus);
		} else {
			
			if(isset($bean->WalletId) || isset($bean->MangoPayId)) {
			
				if(empty($bean->WalletId) || empty($bean->MangoPayId)) {
					// incorrect WalletId or MangoPayUniqueId
					throw new ApiException("WalletId, MangoPayId are required.", ErrorCodeType::UserNotInActiveStatus);
				} else {
					$usersInfo['WalletId']			= $bean->WalletId;
					$usersInfo['MangoPayUniqueId']	= $bean->MangoPayId;
				}
				
				/*if(isset($bean->WalletId) && !empty($bean->WalletId) && $bean->WalletId != $usersInfo->WalletId) {
					 // incorrect WalletId
					throw new ApiException("Your WalletId was incorrect.", ErrorCodeType::UserNotInActiveStatus);
				}
				if(isset($bean->MangoPayUniqueId) && !empty($bean->MangoPayUniqueId) && $bean->MangoPayUniqueId != $usersInfo->MangoPayUniqueId) {
					 // incorrect MangoPayId
					throw new ApiException("Your MangoPayId was incorrect.", ErrorCodeType::UserNotInActiveStatus);
				}*/
			} else if(empty($usersInfo->WalletId) || empty($usersInfo->MangoPayUniqueId)) {
				 // not connected with MangoPay
				throw new ApiException("You are not connected with MangoPay Account.", ErrorCodeType::UserNotInActiveStatus);
			}		
			return $usersInfo;
		}		
    }	
	/**
	* get cards 
	*/
    public function getCards(){ 
		/**
		* Get the bean
		* @var $bean Cards
		*/
        $bean 		= 	$this->bean;
		$cardList  	=   $cardListArray = $cardArray = $newCard = array();
		global $cardTypeArray;
		if(isset($cardTypeArray) && count($cardTypeArray) >0){
			foreach ($cardTypeArray as $key=>$val){
				array_push($newCard,$key);
			}
		}
		$type		=   0;
        // validate the creation
		if($bean->Type)
			$type	= $bean->Type;
		if($type != 1)
        	$userInfo	=	$this->validateCreate();
		if($type == 1){
			$userAccountId				=	$bean->MangoPayUniqueId;
		}
		else
			$userAccountId				=	$userInfo->MangoPayUniqueId;
		if($userAccountId != ''){
			$result						=	getUserCards($userAccountId);
			if($result){
			  foreach($result as $key=>$val){
			  	if($val->Active == 1){
					$cardArray['Id']   				= $val->Id;
					$cardArray['CardNumbar'] 		= $val->Alias;
					$cardArray['CardType']   		= $val->CardType;
					$cardArray['ExpirationDate']   	= $val->ExpirationDate;
					$cardArray['Currency']   		= $val->Currency;
					$cardArray['Active']   			= $val->Active;
					if(in_array($val->CardType,$newCard)){
					  $cardArray['Image']   			= $cardTypeArray[$val->CardType];
					}
					else{
						$cardArray['Image']   			= ADMIN_SITE_PATH.'/webresources/cards/card.png';
					}
					$cardListArray[]				= $cardArray;
				}
			  }	
			  $cardList['result']					= $cardListArray;
			}
		}
		return $cardList;
    }
	/**
	* Topup wallet
	*/
    public function topup(){ 
		/**
		* Get the bean
		* @var $bean Cards
		*/
        $bean 						= 	$this->bean;

		// validate the model
        $this->topupValidate();

        // validate the creation
        $userInfo							=	$this->validateCreate();
		
		$UserDetails['userAccountId']		=	$userInfo->MangoPayUniqueId;
		$UserDetails['userCurrency']		=	$bean->Currency;
		$UserDetails['userWalletId']		=	$userInfo->WalletId;
		$UserDetails['amount']				=	$bean->Amount;		
		$UserDetails['cardId']				=	$bean->CardId;		
		$result								=	topupWallet($UserDetails);
		if(isset($result) && count($result) >0){
		  return $result;
		}
		//echo "<pre>"; echo print_r($result);
    }
	 /**
    * Validate the card id
    * @throws ApiException if the models fails to validate required fields
    */
    public function topupValidate()
    {
		$bean 		= 	$this->bean;		
		$rules 		= 	[
							'required' => [
								 ['CardId'],['Amount'],['Currency']
							]
						];
		
        $v = new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
            throw new ApiException("Please check the card's properties. Fill CardId,Amount,Currency with correct values." ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
    }
}
