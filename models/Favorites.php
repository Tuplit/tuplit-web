<?php
/**
 * Description of Favorites
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

//Require needed models
require_once 'Merchants.php';

class Favorites extends RedBean_SimpleModel implements ModelBaseInterface {

	/**
	* Constructor
	*/
    public function __construct() {
		
    }

	/**
	* Insert favorites
	*/
    public function create(){ 
		/**
		* Get the bean
		* @var $bean Favorites
		*/
        $bean 			= 	$this->bean;
		
		// validate the parameters
        $this->validate();
		
		// validate create
        $favoritedBy	=	$this->validateCreate();		
		if($favoritedBy) {
			if($favoritedBy->FavouriteType == 1 && $bean->FavouriteType == 1){
				throw new ApiException("You have already favorited this merchant." ,  ErrorCodeType::AlreadyFavoured);//already fav
			}
			if($favoritedBy->FavouriteType == 0 && $bean->FavouriteType == 0){
				throw new ApiException("You have already unfavorited this merchant." ,  ErrorCodeType::AlreadyFavoured);//already fav
			}
			if($favoritedBy->FavouriteType == 1 && $bean->FavouriteType == 0){
					$bean->id 	= 	$favoritedBy->id;
					$msg 		= 	'You have unfavorited this merchant successfully';
			}
			if($favoritedBy->FavouriteType == 0 && $bean->FavouriteType == 1){
					$bean->id 	= 	$favoritedBy->id;
					$msg		= 	'You have favorited this merchant successfully';
			}
		}	
				
		$bean->FavouriteDate 	= 	date('Y-m-d H:i:s');		
		$bean->fkMerchantsId 	= 	$bean->MerchantId;
		$bean->fkUsersId 		= 	$bean->UsersId;
		unset($bean->MerchantId);
		unset($bean->UsersId);
		
		// save the bean to the database		
		$favoriteId 			= 	R::store($this);
		
		if(isset($msg) && !empty($msg))
			return $msg;
		else
			return 'You have favorited this merchant successfully';
    }	
	
	/**
	* get users favorites list
	*/
    public function usersFavoritesList(){ 
		/**
		* Get the bean
		* @var $bean Favorites
		*/
        $bean 			= 	$this->bean;
		$condition		=	'';
		if(isset($bean->Name))
			$condition	=	" and m.CompanyName like '%".$bean->Name."%'";
			
		//getting user favorite merchantIds
		$sql 			= 	"select f.fkMerchantsId from favorites f
								left join merchants m on (f.fkMerchantsId = m.id) 
								where f.FavouriteType = 1  and f.fkUsersId ='".$bean->UsersId."' ".$condition;
		$result 		= 	R::getAll($sql);
		$merchantIds 	= 	'';
		if($result) {
			foreach($result as $val) {
					$merchantIdsArray[] 	= 	$val['fkMerchantsId'];
			}
			$merchantIds 					= 	implode(',',$merchantIdsArray);
			$merchant 						= 	R::dispense('merchants');
			$merchant->Latitude				= 	$bean->Latitude;
			$merchant->Longitude			= 	$bean->Longitude;
			$merchant->Start				= 	$bean->Start;
			$merchantList 					= 	$merchant->getMerchantList($merchantIds);
			return $merchantList;
		}
		else {
			throw new ApiException("you have not favorited any merchants." ,  ErrorCodeType::UserFavouriteListEmpty);//no results found
		}
		
    }	
	
	/**
	* Validate the model
	* @throws ApiException if the models fails to validate required fields
	*/
    public function validate()
    {
		$bean		= 	$this->bean;
		$rules 		= 	[
							'required' => [
								['MerchantId'],['UsersId'],['FavouriteType']
							],				
							'in' =>[
								['FavouriteType',['0','1']]
							]
						];
        $v 			= 	new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
            throw new ApiException("Please check the favorite properties." ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
    }
	
	/*
	* validate create
	*/
	public function validateCreate(){
	
		/**
		* Get the bean
		* @var $bean Favorites
		*/
        $bean 			= 	$this->bean;
		$favoritedBy 	= 	R::findOne('favorites', 'fkUsersId = ? and fkMerchantsId = ? ', array($bean->UsersId,$bean->MerchantId));        
		if($favoritedBy){
			return $favoritedBy;
		}
		else{
			if($bean->FavouriteType == 0){
				throw new ApiException("You are not allowed to unfavorite this merchant." ,  ErrorCodeType::FavouriteNotAllowed);//not allowed here to unfav
			}
		}
	}
}