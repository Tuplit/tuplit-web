<?php

require_once '../../lib/Model_Merchants.php';
/**
 * Description of Model_Favorites
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


class Model_Favorites extends RedBean_SimpleModel {

    /**
     * Identifier
     * @var int
     */
    public $id;
	 /**
     * Identifier
     * @var int
     */
	 public $Start;
	  /**
     * Identifier
     * @var int
     */
	 public $Status;

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
         * @var $bean Model_Favorites
         */
        $bean 		= $this->bean;
		
		// validate the parameters
        $this->validate();		
		
		$favoritedBy = R::findOne('favorites', 'fkUsersId = ? and fkMerchantsId = ? ', array($bean->UsersId,$bean->MerchantId));        
		if($favoritedBy){
			if($favoritedBy->FavouriteType == 1 && $bean->FavouriteType == 1){
				throw new ApiException("You have already favorited this merchant." ,  ErrorCodeType::AlreadyFavoured);//already fav
			}
			if($favoritedBy->FavouriteType == 0 && $bean->FavouriteType == 0){
				throw new ApiException("You have already unfavorited this merchant." ,  ErrorCodeType::AlreadyFavoured);//already fav
			}
			if($favoritedBy->FavouriteType == 1 && $bean->FavouriteType == 0){
					$bean->id = $favoritedBy->id;
					$msg = 'You have unfavorited this merchant successfully';
			}
			if($favoritedBy->FavouriteType == 0 && $bean->FavouriteType == 1){
					$bean->id = $favoritedBy->id;
					$msg = 'You have favorited this merchant successfully';
			}
		}
		else{
			if($bean->FavouriteType == 0){
				throw new ApiException("You are not allowed to unfavorite this merchant." ,  ErrorCodeType::FavouriteNotAllowed);//not allowed here to unfav
			}
		}
				
		$bean->FavouriteDate 	= date('Y-m-d H:i:s');		
		$bean->fkMerchantsId = $bean->MerchantId;
		unset($bean->MerchantId);
		$bean->fkUsersId = $bean->UsersId;
		unset($bean->UsersId);
		
		// save the bean to the database		
		$favoriteId = R::store($this);
		
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
         * @var $bean Model_Favorites
         */
        $bean 		= $this->bean;
		
		//getting user favorite merchantIds
		$sql = 'select fkMerchantsId from favorites where FavouriteType = 1  and fkUsersId = '.$bean->UsersId;
		$result = R::getAll($sql);
		$merchantIds = '';
		if($result) {
			foreach($result as $val) {
					$merchantIdsArray[] = $val['fkMerchantsId'];
			}
			$merchantIds 			= implode(',',$merchantIdsArray);
			//$merchant 				= new Model_Merchants();
			$merchant 				= R::dispense('merchants');
			$merchant->Latitude		= $bean->Latitude;
			$merchant->Longitude	= $bean->Longitude;
			$merchant->Start		= $bean->Start;
			$merchantList 	= $merchant->getMerchantList($merchantIds);
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
    public function validate($type='')
    {
		$bean = $this->bean;
		if($type == ''){
			$rules = [
	            'required' => [
	                ['MerchantId'],['UsersId'],['FavouriteType']
	            ],				
				'in' =>[
					['FavouriteType',['0','1']]
				]
	        ];
		} 
        $v = new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
            throw new ApiException("Please check the favorite properties." ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
    }
}