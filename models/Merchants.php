<?php

/**
 * Description of Merchants
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
require_once 'Products.php';
require_once 'Comments.php'; 
require_once 'Orders.php'; 

require_once '../../admin/includes/mangopay/functions.php';
require_once '../../admin/includes/mangopay/config.php';
require_once '../../admin/includes/mangopay/MangoPaySDK/mangoPayApi.inc';


class Merchants extends RedBean_SimpleModel implements ModelBaseInterface {

	/**
	* Merchants merchantIds
	* @var string
	*/
    public $merchantIds;

	/**
	* Person Start
	* @var string
	*/
    public $Start;

	/**
	* Constructor
	*/
    public function __construct() {

    }

    /**
    * Check the Email and Password
    */
    public static function checkLogin($email,$password){
		/**
		* @var Merchants
		*/		
		$result = R::findOne('merchants', 'Email = ? and Password = ? and Status <> ? ', array($email,PasswordHelper::encrypt($password),StatusType::DeleteStatus));
        if (!$result) {
            return false;
        }
        else {
			if($result->Status == 0){
				 throw new ApiException("You are not approved by admin to proceed further. Please wait", ErrorCodeType::MerchantsNotInActiveStatus);
			}
			else if($result->Status != 1){
				// the Merchants was not found
				 throw new ApiException("Merchants not in active state", ErrorCodeType::MerchantsNotInActiveStatus);
			}
			else{
				return $result->id.'##2';
			}
        }
    }
	
	/**
	* Create an merchant account
	* Validation for email
	*/
    public function create(){ 
		 
		/**
		* Get the bean
		* @var $bean Merchants
		*/
        $bean 						= 	$this->bean;
		
		// validate the model
        $this->validate();

        // validate the creation
        $this->validateCreate();
		
        $bean->DateCreated 			= 	date('Y-m-d H:i:s');
        $bean->DateModified 		= 	$bean->DateCreated;
		
		// encrypt the password
		if($bean->Password){
        	$bean->Password 		= 	PasswordHelper::encrypt($bean->Password);
		}
		//save ip address
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else{
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		$bean->IpAddress 			= 	$ip;
		$bean->Status 				= 	0;
		
		// save the bean to the database
		$merchantsId 				= 	R::store($this);
	  	return $merchantsId;
    }

	
    /**
    * Validate the model
    * @throws ApiException if the models fails to validate required fields
    */
    public function validate()
    {
		$bean = $this->bean;		
		$rules = [
			'required' => [
				 ['FirstName'],['LastName'],['Email'],['Password'],['CompanyName']
			],
			'Email' => 'Email',
		];
		
        $v = new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
            throw new ApiException("Please check the merchant's properties. Fill FirstName,LastName,Email,Password,CompanyName with correct values" ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
    }
	
	/**
	* Validate the creation of an account
	*/
    public function validateCreate(){

		/**
		* Get the bean
		*/
        $bean = $this->bean;
		
		/**
		* Email Id must be unique
		*/
        $existingAccount = R::findOne('merchants', 'Email = ? and Status <> ? order by DateModified desc', array($bean->Email,StatusType::DeleteStatus));
        if ($existingAccount) {
            // an account with that email already exists in the system - don't create account
            throw new ApiException("This Email Address is already associated with another merchant account", ErrorCodeType::EmailAlreadyExists);
		}
		
    }
	
	/**
	* @param Modify the user entity
	*/
    public function modify($merchantsId = null,$iconExist='',$merchantExist=''){

		/**
		* Get the bean
		*/
		$bean = $this->bean;
		
		// validate the model
        $this->validateModifyMerchant($iconExist,$merchantExist);
		
        // validate the modification
        $this->validateModify($merchantsId);

        $bean->DateModified = date('Y-m-d H:i:s');
		
		$latlong = $lat = $lng = '';
		$latlong = getLatLngFromAddress($bean->Address) ;
		if($latlong != ''){
			$latlngArray = explode('###',$latlong);
			if(isset($latlngArray) && is_array($latlngArray) && count($latlngArray) > 0){
				if(isset($latlngArray[0]))
					$lat = $latlngArray[0];
				if(isset($latlngArray[1]))
					$lng = $latlngArray[1];
			}
		}
		if($lat != '')
			$bean->Latitude = $lat;
		if($lng != '')
			$bean->Longitude = $lng;
		
        // modify the bean to the database
        R::store($this);
    }

	/**
	* Validate the model
	* @throws ApiException if the models fails to validate required fields
	*/
    public function validateModifyMerchant($iconExist,$merchantExist)
    {
		$bean 		= 	$this->bean;
		$temp 		= 	array(array('CompanyName'),array('Address'),array('PhoneNumber'),array('WebsiteUrl'),array('DiscountTier'),array('PriceRange'));
		if($iconExist == '' && $merchantExist == '' ){
		   $temp[] 	= 	array('IconPhoto');
		   $temp[] 	= 	array('MerchantPhoto');
		}
		else if($iconExist == ''){
			$temp[] = 	array('IconPhoto');
		}
		else if($merchantExist == ''){
			$temp[] = 	array('MerchantPhoto');
		}
			
		$rules = [
	            'required' => $temp,
				'Url' => 'WebsiteUrl',
				'in' =>[
						['DiscountTier',['1','2','3','4','5','6']]
					]
	        ];		
        $v = new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
            throw new ApiException("Please check the merchant's properties. Fill Company Name,Address,Phone Number,WebsiteUrl,Discount Tier,Price Range,Icon Photo,Merchant Photo with correct values" ,  ErrorCodeType::SomeFieldsRequired, $errors);//Email,
        }
    }
		
	/**
	* Validate the modification of an account
	* @throws ApiException if the user being modifying the account 
	*/
	public function validateModify($merchantsId)
    {
		/**
		* Get the bean
		* @var $bean Merchants
		*/
        $bean = $this->bean;

		/**
		* Get the identity of the person making the change
		* @var $modifiedBy Merchants
		*/
		$sql = "select id from merchants where id = '".$merchantsId."' and Status = 1 ";
        $modifiedBy = R::getAll($sql);
        if (!$modifiedBy) {
            // the Merchants was not found
            throw new ApiException("You have no access to edit the data's", ErrorCodeType::NotAccessToDoProcess);
        }
		
		/**
		* Email Id must be unique
		*/
		$existingAccount = R::findOne('merchants', 'Email = ? and id <> ? and Status <> ? order by DateModified desc', array($bean->Email,$merchantsId,StatusType::DeleteStatus));
        if ($existingAccount) {
            // an account with that email already exists in the system - don't modify account
            throw new ApiException("This Email Address is already associated with another merchant account", ErrorCodeType::EmailAlreadyExists);
		} 
    }
	
	/**
	* Get user details
	*/
    public function getMerchantsDetails($merchantId){
		global	$discountTierArray;
		$fields	= $userId = $joinCondition	=  $latitude = $longitude ='';
		$bean 	= $this->bean;
		$userId = $bean->UserId;
		if($bean->From != '')
			$from = $bean->From;
		else
			$from = 1;
		$latitude		=	$bean->Latitude;			
		$longitude		=	$bean->Longitude;
		
		if(!empty($latitude) && !empty($longitude))
			$fields	 = ",(((acos(sin((".$latitude."*pi()/180)) * sin((m.`Latitude`*pi()/180))+cos((".$latitude."*pi()/180)) * cos((m.`Latitude`*pi()/180)) * cos(((".$longitude."- m.`Longitude`)*pi()/180))))*180/pi())*60*1.1515)*1.6093 as distance";
		
		if($from == 1){
			//Validate latitude and longitude	
			$this->validateLatLong();		
		
			//validate merchant
			$this->validateMerchants($merchantId,3);
			
			//Already Favourited
			if(!empty($userId) && !empty($latitude) && !empty($longitude)){
				$this->validateUser($userId);				
				$fields			.= ",FavouriteType as AlreadyFavourited";
				$joinCondition	= "LEFT JOIN favorites as f ON(m.id = f.fkMerchantsId and f.fkMerchantsId = ".$merchantId." and f.fkUsersId = ".$userId." and FavouriteType = 1) ";
 			}			
		}	
		
		// Merchant details		
		$sql 	= "SELECT m.id,m.id as MerchantId,m.FirstName,m.LastName,m.Email,m.CompanyName,m.Address,m.Location,m.Latitude,
				  m.Longitude,m.PhoneNumber,m.WebsiteUrl,m.Icon,m.Image,m.Description,m.ShortDescription,m.MangoPayUniqueId,
				  m.DiscountTier, m.DiscountType, m.DiscountProductId,m.SpecialIcon,m.ItemsSold,m.OpeningHours,m.PriceRange,m.SpecialsSold".$fields." 
				  FROM merchants as m	".$joinCondition."	where m.Status = 1 and m.id=".$merchantId." ";
		$merchants 		= R::getAll($sql);
		$merchants 		= $merchants[0];		
        if (!$merchants) {
            // the Merchants was not found
            throw new ApiException("Your status is not in active state", ErrorCodeType::MerchantsNotInActiveStatus);
        }
		else
		{	
			//condition to check whether we can allow cart to be enabled
			if(isset($merchants['distance']) && !empty($merchants['distance'])) {					
				$result 		= R::findOne('admins', 'id = ? ', array('1'));
				$distanceLimit 	= $result->LocationLimit;
				if($merchants['distance'] > $distanceLimit) 
					$AllowCart = 0;
				else
					$AllowCart = 1;
			}
			
			//AlreadyFavourited  
			if($userId != '' && $from == 1 && $merchants['AlreadyFavourited']  == '')
				$merchants['AlreadyFavourited'] 	= 	'0';
			else
				$merchants['AlreadyFavourited']  	= 	'0';
				 
			//Merchants Discount Tier
		    if($merchants['DiscountTier']  > 0)
				$merchants['DiscountTier'] 			= 	$discountTierArray[$merchants['DiscountTier']].'%';
				
			//Merchant Details
			$merchantsDetails 						= 	$merchants;
		   
			// Merchant OpeningHours		   
			$merchantsDetails['OpeningHours']		= 	$this->getMerchantOpeningHours($merchantId,$from);
		    		
			//Merchant Categories
			$merchantCategories						=	$this->getMerchantCategories($merchantId,$from);
			$merchantsDetails['Category']			=	$merchantCategories['Categoryids'];;
		   		   
		   if($from == 1){
		   		
				//category details
				$merchantsDetails['CategoryList']			=	$merchantCategories['categoryDeatil'];
							
				//merchant Comments
				$comments 									= 	R::dispense('comments');
				$comments->MerchantId						= 	$merchantId;
				$comments->Start 							= 	0;
				$comments->Limit 							= 	3;
				$merchantsDetails['Comments'] 				= 	$comments->getMerchantCommentList();
				
				//Total users shopped
				$totalShopped 								= 	new Orders();
				$totalShoppedList							= 	$totalShopped->getAlreadyShoppedFriends($merchantId,$userId);				
				$merchantsDetails['CustomersCount']			=	$totalShoppedList['OrderCount'];
				$merchantsDetails['OrderedFriendsCount']	=	$totalShoppedList['OrderedFriendsCount'];
				$merchantsDetails['OrderedFriendsList']		=	$totalShoppedList['OrderedFriendsList'];
				
				//Product Details
				$productDetail 								= 	new Products();
				$ProductList								= 	$productDetail->getProductListWithCategory($merchantId,null,1);//1-mobile
				$merchantsDetails['ProductList'] 			=  	$ProductList;
			}					
			$merchantsDetailsOut['merchantDetails'] 		= 	$merchantsDetails;
			if(isset($AllowCart))
				$merchantsDetailsOut['AllowCart'] 			= 	$AllowCart;				
			return $merchantsDetailsOut;
		}
    }
	
	/*
	* Get merchants Opening Hours
	*/
	public function getMerchantOpeningHours($merchantId,$from)
    {
		$sql1 				= 	"select * from merchantshoppinghours where fkMerchantId=".$merchantId;
		$openingHours 		= 	R::getAll($sql1);
		if($from == 1) {			
			$openingHours 	= 	openingHoursStringupdated($openingHours);
		}
		return	$openingHours;
	}
	
	/*
	* Get merchants Categories
	*/
	public function getMerchantCategories($merchantId,$from)
    {
		$categoriesArray	= $categoryDeatil	=	array();
		$sql 				= "select group_concat(`fkCategoriesId` separator ',') as  catId FROM merchantcategories where fkMerchantId=".$merchantId;
		$categories 		= R::getAll($sql);
		$categories			= $categories[0];
		if($categories) {
			if($from == 1) {			
				// Merchant categories for mobile
				if($categories['catId'] != ''){
					$sql 			= "select c.id as CategoryId,CategoryName,CategoryIcon from categories as c where id in (".$categories['catId'].") and Status = 1";
					$categoriesList = R::getAll($sql);
					if($categoriesList){
						foreach($categoriesList as $key => $value){
							$value['CategoryIcon']	= 	CATEGORY_IMAGE_PATH.$value['CategoryIcon'];
							$value['CategoryName']	= 	ucfirst($value['CategoryName']);
							$categoryDeatil[]		= 	$value;
						}
					}
				}
				
				$categoriesArray['Categoryids']		=	$categories['catId'];
				$categoriesArray['categoryDeatil']	=	$categoryDeatil;
				return $categoriesArray;
			} else {
				$categoriesArray['Categoryids']		=	$categories['catId'];
				return $categoriesArray;
			}			
		} else {
			return $categoriesArray;
		}
	}
	
	/**
	* Validate the modification
	*/
	public function validateMerchants($merchantsId,$type='')
    {		
		/**
		* Get the identity of the person requesting the details
		*/
		$sql		 = "select id,FirstName,LastName,Email,Status from merchants where id = '".$merchantsId."' and Status != 3";
		$merchants	 = R::getAll($sql);
		
        if (!$merchants) {
			if($type == 3){				
				// the Merchants was not found
				throw new ApiException("The Merchant you requested was not in active state.", ErrorCodeType::NotAccessToDoProcess);
			}
			else {
				// the Merchants was not found
				throw new ApiException("Your status is not in active state", ErrorCodeType::MerchantsNotInActiveStatus);
			}
        }
		else{
			if($type == 1){
				if($merchants[0]['Status'] == 0){
					// the Merchants was not found
	            	throw new ApiException("Sorry! you cannot do this process", ErrorCodeType::NotAccessToDoProcess);
				}
			}
			if($merchants[0]['Status'] == 2){
				// the Merchants was not found
            	throw new ApiException("Your status is not in active state", ErrorCodeType::MerchantsNotInActiveStatus);
			}
			return $merchants;
		}
    }
	
	/**
	* Check Reset Password
	* @param checking reset password 
	*/
	public function checkResetPassword($merchantsId){	 
			
			//validate param
			$this->validateMerchants($merchantsId,1);
			
			$sql 		 		= 	" select id,ResetPassword from merchants where id = '".$merchantsId."' and Status = 1";
			$merchantDetails 	= 	R::getAll($sql);
			if($merchantDetails){
				if($merchantDetails[0]['ResetPassword'] == 0){
					throw new ApiException("Sorry! You cannot use this link again" ,  ErrorCodeType::PasswordResetNotValid);
				}
			}			
			return $merchantDetails;
	 }
	 
	/**
    * Check forgot Password Email
    * @param checking email
    */
	public function validateForgotEmail($email){	 
		$sql 		 	 = " select id,FirstName,LastName,ResetPassword,Status,Email from merchants where email = '".$email."' order by id desc";
		$merchantDetails = R::getAll($sql);
		if(!$merchantDetails) {
			/**
			* No Merchant registered with email address
			*/
            throw new ApiException("No Merchant registered with this Email", ErrorCodeType::NoEmailExists);
        }
        else if($merchantDetails[0]['Status'] != StatusType::ActiveStatus)
        {
			/**
			* Only active user can request for request forgetpassword for this account  
			*/
			throw new ApiException("You are not authorized to request forgot password for this account", ErrorCodeType::NoAuthoriseToRequestForPassword);
		}
		return $merchantDetails;
	}
	 
	/**
    * Update Password
    * @param updating password by using word table from database
    */
	public function updatePassword(){
			
		/**
		* Get the bean
		* @var $bean Merchants
		*/
		$bean 			= 	$this->bean;
		$merchantId 	= 	$bean->MerchantId;
		
		//validate param
		$this->validateMerchantsPassword(2);
		if(isset($bean->OldPassword)){
			$result = R::findOne('merchants', 'Password = ? and id=? ', array(PasswordHelper::encrypt($bean->OldPassword),$merchantId));
			if (!$result) {
				// Old password 
				throw new ApiException("Please enter valid Old Password", ErrorCodeType::EmailAlreadyExists);
			}
			unset($bean->OldPassword);
		}
		else{
			//validate merchant
			$this->checkResetPassword($merchantId);
		}
		unset($bean->MerchantId);
		
		// encrypt the password
		$bean->id				= $merchantId;
		$bean->Password			= PasswordHelper::encrypt($bean->Password);
		$bean->ResetPassword	= 0;
		
		// save the bean to the user table
		$merchantsId 			= R::store($this);
		
		return $merchantsId;
	}
	 
	/**
	* forgot Password
	*/
	public function forgotPassword(){
		/**
		* Get the bean
		*/
		$bean = $this->bean;
		
		//validate param
		$this->validateMerchantsPassword(1);
		
		//validate Email
		$merchantDetails 	= 	$this->validateForgotEmail($bean->Email);
		
		$merchantsId 		= 	'';
		if($merchantDetails){
			$merchantsId 	= 	$merchantDetails[0]['id'];
			$bean->id    	= 	$merchantsId;
			$bean->ResetPassword = 1;
			R::store($this);
			return $merchantDetails;
		}
	}
	
	/**
    * Validate the fields  for ResetPassword
    * @throws ApiException if the models fails to validate
    */
    public function validateMerchantsPassword($type = '')
    {
		if($type == 1){
			$rules = [
		            'required' => [
		               ['Email']
		            ]
		        ];
			$string = "forgot";
		}
		else{
			$rules = [
		            'required' => [
		               ['MerchantId'],['Password']
		            ]
		        ];
			$string = "reset";
		}
        $v = new Validator($this->bean);
        $v->rules($rules);
		if (!$v->validate()) {
	            $errors = $v->errors();
	            throw new ApiException("Please check the ".$string." password" ,  ErrorCodeType::SomeFieldsRequired, $errors);
	      }
    }
	
	/**
    * @param Update settings
    */
    public function updateSettings($merchantsId){

		/**
		* Get the bean
		*/
		$bean = $this->bean;
		
		//validate param
		$this->validateSettingsParams();
		
		// validate the modification
        $this->validateSettings($merchantsId);
		$merchants 						= R::dispense('merchants');
		$merchants->id 					= $merchantsId;
		$merchants->PushNotification 	= $bean->Action;
		$merchantsUpdate 				= R::store($merchants);
	}
	
	/**
    * @throws ApiException if the Login user not in active state 
    */
    public function validateSettings($merchantsId)
    {
		/**
		* Get the bean
		* @var $bean Merchants
		*/
        $bean 		= 	$this->bean;
		
		$merchants 	= 	R::findOne('merchants', 'id = ? and Status = ?', [$merchantsId,StatusType::ActiveStatus]);
        if (!$merchants) {
            // the Merchants was not found
            throw new ApiException("Your status is not in active state", ErrorCodeType::MerchantsNotInActiveStatus);
        }
	}

	/**
    * Validate the fields (Action,Type)
    * @throws ApiException if the models fails to validate
    */
    public function validateSettingsParams()
    {
		$rules = [
            'required' => [
               ['Action']
            ],
        ];

        $v = new Validator($this->bean);
        $v->rules($rules);

        if (!$v->validate()) {
            $errors = $v->errors();
            throw new ApiException("Please Action" ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
		$bean  = $this->bean;
		if($bean->Action != '1' && $bean->Action != '0'){
			// the action was not found
            throw new ApiException("Settings Action is not valid", ErrorCodeType::ErrorInSettingTypeOrSettingAction);
		}
    }
	

	/**
    * @param Get merchant list
	* Merchant listing
    */
	public function getMerchantList($merchantIds='')
    {		
		global $discountTierArray;
		$condition	= $searchCondition	= $category	 ='';

		/**
		* Get the bean
		*/
		$bean 			= $this->bean;
		$latitude		=	$bean->Latitude;			
		$longitude		=	$bean->Longitude;
		
		//Validate latitude and longitude
		$this->validateLatLong();
		
		if($bean->Start)
			$Start 		= 	$bean->Start;
		else
			$Start 		= 	0;
		
		if(isset($bean->Category))
			$category	=	$bean->Category;
			
		if($bean->Type)
			$type		=	$bean->Type;
		else
			$type 		= 	0;
			
		if(isset($bean->DiscountTier) && $bean->DiscountTier != ''){
			$tierVal	=	$bean->DiscountTier;
			
		//Validate discount tier
		$this->validateDiscountTier($tierVal);
		if($tierVal > 0)
			$searchCondition	.=	" and m.DiscountTier = ".$tierVal." ";
		}
		
		/**
		* Query to get merchant details
		*/
		if($bean->SearchKey != '')
			$searchCondition	.=	" and (m.CompanyName like '%".$bean->SearchKey."%' or  m.ShortDescription like '%".$bean->SearchKey."%')";
			
		if($category != ''){
			$fields				 =	" m.id,m.id as MerchantId,DiscountType,m.Icon,m.Image,m.DiscountTier,m.CompanyName,m.ShortDescription,m.Address,mc.fkCategoriesId,m.ItemsSold,m.Latitude,m.Longitude,DiscountProductId";
			$join_condition 	 = 	" LEFT JOIN  merchantcategories as mc ON (mc.fkMerchantId = m.id) ";
			$searchCondition	.=	" and mc.fkCategoriesId ='".$category."'";
		}
		else{
			$fields				=	"m.id,m.id as MerchantId,DiscountType,m.Icon,m.Image,m.DiscountTier,m.CompanyName,m.ShortDescription,m.Address,m.ItemsSold,m.Latitude,m.Longitude,DiscountType,DiscountProductId";
			$join_condition		= 	"";
		}
		
		if($type == 2 )
			$orderby			=	"m.ItemsSold desc";
		 else
			$orderby			=	"distance asc";
		
		if(!empty($merchantIds)) { 
			$condition 			= 	" and m.id in (".$merchantIds.")";
		}
		
		$sql 	= "SELECT SQL_CALC_FOUND_ROWS ".$fields.",(((acos(sin((".$latitude."*pi()/180)) * sin((m.`Latitude`*pi()/180))+cos((".$latitude."*pi()/180)) * cos((m.`Latitude`*pi()/180)) * cos(((".$longitude."- m.`Longitude`)*pi()/180))))*180/pi())*60*1.1515)*1.6093 as distance  from merchants as m ".$join_condition." where m.Status = 1 ".$searchCondition." ".$condition." and m.Address != '' and m.Image != '' ORDER BY ".$orderby." limit $Start,10";	
		$result 			= 	R::getAll($sql);
		$totalRec 			= 	R::getAll('SELECT FOUND_ROWS() as count ');
		$total 				= 	(integer)$totalRec[0]['count'];
		$listedCount		= 	count($result);
		$countProducts 		= 	array();
		$discountedProduct 	= 	0;
		
		if($result){
			foreach($result as $key=>$value)
			{
				$totalProduct 				= 	0;
				$merchantid					= 	$value['MerchantId'];
				
				//Product Details
				$productDetail 				= 	new Products();
				$countResult				= 	$productDetail->getProductsDiscounttype($merchantid);				
				foreach($countResult as $kk=>$vv)
				{
					if($vv['DiscountApplied'] == 1){
						$discountedProduct 	= 	$vv['productCount'];
						$totalProduct 		= 	$totalProduct + $vv['productCount'];
					}
					else{
						$totalProduct 		= 	$totalProduct + $vv['productCount'];
					}
					$countProducts[$merchantid]['TotalCount'] 		= 	$totalProduct;
					$countProducts[$merchantid]['DiscountApplied'] 	= 	$discountedProduct;
				}
			}
			foreach($result as $key=>$value)
			{
				$imagePath = $iconPath	= 	'' ;
				$iconPath  				= 	MERCHANT_ICONS_IMAGE_PATH.$value['Icon'];
				$imagePath 				= 	MERCHANT_IMAGE_PATH.$value['Image'];
				$value['Image'] 		= 	$imagePath;
				$value['Icon'] 			= 	$iconPath;
				$value['IsSpecial'] 	= 	"0";
				$value['IsGoldenTag'] 	= 	"0";
				$discountFlag 			= 	0;
				if($value['DiscountType'] == 1)
				{
					if(isset($value['DiscountProductId']) && $value['DiscountProductId'] == 'all')
						$discountFlag 	= 	1;
					else{
						$tot_product_ids = $this->validateMerchantProduct($value['DiscountProductId']);
						if(isset($countProducts[$value['MerchantId']]) && is_array($countProducts[$value['MerchantId']]) && count($countProducts[$value['MerchantId']]) > 0 ){
							if($tot_product_ids >= $countProducts[$value['MerchantId']]['DiscountApplied'] ){
								$totalProduct 	= 	$tot_product_ids;
								$disapplied	  	= 	$countProducts[$value['MerchantId']]['DiscountApplied'];
								$totaldiscount 	= 	($disapplied/$totalProduct)*100;
								if($totaldiscount >= 75)
									$value['IsSpecial'] 	= "1";
								if($disapplied == $totalProduct)
									$value['IsGoldenTag'] 	= "1";
							}
						}
					}
				}
				else
					$discountFlag 			= 	1;
				if($discountFlag == 1){
					if(isset($countProducts[$value['MerchantId']]) && is_array($countProducts[$value['MerchantId']]) && count($countProducts[$value['MerchantId']]) > 0 ){
				  		$totalProduct 		= 	$countProducts[$value['MerchantId']]['TotalCount'];
						$disapplied	  		= 	$countProducts[$value['MerchantId']]['DiscountApplied'];
						$totaldiscount 		= 	($disapplied/$totalProduct)*100;
						if($totaldiscount >= 75)
							$value['IsSpecial'] 	= "1";
						if($disapplied == $totalProduct)
							$value['IsGoldenTag'] 	= "1";
				     }
			    }
				if($value['DiscountTier']  != '')
					$value['DiscountTier'] 	= $discountTierArray[$value['DiscountTier']].'%';
				$MerchantListArray[] 		= $value;
			}
			$MerchantArray['totalCount']	= $total;
			$MerchantArray['listedCount']	= $listedCount;
			$MerchantArray['result']		= $MerchantListArray;
			return $MerchantArray;
		}
		else{
			/**
			* throwing error when no data found
			*/
			throw new ApiException("No Merchants Found", ErrorCodeType::NoResultFound);
		}
	}
	
	/**
    * Validate the latitude and longitude  for Merchant List
    * @throws ApiException if the models fails to validate
    */
	public function validateLatLong()
    {		
		$bean = $this->bean; 			
		$rules = [
			'required' => [
				 ['Latitude'],['Longitude']
			],
			
		];	
	
        $v = new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
			// the action was not found
            throw new ApiException("Please check the merchant's properties. Fill Latitude,Longitude with correct values" ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
    }
	
	/**
    * Validate the fields  for Merchant List
    * @throws ApiException if the models fails to validate
    */
	public function validateDiscountTier($tierVal='')
    {
		global $discountTierArray;
		$discountArray 	= 	explode(',',$tierVal);		
		$discountId  	= 	'';
		foreach($discountTierArray as $key=>$value){
			$discountId .= $key.',';
		}
		if($discountId != ''){
			$discountId 	= 	trim($discountId,',');
			$globalDiscount = 	explode(',',$discountId);
			foreach($discountArray as $dkey){
				if(!in_array($dkey,$globalDiscount)){
					// the action was not found
					throw new ApiException("Invalid DiscountTier" ,  ErrorCodeType::SomeFieldsRequired);
				}
			}
		}
    }
	
	/**
    * Validate the modification
    */
	public function validateUser($userId)
    {
		/**
		* Get the identity of the person requesting the details
		*/
		$requestedBy = R::findOne('users', 'id = ? and Status = ?', [$userId,StatusType::ActiveStatus]);
		
        if (!$requestedBy) {
            // the User was not found
            throw new ApiException("Your status is not in active state", ErrorCodeType::UserNotInActiveStatus);
        }
    }
	
	/**
    * Check Location
    */
    public function checkLocation(){

		/**
		* Get the bean
		* @var $bean Merchants
		*/
		$bean 				= 	$this->bean;
		
		//validate param
		$this->validatecheckLocationParams();
		
		$result 			= 	R::findOne('merchants', 'id = ? ', array($bean->MerchantId));	
		if($result) {
			// User Distance from latitude & longitude
			$distance 		= 	(((acos(sin(($bean->Latitude*pi()/180)) * sin(($result->Latitude*pi()/180))+cos(($bean->Latitude*pi()/180)) * cos(($result->Latitude*pi()/180)) * cos((($bean->Longitude- $result->Longitude)*pi()/180))))*180/pi())*60*1.1515)*1.6093;
			
			//Admin Distance limit			
			$adminResult 	=	 R::findOne('admins', 'id = ? ', array('1'));	
			$distanceLimit 	= 	$adminResult->LocationLimit;
			
			//check for user in the location
			if($distance > $distanceLimit) 
				$AllowCart 	= 	0;
			else
				$AllowCart 	= 	1;
				
			$AllowCartArray['AllowCart'] = $AllowCart;			
			return $AllowCartArray;
		}
		else {
			/**
			* throwing error when no data found
			*/
			throw new ApiException("No merchants Found", ErrorCodeType::NoResultFound);
		}
	}
	
	/**
    * Validate checkLocation params
    * @throws ApiException if the models fails to validate
    */
	public function validatecheckLocationParams()
    {
		$bean = $this->bean;
	  	$rules = [
            'required' => [
                 ['Latitude'],['Longitude'],['MerchantId']
            ],
			
        ];
		
        $v = new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
			// the action was not found
            throw new ApiException("Please check the search properties. Fill MerchantId,Latitude,Longitude with correct values" ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
    }
	
	/**
    * Validate the product ids from merchant 
    */
	public function validateMerchantProduct($productIds)
    {
        /**
        * Get the identity of the person requesting the details
        */
		$product = R::find('products', 'id = ? and Status = ?', [$productIds,StatusType::ActiveStatus]);
		return count($product);
    }
	
	/**
    * get Customer list
    */
	public function getCustomerList($merchantId)
    {
		/**
        * Query to get customer list
        */
		$d 			=	 0;
		$condition 	=  	 $having_condition = $userName = $visitCount = $totalSpend ='';
		$bean 		=	 $this->bean;
		
		$this->validateMerchants($merchantId,1);
		if($bean->Type == 1){
			$start 	= 	0;
			$limit	= 	5;
			$orderby = 'TotalOrders desc,o.OrderDate desc';
		}
		else{
			$orderby = 'LastVisit  desc';
			if(isset($bean->UserName))			$userName		=	$bean->UserName;
			if(isset($bean->TotalOrders))		$visitCount		=	$bean->TotalOrders;
			if(isset($bean->TotalPrice))		$totalSpend		=	$bean->TotalPrice;
			if($bean->Start)					$start 			= 	$bean->Start;
			else
				$start = 0;
			if($bean->Limit)					$limit 			= 	$bean->Limit;
			else
				$limit = 10;
			if(isset($bean->FromDate))			$fromDate		=	$bean->FromDate;
			if(isset($bean->ToDate))			$toDate			=	$bean->ToDate;
		}
		if($userName != '')
			$condition  		.= 	' and (u.FirstName LIKE "%'.$userName.'%" or u.LastName LIKE "%'.$userName.'%")';
		if(isset($fromDate) && $fromDate != ''	&&	isset($toDate) && $toDate != ''){
			$condition .= " AND ((date(OrderDate) >=  '".date('Y-m-d',strtotime($fromDate))."' and date(OrderDate) <= '".date('Y-m-d',strtotime($toDate))."') ) ";
		}
		else if(isset($fromDate) && $fromDate != '')
			$condition .= " AND date(OrderDate) >=  '".date('Y-m-d',strtotime($fromDate))."'";
		else if(isset($toDate) && $toDate != '')
			$condition .= " AND date(OrderDate) <=  '".date('Y-m-d',strtotime($toDate))."'";
		if($visitCount != '' && $totalSpend != '')
			$having_condition  	.= 	' Having TotalOrders = '.$visitCount.'  and  TotalPrice = '.$totalSpend.' ';
		else if($visitCount != '')
			$having_condition  	.= 	' Having TotalOrders = '.$visitCount.' ';
		else if($totalSpend != '')
			$having_condition  	.= 	' Having TotalPrice = '.$totalSpend.' ';	
		
		$sql 			= 	"SELECT SQL_CALC_FOUND_ROWS u.id as userId,concat(u.FirstName,' ',u.LastName)as UserName,u.Photo,MAX(o.OrderDate) as LastVisit,
							MIN(o.OrderDate) as FirstVisit,COUNT(o.id) as TotalOrders,SUM(TotalPrice) as TotalPrice,c.id as Comments from orders as o 
							LEFT JOIN users as u ON(u.id = o.fkUsersId) 
							Left JOIN comments as c	on(u.id	= c.fkUsersId)
							where 1 ".$condition." and o.OrderStatus IN(0,1,2) and u.Status =1  and o.fkMerchantsId = ".$merchantId." 
							GROUP BY u.id ".$having_condition." order by ".$orderby." limit $start,$limit";	
							//echo '-->'. $sql .'<br>';
							
		$CustomerList 	=	R::getAll($sql);
		$totalRec 		= 	R::getAll('SELECT FOUND_ROWS() as count ');
		
		$total 			= 	(integer)$totalRec[0]['count'];
		$listedCount	= 	count($CustomerList);
		if($CustomerList){
			foreach($CustomerList as $key=>$value){
				$imagePath 		= $originalPath = '';
				if($value["Photo"] !=''){
					if(SERVER){
						$imagePath 		= USER_THUMB_IMAGE_PATH.$value["Photo"];
						$originalPath 	= USER_IMAGE_PATH.$value["Photo"];
					}
					else{
						if(file_exists(USER_THUMB_IMAGE_PATH_REL.$value["Photo"]))
							$imagePath = USER_THUMB_IMAGE_PATH.$value["Photo"];
						if(file_exists(USER_IMAGE_PATH_REL.$value["Photo"]))
							$originalPath = USER_IMAGE_PATH.$value["Photo"];
					}
				}
				$totalOrders					=	$value["TotalOrders"];
				$totalPrice						=	$value["TotalPrice"];
				$averagePrice					=	$totalPrice/$totalOrders;
				$value["AverageSpend"]			=	round($averagePrice,2);
				$diff 							= 	abs(strtotime($value["LastVisit"])-strtotime($value["FirstVisit"]));
				$year							=	round($diff/(365*24*60*60));
				$month							=	round(($diff-($year*365*24*60*60))/(30*24*60*60));
				$dates							=	round(($diff-($year*365*24*60*60)-($month*30*24*60*60))/(24*60*60));
				$d								=	abs($dates);
				$value["DayDifference"]			=	$d;//$date_diff->format('%d');
				$value["Photo"]					=	$imagePath;
				$value["OriginalPhoto"]			=	$originalPath;
				$CustomerListArray[] 			= 	$value;
			}
			
			$CustomerListArray['result'] 		= 	$CustomerListArray;
			$CustomerListArray['totalCount']	= 	$total;
			$CustomerListArray['listedCount']	= 	$listedCount;
			return $CustomerListArray;
		}
		else{
			/**
	        * throwing error when no data found
	        */
			throw new ApiException("No customers found", ErrorCodeType::NoResultFound);
		}
	}	
	/*
	* Add MangoPay Details
	*/
	public function addMangoPayDetails($merchantsId)
    {
		$bean 			=	 $this->bean;
		$merchantArray 	= array();
		
		//Validate MangoPay params
		$this->validateMangoPay();
		$merchantArray['CompanyName']	=	$bean->CompanyName;
		$merchantArray['FirstName']		=	$bean->FirstName;
		$merchantArray['LastName']		=	$bean->LastName;
		$merchantArray['Email']			=	$bean->Email;
		$merchantArray['Address']		=	$bean->Address;
		$merchantArray['Country']		=	$bean->Country;
		$merchantArray['Currency']		=	$bean->Currency;
		$merchantArray['Birthday']		=	$bean->Birthday;
		$mangopayDetails 				=   merchantRegister($merchantArray);

		if(isset($mangopayDetails) && count($mangopayDetails)>0 ){
			$uniqueId								=	$mangopayDetails->Id;
			$walletId								=	createWallet($uniqueId,$merchantArray['Currency']);
			$merchants 								= 	R::dispense('merchants');
			$merchants->id 							= 	$merchantsId;
			$merchants->MangoPayTransactionDetails 	= 	serialize($mangopayDetails);
			$merchants->MangoPayUniqueId 			= 	$uniqueId;
			$merchants->WalletId 					= 	$walletId;
			$merchantsUpdate 						= 	R::store($merchants);
			return $merchantsId;
		}
	}
	/**
    * Validate MangoPay params
    * @throws ApiException if the models fails to validate
    */
	public function validateMangoPay()
    {
		$bean = $this->bean;
	  	$rules = [
            'required' => [
                 ['CompanyName'],['FirstName'],['LastName'],['Email'],['Address'],['Country'],['Currency'],['Birthday']
            ],
			
        ];
		
        $v = new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
			// the action was not found
            throw new ApiException("Please check the mango pay properties. Fill CompanyName,FirstName,LastName,Email,Address,Country,Currency,Birthday with correct values" ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
    }
}
