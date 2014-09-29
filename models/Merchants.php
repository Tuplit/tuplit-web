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
		if($password && $password  !=''){
			$result = R::findOne('merchants', 'Email = ? and Password = ? and Status <> ? ', array($email,PasswordHelper::encrypt($password),StatusType::DeleteStatus));
		}
		else
			$result = R::findOne('merchants', 'Email = ? and Status <> ? ', array($email,StatusType::DeleteStatus));
			
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
		
		if(isset($bean->Address) && !empty($bean->Address)) {
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
			$bean->Latitude = $lat;
			$bean->Longitude = $lng;
		}
		
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
				 ['FirstName'],['LastName'],['Email'],['PhoneNumber'],['BusinessName'],['BusinessType'],['CompanyName'],['RegisterCompanyNumber'],['Address'],['Country'],['PostCode'],['Password'],['HowHeared']
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
		
		if(isset($bean->Address) && !empty($bean->Address)) {
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
			//if($lat != '')
				$bean->Latitude = $lat;
			//if($lng != '')
				$bean->Longitude = $lng;
		}
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
		$temp 		= 	array(
								array('CompanyName'),
								array('Email'),
								array('ShortDescription'),
								array('Description'),
								array('Street'),
								array('PhoneNumber'),
								array('WebsiteUrl'),
								array('DiscountTier'),
								array('City'),
								array('PriceRange'),
								array('State'),
								array('PostCode'),
								array('LocationCountry'),
								array('PriceRange')
							);
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
	* @param Modify the user entity
	*/
    public function modifySettings(){

		/**
		* Get the bean
		*/
		$bean = $this->bean;
	
		// validate the model
        $this->validateModifyParams();
		
        // validate the modification
        $this->validateModify($bean->id);

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
    public function validateModifyParams()
    {
		$bean 		= 	$this->bean;
		$temp 		= 	array(array('FirstName'),
							array('LastName'),
							array('Email'),
							array('PhoneNumber'),
							array('BusinessName'),
							array('BusinessType'),
							array('Currency'),
							array('Address'),
							array('CompanyName'),
							array('RegisterCompanyNumber'),
							array('Country'),
							array('PostCode'),
							array('DiscountTier'),
							array('DiscountType'),
							);
		if(isset($bean->DiscountType) && $bean->DiscountType == 1){
		   $temp[] 	= 	array('DiscountProductId');
		}
		$rules = [
	            'required' => $temp,
				'in' =>[
						['DiscountTier',['1','2','3','4','5','6']]
					]
	        ];		
        $v = new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
            throw new ApiException("Please check the merchant's properties. Fill fields with correct values" ,  ErrorCodeType::SomeFieldsRequired, $errors);//Email,
        }
    }
	
	
	
	
	/**
	* Get user details
	*/
    public function getMerchantsDetails($merchantId){
		
		global	$discountTierArray;
		$fields	= $userId = $joinCondition	=  $latitude = $longitude ='';
		$alreadyFavourited	= $AllowCart = $totalProduct = $discountedProduct = 0;
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
		$sql 	= "SELECT m.*,m.id as MerchantId".$fields." 
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
			if(!empty($latitude) && !empty($longitude)){
				if($merchants['distance'] != '') {					
					$result 		= R::findOne('admins', 'id = ? ', array('1'));
					$distanceLimit 	= $result->LocationLimit;
					if($merchants['distance'] > $distanceLimit) 
						$AllowCart = 0;
					else
						$AllowCart = 1;
				}
			}	
			//AlreadyFavourited  
			if($userId != '' && $from == 1 && $merchants['AlreadyFavourited']  == '')
				$merchants['AlreadyFavourited'] 	= 	'0';
			if(!isset($merchants['AlreadyFavourited'] ))
				$merchants['AlreadyFavourited'] 	= 	'0';
			/*else
				$merchants['AlreadyFavourited']  	= 	'';*/
				 
			//Merchants Discount Tier
		    if($merchants['DiscountTier']  > 0)
				$merchants['DiscountTier'] 			= 	$discountTierArray[$merchants['DiscountTier']].'%';
			
			//Total Special Products scold
			$specialScoldobj						= 	R::dispense('orders');
			$totalSpecialScold						=	$specialScoldobj->getSpecialOrdersCount($merchantId);
			if(!empty($totalSpecialScold) && $totalSpecialScold > 0)
				$merchants['SpecialsSold'] 			= 	$totalSpecialScold;
			else
				$merchants['SpecialsSold'] 			= 	'0';
			
			//Merchant Details
			$merchantsDetails 						= 	$merchants;
		   
			// Merchant OpeningHours		   
			$merchantsDetails['OpeningHours']		= 	$this->getMerchantOpeningHours($merchantId,$from);
			
			// Merchant slideshow		   
			$merchantsDetails['slideshow']			= 	$this->getMerchantSlideshowImages($merchantId);

			//Merchant Categories
			$merchantCategories						=	$this->getMerchantCategories($merchantId,$from);
			$merchantsDetails['Category']			=	$merchantCategories['Categoryids'];
			$productDetail 							= 	R::dispense('products');
			$countResult							= 	$productDetail->getProductsDiscounttype($merchantId);	
			foreach($countResult as $kk=>$vv)
			{
				if($vv['DiscountApplied'] == 1){
					$discountedProduct 	= 	$vv['productCount'];
					$totalProduct 		= 	$totalProduct + $vv['productCount'];
				}
				else{
					$totalProduct 		= 	$totalProduct + $vv['productCount'];
				}
				$countProducts['TotalCount'] 		= 	$totalProduct;
				$countProducts['DiscountApplied'] 	= 	$discountedProduct;
			}

			/*$merchantsDetails['IsGoldenTag'] 		= 	"0";
			$discountFlag 			= 	0;
		   	if($merchants['DiscountType'] == 1)
			{
				if(isset($merchants['DiscountProductId']) && $merchants['DiscountProductId'] == 'all')
					$discountFlag 	= 	1;
				else{
					$tot_product_ids = $this->validateMerchantProduct($merchants['DiscountProductId']);
					if(isset($countProducts) && is_array($countProducts) && count($countProducts) > 0 ){
						if($tot_product_ids >= $countProducts['DiscountApplied'] ){
							$totalProduct 	= 	$tot_product_ids;
							$disapplied	  	= 	$countProducts['DiscountApplied'];
							if($disapplied == $totalProduct)
								$merchantsDetails['IsGoldenTag'] 	= "1";
						}
					}
				}
			}
			else
				$discountFlag 			= 	1;
			if($discountFlag == 1){
				if(isset($countProducts) && is_array($countProducts) && count($countProducts) > 0 ){
			  		$totalProduct 		= 	$countProducts['TotalCount'];
					$disapplied	  		= 	$countProducts['DiscountApplied'];
					if($disapplied == $totalProduct)
						$merchantsDetails['IsGoldenTag'] 	= "1";
			     }
		    }	   */
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
				$totalShopped 								= 	R::dispense('orders');
				$totalShoppedList							= 	$totalShopped->getAlreadyShoppedFriends($merchantId,$userId);				
				$merchantsDetails['CustomersCount']			=	$totalShoppedList['OrderCount'];
				$merchantsDetails['OrderedFriendsCount']	=	$totalShoppedList['OrderedFriendsCount'];
				$merchantsDetails['OrderedFriendsList']		=	$totalShoppedList['OrderedFriendsList'];
								
				//Product Details
				$productDetail 								= 	R::dispense('products');
				$ProductList								= 	$productDetail->getProductListWithCategory($merchantId,null,1);//1-mobile
				$merchantsDetails['ProductList'] 			=  	$ProductList;

			}	
			//Tag Type
			$merchantsDetails['TagType'] 					=  	$this->getTagType($merchantId);
			
			$merchantsDetailsOut['merchantDetails'] 		= 	$merchantsDetails;
			//if(isset($AllowCart))
			$merchantsDetailsOut['AllowCart'] 				= 	$AllowCart;				
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
			$openingHours 	= 	openingHoursStringupdated($openingHours,1);
		}
		return	$openingHours;
	}
	
	/*
	* Get merchants Slideshow images
	*/
	public function getMerchantSlideshowImages($merchantId)
    {
		$sql1 				= 	"select * from merchantslideshow where fkMerchantId=".$merchantId;
		$slideshowImages	= 	R::getAll($sql1);
		$slideshowArray		=	array();
		if($slideshowImages) {
			foreach($slideshowImages as $val) {
				$slideshowArray[]	=	MERCHANT_IMAGE_PATH.$merchantId.'/'.$val['SlideshowName'];
			}
		}
		return	$slideshowArray;
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
		$sql 		 	 = " select id,FirstName,LastName,ResetPassword,Status,Email,OrderMail from merchants where email = '".$email."' order by id desc";
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
		$condition	= $searchCondition	= $category	 = $group_condition	= '';
		
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
			$fields				 =	" m.id,m.id as MerchantId,DiscountType,m.Icon,m.Image,m.DiscountTier,m.CompanyName,m.ShortDescription,m.Address,mc.fkCategoriesId as Category,m.ItemsSold,m.Latitude,m.Longitude,DiscountProductId";
			$join_condition 	 = 	" LEFT JOIN  merchantcategories as mc ON (mc.fkMerchantId = m.id) ";
			if($type == 2){
			$fields				 .= ',count(DISTINCT(o.fkUsersId)) as TotalUsersShopped';
			$join_condition 	.= " left join  orders as o ON (o.fkMerchantsId = m.id) ";
			}
			$searchCondition	.=	" and mc.fkCategoriesId ='".$category."'";
		}
		else{
			$fields				=	"m.id,m.id as MerchantId,DiscountType,m.Icon,m.Image,m.DiscountTier,m.CompanyName,m.ShortDescription,m.Address,m.ItemsSold,m.Latitude,m.Longitude,DiscountType,DiscountProductId, group_concat(DISTINCT(mc.fkCategoriesId)) as Category,count(DISTINCT(o.fkUsersId)) as TotalUsersShopped";
			$join_condition 	= 	" LEFT JOIN  merchantcategories as mc ON (mc.fkMerchantId = m.id) ";
			$join_condition 	.= " left join  orders as o ON (o.fkMerchantsId = m.id) ";
		}
		if($type == 2){
			$fields				 .=	",count(o.id) as OrderCount";
		}
		if($type == 2 )
			$orderby			=	"orderCount desc";
		 else
			$orderby			=	"distance asc";
		
		if(!empty($merchantIds)) { 
			$condition 			= 	" and m.id in (".$merchantIds.")";
		}
		$group_condition	  =  " group by m.id";
		$sql 	= "SELECT SQL_CALC_FOUND_ROWS ".$fields.",date(m.DateCreated) as DateCreated,(((acos(sin((".$latitude."*pi()/180)) * sin((m.`Latitude`*pi()/180))+cos((".$latitude."*pi()/180)) * cos((m.`Latitude`*pi()/180)) * cos(((".$longitude."- m.`Longitude`)*pi()/180))))*180/pi())*60*1.1515)*1.6093 as distance  from merchants as m ".$join_condition." where  m.Status = 1 and m.UserType = 1 ".$searchCondition." ".$condition." and m.Address != '' and m.Image != '' ".$group_condition." ORDER BY ".$orderby." limit $Start,10";	
		//if($_SERVER['REMOTE_ADDR'] == '172.21.4.130')
			//echo $sql;
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
				$productDetail 				= 	R::dispense('products');
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
				//$value['IsSpecial'] 	= 	$value['IsGoldenTag'] 	= 	$discountFlag 	= 	0;
				
				//new tag for merchant
				$before30days			=	date('Y-m-d', strtotime('-30 days'));
				$DateCreated			=	$value['DateCreated'];
				if($DateCreated != '0000-00-00' && $DateCreated >= $before30days)
					$value['NewTag'] 	= 	'1';
				else
					$value['NewTag'] 	= 	'0';
				unset($value['DateCreated']);
					
				/*if($value['DiscountType'] == 1)
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
			    }*/
				if($value['DiscountTier']  != '')
					$value['DiscountTier'] 	= $discountTierArray[$value['DiscountTier']].'%';
				
				$value['TagType']			=	$this->getTagType($value['id']);
				$MerchantListArray[] 		= 	$value;
			}
			/*if($_SERVER['REMOTE_ADDR'] == '172.21.4.130')
				echo "<pre>"; echo print_r($result); echo "</pre>";*/
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
		$message = '';
		$result 			= 	R::findOne('merchants', 'id = ? ', array($bean->MerchantId));	
		if($result) {
			// User Distance from latitude & longitude
			$distance 		= 	(((acos(sin(($bean->Latitude*pi()/180)) * sin(($result->Latitude*pi()/180))+cos(($bean->Latitude*pi()/180)) * cos(($result->Latitude*pi()/180)) * cos((($bean->Longitude- $result->Longitude)*pi()/180))))*180/pi())*60*1.1515)*1.6093;
			
			//Admin Distance limit			
			$adminResult 	=	 R::findOne('admins', 'id = ? ', array('1'));	
			$distanceLimit 	= 	$adminResult->LocationLimit;
			
			//check for user in the location
			if($distance > $distanceLimit) {
				$AllowCart 	= 	0;
				$message	= 'Your location is too far to order this item';
			}
			else{
				$AllowCart 	= 	1;
				$message	= 'You are allowed to order this item';
			}	
			$AllowCartArray['AllowCart'] = $AllowCart;		
			$AllowCartArray['Message'] = $message;	
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
		
		$sql 			= 	"SELECT SQL_CALC_FOUND_ROWS u.id as userId,u.FirstName,u.LastName,u.Photo,MAX(o.OrderDate) as LastVisit,
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
		if(isset($mangopayDetails) && count($mangopayDetails) > 0 && isset($mangopayDetails->Id)){
			$uniqueId								=	$mangopayDetails->Id;
			$walletId								=	createWallet($uniqueId,$merchantArray['Currency']);
			$merchants 								= 	R::dispense('merchants');
			$merchants->id 							= 	$merchantsId;
			$merchants->MangoPayTransactionDetails 	= 	serialize($mangopayDetails);
			$merchants->MangoPayUniqueId 			= 	$uniqueId;
			$merchants->WalletId 					= 	$walletId;
			$merchantsUpdate 						= 	R::store($merchants);
			return $mangopayDetails;
		} else {
			throw new ApiException("Error with mangopay/functions" ,  ErrorCodeType::SomeFieldsRequired);
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
                 ['CompanyName'],['FirstName'],['LastName'],['Email'],['Country'],['Currency'],['Birthday']
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
	
	/**
    * transaction list for merchant
    */
	public function getPaymentsList()
    {
		$bean 				= 	$this->bean;
				
		$merchantDetail 	=	 R::findOne('merchants', 'id = ? and Status = ?', [$bean->merchantId,StatusType::ActiveStatus]);	
		if($merchantDetail) {
			if(!empty($merchantDetail->WalletId) && !empty($merchantDetail->MangoPayUniqueId)) {
								
				//forming input array
				$inputarr['Id']				=	$merchantDetail->WalletId;
				if(isset($bean->Start) && !empty($bean->Start))
					$inputarr['Start']		=	$bean->Start;
				if(isset($bean->End) && !empty($bean->End))
					$inputarr['End']		=	$bean->End;
				if(isset($bean->Status) && !empty($bean->Status)) {
					if($bean->Status == 1)
						$inputarr['Status']		=	'SUCCEEDED';
					else if($bean->Status == 2)
						$inputarr['Status']		=	'FAILED';
				}
				if(isset($bean->Nature) && !empty($bean->Nature))
					$inputarr['Nature']		=	$bean->Nature;
				$result		=	GetTransactionsNew($inputarr);				
				if($result) {
					
					//getting user's namelist 
					$sql =	"SELECT DISTINCT o.fkUsersId, u.FirstName, u.LastName, u.MangoPayUniqueId, u.WalletId FROM `orders` o
								LEFT JOIN users u ON ( o.fkUsersId = u.id )
								WHERE 1 AND o.fkMerchantsId =".$bean->merchantId." AND u.MangoPayUniqueId != '' AND u.WalletId != ''";					
					$userList 	=	R::getAll($sql);
					if($userList) {
						foreach($userList as $key=>$val) {
							$nameArray[$val['MangoPayUniqueId']]	=	ucfirst($val['FirstName'].' '.$val['LastName']);
						}
					}
					
					//forming result 
					foreach($result as $key=>$val){
						$out[$key]					= 	(array)$val;
						
						//user uniqueId
						$id							=	$val->AuthorId;
						if(array_key_exists($id,$nameArray))
							$out[$key]['Customer']	=	$nameArray[$id];
						else
							$out[$key]['Customer']	=	'';
					}
					
					return $out;
				}
				else {
					/*
					*No Transactions Found
					*/
					throw new ApiException("No Transactions Found", ErrorCodeType::NoResultFound);
				}
			} else {
				/*
				*Account error
				*/
				throw new ApiException("You are not connected with any payment accounts", ErrorCodeType::NoResultFound);
			}
		} else {
			/**
	        * throwing error when no data found
	        */
			throw new ApiException("Merchant not in active status or not found", ErrorCodeType::NoResultFound);
		}
    }
	
	/*
	* get products count
	*/
	public function getProductCounts()
    {
		/**
		* Get the bean
		* @var $bean merchants
		*/
        $bean = $this->bean;
		
		//assigning variable
		$deals	=	$specials	= $regular	=	$counts 	=	array();
		$applied	= $unapplied	=	0;
		
		//getting product details
		$productDetail 					= 	R::dispense('products');
		$productDetail->merchantId		=	$bean->merchantId;
		$countResult					= 	$productDetail->getProductsTotal();	
		if($countResult && is_array($countResult) && count($countResult) > 0) {
			
			//getting various counts of products 			
			foreach($countResult as $val) {
				if($val['ItemType'] == 1) {
					if($val['DiscountApplied'] == 1)
						$applied	=	$applied	+ 1;
					if($val['DiscountApplied'] == 0)
						$unapplied	=	$unapplied	+ 1;
					$regular[]		=	$val;
				}
				if($val['ItemType'] == 2)
					$deals[]		=	$val;
				if($val['ItemType'] == 3)
					$specials[]		=	$val;
			}
			$counts['Totalproducts']				=	count($countResult);
			$counts['Regular']						=	count($regular);
			$counts['Deals']						=	count($deals);
			$counts['Specials']						=	count($specials);
			$counts['RegularDiscountApplied']		=	$applied;
			if(isset($bean->Discount) && $bean->Discount == 0)
				$counts['RegularDiscountApplied']	=	$applied	-	1;
			$counts['RegularDiscountNotApplied']	=	$unapplied;
			
			//getting how products to be discounted
			$discountMust	=	floor(((1/3) * count($countResult)));
			$counts['DiscountProductMustBe']		=	$discountMust;
			
			//getting total discounted products
			$disapplied		=	$counts['Deals'] + $counts['Specials'] + $counts['RegularDiscountApplied'];
			$counts['TotalDiscountApplied']			=	$disapplied;
						
			//checking the rule 1 (1/3 of product discount)
			if($counts['TotalDiscountApplied'] >= $counts['DiscountProductMustBe']) {
				$counts['Discounted']				=	1;
				$counts['ProductDifference']		=	$counts['TotalDiscountApplied'] - $counts['DiscountProductMustBe'];
				if(isset($bean->Type) && $bean->Type == 1) {					
					$counts['ProductPlusDiscount']		=	floor(((1/3) * ($counts['Totalproducts'])));
				}
				else 
					$counts['ProductPlusDiscount']		=	floor(((1/3) * ($counts['Totalproducts']  + 1)));
			}
			else {
				/**
				 * throwing error when Merchant not having 1/3 of products discounted
				 */
				 throw new ApiException("Sorry! 1/3 of your products should be with discount", ErrorCodeType::ProductDiscountMust);
			}
			return  $counts;				
		} else {
				/**
				* throwing error when no Products found
				*/
				throw new ApiException("No Products found", ErrorCodeType::NoResultFound);
		}
	}
	
	/*
	* get slideshow details
	*/
	public function getSlideshowDetails()
    {
		/**
		* Get the bean
		* @var $bean merchants
		*/
        $bean = $this->bean;
		
		$sql =	"SELECT * FROM `merchantslideshow` m
					WHERE 1 AND m.fkMerchantId ='".$bean->merchantId."'";					
		$slideshows 	=	R::getAll($sql);
		if($slideshows) {
			foreach($slideshows as $key=>$val)
				$slideshows[$key]['imagePath']	=	MERCHANT_IMAGE_PATH.$bean->merchantId.'/'.$val['SlideshowName'];
			return $slideshows;
		}		
	}
	
	/*
	* get slideshow details
	*/
	public function deleteSlideshow()
    {
		/**
		* Get the bean
		* @var $bean merchants
		*/
        $bean = $this->bean;
		
		$sql =	"DELETE FROM `merchantslideshow` WHERE 1 AND fkMerchantId ='".$bean->merchantId."' and id in (".$bean->DeleteIds.")";	
		//echo $sql;
		return R::exec($sql);
		
	}
	
	/*
	* create salesperson
	*/
	public function createSalesperson()
    {
		/**
		* Get the bean
		* @var $bean merchants
		*/
        $bean = $this->bean;
		
		// validate the model
        $this->validateSalesperson();

        // validate the creation
        $this->validateCreateSalesperson();
		
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
		$bean->MainMerchantId		= 	$bean->MerchantId;
		unset($bean->MerchantId);
		$bean->UserType = '2';
		$bean->Status   = '1';
			
        // modify the bean to the database
        $subuserId = R::store($this);
		return $subuserId; 
	}
	
	/**
    * Validate salesperson params
    * @throws ApiException if the models fails to validate
    */
	public function validateSalesperson($type = '')
    {
		$bean = $this->bean;
		if($type == 1){
			$rules = [
				'required' => [
					 ['FirstName'],['LastName'],['Email']
				],
				
			];
		} else {
			$rules = [
				'required' => [
					 ['FirstName'],['LastName'],['Email'],['Password'],['MerchantId']
				],
				
			];
		}
        $v = new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
			// the action was not found
            throw new ApiException("Please check the salesperson properties. Fill FirstName,LastName,Email with correct values" ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
    }
	
	/**
	* Validate the salesperson creation of an account
	*/
    public function validateCreateSalesperson(){

		/**
		* Get the bean
		*/
        $bean = $this->bean;
		
		/**
		* Email Id must be unique
		*/
       //$existingAccount = R::findOne('merchants', 'Email = ? and Status <> ? and UserType = ? and MerchantId = ? order by DateModified desc', array($bean->Email,StatusType::DeleteStatus,2,$bean->MerchantId));
        $existingAccount = R::findOne('merchants', 'Email = ? and Status <> ? order by DateModified desc', array($bean->Email,StatusType::DeleteStatus));
        if ($existingAccount) {
            // an account with that email already exists in the system - don't create account
            throw new ApiException("This Email Address is already associated with another account", ErrorCodeType::EmailAlreadyExists);
		}
    }
	
	/*
	* get salesperson List
	*/
	public function getSalespersonList()
    {
		/**
		* Get the bean
		* @var $bean merchants
		*/
        $bean 		= 	$this->bean;
		$sql 		=	"SELECT * FROM `merchants` m WHERE 1 and m.UserType = 2 and m.Status = 1 and m.MainMerchantId ='".$bean->merchantId."' order by id desc";					
		$result 	=	R::getAll($sql);
		if($result) {
			foreach($result as $key=>$val){
				$salespersonlist[$key]['id']	=	$val['id'];
				$salespersonlist[$key]['Name']	=	ucfirst($val['FirstName'].' '.$val['FirstName']);
				$salespersonlist[$key]['Email']	=	$val['Email'];
				$salespersonlist[$key]['DateCreated']	=	$val['DateCreated'];
			}
			
			$salespersonlistarray['result'] 	= 	$salespersonlist;
			$salespersonlistarray['totalCount']	= 	count($salespersonlist);
			return $salespersonlistarray;
		} else {		
			/**
			* throwing error when no salesperson found
			*/
			throw new ApiException("No salesperson found", ErrorCodeType::NoResultFound);
		}
	}
	
	/*
	* get salesperson details
	*/
	public function getSalespersonDetails()
    {
		/**
		* Get the bean
		* @var $bean merchants
		*/
        $bean 		= 	$this->bean;
		$result 	=	R::getRow('select id,FirstName,LastName,Email from merchants where id = ? and MainMerchantId = ? and UserType = ? and Status = ?', array($bean->userId,$bean->merchantId,2,1));
		if($result) {
			return $result;
		} else {		
			/**
			* throwing error when no salesperson found
			*/
			throw new ApiException("No salesperson found", ErrorCodeType::NoResultFound);
		}
	}
	
	/*
	* get salesperson details
	*/
	public function deleteSalesperson()
    {
		/**
		* Get the bean
		* @var $bean merchants
		*/
        $bean 		= 	$this->bean;
		$result 	=	R::getCol("select id from merchants where id = '".$bean->userId."' and Status != 3");
		if($result) {
			return R::exec("update merchants set Status = 3 where id ='".$bean->userId."'");;
		} else {		
			/**
			* throwing error when no salesperson found
			*/
			throw new ApiException("salesperson was not found or already deleted", ErrorCodeType::NoResultFound);
		}
	}
	
	/*
	* modify salesperson details
	*/
	public function modifySalesPerson()
    {
		/**
		* Get the bean
		* @var $bean merchants
		*/
        $bean 		= 	$this->bean;
		echo
		// validate the model
        $this->validateSalesPerson(1);
		
		// encrypt the password
		if($bean->Password)
        	$bean->Password 		= 	PasswordHelper::encrypt($bean->Password);
			
		$bean->DateModified 		= 	date('Y-m-d H:i:s');
		
		// modify the bean to the database
        $subuserId = R::store($this);
		return $subuserId; 
	}
	
	/*
	* get products count
	*/
	public function getTagType($merchantId)
    {
		//assigning variable
		$deals	=	$specials	= $regular	=	$applied	= $unapplied	=	0;
		
		//getting product details
		$productDetail 					= 	R::dispense('products');
		$productDetail->merchantId		=	$merchantId;
		$countResult					= 	$productDetail->getProductsTotal();	
		if($countResult && is_array($countResult) && count($countResult) > 0) {
			
			//getting various counts of products 			
			foreach($countResult as $val) {
				if($val['ItemType'] == 1) {
					if($val['DiscountApplied'] == 1)
						$applied	=	$applied	+ 1;
					if($val['DiscountApplied'] == 0)
						$unapplied	=	$unapplied	+ 1;
					$regular		=	$regular + 1;
				}
				if($val['ItemType'] == 2)
					$deals			=	$deals + 1;
				if($val['ItemType'] == 3)
					$specials		=	$specials + 1;
			}
			$Totalproducts				=	count($countResult);
			
			//getting total discounted products
			$disapplied		=	$deals + $specials + $applied;
			
			//getting how products to be discounted
			$discountMust		=	floor(((1/3) * count($countResult)));
			$ProductMustBe1		=	$discountMust;
			$discountMust		=	floor(((2/3) * count($countResult)));
			$ProductMustBe2		=	$discountMust;
			$discountMust		=	floor(((3/3) * count($countResult)));
			$ProductMustBe3		=	$discountMust;
			
			$tagType		=	0;
			if($disapplied > 0) {
				if($disapplied	==	$Totalproducts)
					$tagType	=	3;
				else if(($disapplied > $ProductMustBe1) && ($disapplied >= $ProductMustBe2))
					$tagType	=	2;
				else if(($disapplied >= $ProductMustBe1))
					$tagType	=	1;
			} else
				return 0;
			if($_SERVER['REMOTE_ADDR'] == '172.21.4.130'){
				/*$counts["Totalproducts"]		=	$Totalproducts;
				$counts["regular"]     			=  	$regular;
				$counts["deals"]     			=  	$deals;
				$counts["specials"]     		=   $specials;
				$counts["regular discount Applied"]     =   $applied;
				$counts["discount Applied"]     =   $disapplied;
				$counts["discount not Applied"] =   $unapplied;
				$counts["1/3"]     				=   $ProductMustBe1;
				$counts["2/3"]     				=   $ProductMustBe2;
				$counts["3/3"]					=	$ProductMustBe3;

				echo "<pre>"; echo print_r($counts); echo "</pre>";	
				echo "----------------------->".$tagType;*/
			}
			
			return $tagType;
			
		} else
			return 0;
	}
}
