<?php

/**
 * Description of Cards
 *
 * @author 
 */
ini_set('default_encoding','utf-8');
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
		
		if(isset($bean->Amount) && !empty($bean->Amount) && $bean->Amount > 0)
			$this->validateAmount($bean->Amount);
		
		$UserDetails['userAccountId']		=	$userInfo->MangoPayUniqueId;
		$UserDetails['userCurrency']		=	$bean->Currency;
		$UserDetails['walletId']			=	$userInfo->WalletId;
		$UserDetails['userWalletId']		=	$userInfo->WalletId;
		$UserDetails['amount']				=	$bean->Amount;		
		$UserDetails['cardNumber']			=	$bean->CardNumber;		
		$UserDetails['cardExpirationDate']	=	$bean->CardExpirationDate;		
		$UserDetails['cvv']					=	$bean->CVV;	
		$result								=	addCreditCard($UserDetails);
		if($result->CardId){			
			$UserDetails['cardId']			=	$result->CardId;		
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
			//echo"<br>see here====sdfsfsdf===========";
			$result						=	getUserCards($userAccountId);
			//echo'<pre>';print_r($result);echo'</pre>';
			if($result){
			  foreach($result as $key=>$val){
			  	if(isset($val->Active) && $val->Active == 1){
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
			/*else {
				
				throw new ApiException("No cards found", ErrorCodeType::NoResultFound);
			}*/
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
		
		//validate amount
		$this->validateAmount($bean->Amount);
		
		$UserDetails['userAccountId']		=	$userInfo->MangoPayUniqueId;
		$UserDetails['userCurrency']		=	$bean->Currency;
		$UserDetails['userWalletId']		=	$userInfo->WalletId;
		$UserDetails['amount']				=	$bean->Amount;		
		$UserDetails['cardId']				=	$bean->CardId;		
		$result								=	topupWallet($UserDetails);
		if(isset($result) && $result->Status != 'FAILED'){
		  return $result;
		} else if(isset($result)){
			// error
			//throw new ApiException($result->ResultMessage, ErrorCodeType::NoResultFound);
				
				throw new ApiException("Error in topup", ErrorCodeType::NoResultFound);
		}
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
	
	 /**
    * Validate the amount
    */
    public function validateAmount($amount)
    {
		if ($amount <= 0 || $amount >= 80) {           
            throw new ApiException("Sorry you can't process this amount value. Try with less amount below ".utf8_encode('£')."80" , ErrorCodeType::NoResultFound);
        }
    }
	
	/**
    * Deleting user cards
    */
    public function cardDelete()
    {
		/**
		* Get the bean
		* @var $bean Cards
		*/
        $bean					= 	$this->bean;

        // validate the creation
        $userInfo				=	$this->validateCreate();
		
		//getting users cards
		$userCards				=	$this->getCards();
		if(count($userCards['result']) > 0) {
			$cardArray			=	array();
			foreach($userCards['result'] as $val) {
				if(!empty($val['Id'])) {
					$cardArray[]=	$val['Id'];
				}
			}
			//Check weather this card is for this user
			if(in_array($bean->CardId,$cardArray)) {
				$cardId			=	$bean->CardId;
				//deleting the card
				$result			=	deleteCard($cardId);
				if(isset($result) && count($result) > 0)
					return $result;
			} else {
				// Invalid user card Id
				throw new ApiException("Invalid card id. Unable to delete it.", ErrorCodeType::ErrorInCardDelete);
			}
		} else {
			// No cards found
			throw new ApiException("Sorry, you are not having card to delete", ErrorCodeType::NoResultFound);
		}
    }
}
