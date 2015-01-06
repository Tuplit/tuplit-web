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
require_once 'Users.php';

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
				 ['FirstName'],['LastName'],['Email'],['CompanyName'],['PhoneNumber'],['Password']
			],
			'Email' => 'Email',
		];
		
        $v = new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
            throw new ApiException("Please check the merchant's properties. Fill Name,Email,Password with correct values" ,  ErrorCodeType::SomeFieldsRequired, $errors);
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
    public function modify($merchantsId = null,$iconExist='',$merchantExist='',$backgroundExist = ''){

		/**
		* Get the bean
		*/
		$bean = $this->bean;
		// validate the model
        $this->validateModifyMerchant($iconExist,$merchantExist,$backgroundExist);
		
        // validate the modification
        $this->validateModify($merchantsId);

        $bean->DateModified = date('Y-m-d H:i:s');
		if($bean->Password){
        	$bean->Password 		= 	PasswordHelper::encrypt($bean->Password);
		}
		
		$Address = '';
		if(!empty($bean->Street))
			$Address	.=	$bean->Street;
		if(!empty($bean->City))
			$Address	.=	', '.$bean->City;
			
		if(!empty($bean->State) && !empty($bean->PostCode))
			$Address	.=	', '.$bean->State.' - '.$bean->PostCode;
		else if(!empty($bean->State) && empty($bean->PostCode))
			$Address	.=	', '.$bean->State;
		else if(empty($bean->State) && !empty($bean->PostCode))
			$Address	.=	', '.$bean->PostCode;
		
		if(!empty($bean->Country))
			$Address	.=	', '.$bean->Country;
		
		
		if(isset($Address) && !empty($Address)) {
			$latlong = $lat = $lng = '';
			$latlong = getLatLngFromAddress($Address) ;
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
    public function validateModifyMerchant($iconExist,$merchantExist,$backgroundExist)
    {
		$bean 		= 	$this->bean;
		$temp 		= 	array(
								array('CompanyName'),
								array('Email'),
								array('ShortDescription'),
								array('Description'),								
								array('PhoneNumber'),
								array('WebsiteUrl'),
								array('PriceRange'),
								array('DiscountTier'),
								array('Street'),
								array('City'),
								array('PostCode'),								
								array('State'),								
								array('Country'),
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
		}else if($backgroundExist == ''){
			$temp[] = 	array('BackgroundPhoto');
		}
			
		$rules = [
	            'required' => $temp,
				'Url' => 'WebsiteUrl',
				/*'in' =>[
						['DiscountTier',['1','2','3','4','5','6']]
					]*/
	        ];		
        $v = new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
            throw new ApiException("Please check the merchant's properties. Fill Company Name,Phone Number,WebsiteUrl,Discount Tier,Price Range,Icon Photo,Merchant Photo with correct values" ,  ErrorCodeType::SomeFieldsRequired, $errors);//Email,
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
		
		//validate merchant
		$this->validateMerchants($merchantId,3);
				
		if($from == 1){
			if($_SERVER['SERVER_ADDR'] != '172.21.4.104'){
				//Check for total products   - 27/10/2014
				$sql		=	"SELECT `fkMerchantsId`,count(*) as totcount FROM `products` WHERE 1 and fkMerchantsId ='".$merchantId."' and `ItemType` in (1,3) and `Status` in (1,2) group by `fkMerchantsId` HAVING totcount >= 20 ";
				$producttot = 	R::getAll($sql);
				if(!$producttot) {
					throw new ApiException("The Merchant you requested was not published.", ErrorCodeType::MerchantNotPublished);
				}
			}	
			//Validate latitude and longitude	
			$this->validateLatLong();				

			//Already Favourited
			if(!empty($userId) && !empty($latitude) && !empty($longitude)){
			
				$this->validateUser($userId);				
				$fields			.= ",FavouriteType as AlreadyFavourited";
				$joinCondition	= "LEFT JOIN favorites as f ON(m.id = f.fkMerchantsId and f.fkMerchantsId = ".$merchantId." and f.fkUsersId = ".$userId." and FavouriteType = 1) ";
			}			
		}	
		
		// Merchant details		
		$sql 	= 	"SELECT m.*,m.id as MerchantId".$fields." 
						FROM merchants as m	".$joinCondition."	where m.Status = 1 and m.id=".$merchantId." ";

		$merchants 		= R::getAll($sql);
		$merchants 		= $merchants[0];		
		if (!$merchants) {
			// the Merchants was not found
			throw new ApiException("Your status is not in active state", ErrorCodeType::MerchantsNotInActiveStatus);
		}
		else
		{
			$merchants['Address'] = '';
			if(!empty($merchants['Street']))
				$merchants['Address']	.=	$merchants['Street'];
			if(!empty($merchants['City']))
				$merchants['Address']	.=	', '.$merchants['City'];
			
			if(!empty($merchants['State']) && !empty($merchants['PostCode']))
				$merchants['Address']	.=	', '.$merchants['State'].' - '.$merchants['PostCode'];
			else if(!empty($merchants['State']) && empty($merchants['PostCode']))
				$merchants['Address']	.=	', '.$merchants['State'];
			else if(empty($merchants['State']) && !empty($merchants['PostCode']))
				$merchants['Address']	.=	', '.$merchants['PostCode'];
			
			if(!empty($merchants['Country']))
				$merchants['Address']	.=	', '.$merchants['Country'];
			$merchants['Country']	=	$merchants['Country'];
			
			if(isset($merchants['BrowserDetails']) && !empty($merchants['BrowserDetails']))
				$merchants['BrowserDetails'] = $merchants['BrowserDetails'];
			else 
				$merchants['BrowserDetails'] =	'';
			//condition to check whether we can allow cart to be enabled
			if(!empty($latitude) && !empty($longitude)){
				if($merchants['distance'] != '') {					
					$result 		= 	R::getAll("select LocationLimit from admins where id=1");
					$distanceLimit	=	$result[0]['LocationLimit'];
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
			if($from == 1){
				
				//category details
				$merchantsDetails['CategoryList']			=	$merchantCategories['categoryDeatil'];
							
				//merchant Comments
				$comments 									= 	R::dispense('comments');
				$comments->MerchantId						= 	$merchantId;
				$comments->Start 							= 	0;
				$comments->Limit 							= 	3;
				$merchantsDetails['TotalComments'] 			= 	0;
				$merchantsDetails['Comments'] 				= 	Array();
				$merchantsComments			 				= 	$comments->getMerchantCommentList();
				if(!empty($merchantsComments) && isset($merchantsComments['totalcomments']) && $merchantsComments['totalcomments'] > 0) {
					$merchantsDetails['TotalComments'] 		= 	$merchantsComments['totalcomments'];
					$merchantsDetails['Comments'] 			= 	$merchantsComments['comments'];
				}
				
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

			} else {
				//SalespersonList 
				$merchantsDetails['SalespersonList']	= 	$this->getSalespersonList($merchantId,1);
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
		$sql 				= 	"select SlideshowName from merchantslideshow where fkMerchantId=".$merchantId;
		$slideshowImages	= 	R::getAll($sql);
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
		$sql		 = "select id,FirstName,LastName,Email,Status,MangoPayUniqueId from merchants where id = '".$merchantsId."' and Status != 3";
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
			if($merchants[0]['Status'] == 2 || $merchants[0]['Status'] == 0){
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
		$sql 		 	 = " select id,FirstName,LastName,ResetPassword,Status,Email,OrderMail from merchants where Email = '".$email."' order by id desc";
		//echo $sql;
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
		$condition	= $searchCondition	= $category	 = $group_condition	= $categorytype = $userId = '';
		
		/**
		* Get the bean
		*/
		$bean 			= 	$this->bean;
		$latitude		=	$bean->Latitude;			
		$longitude		=	$bean->Longitude;
		
		//Validate latitude and longitude
		$this->validateLatLong();
		
		if($bean->Start)					$Start 			= 	$bean->Start;			else		$Start 		= 	0;	
		if($bean->Type)						$type			=	$bean->Type;			else		$type 		= 	0;
		if(isset($bean->Category))			$category		=	$bean->Category;		
		if(isset($bean->CategoryType))		$categorytype	=	$bean->CategoryType;		
		if(isset($bean->UserID) && !empty($bean->UserID))		$userId	=	$bean->UserID;
		if(isset($bean->DiscountTier) && $bean->DiscountTier != ''){
			$tierVal	=	$bean->DiscountTier;			
			//Validate discount tier
			$this->validateDiscountTier($tierVal);
			if($tierVal > 0)
				$searchCondition	.=	" and m.DiscountTier = ".$tierVal." ";
		}
		
		//Check for total products   - 27/10/2014
		$merchantsIdIn	=	'';
		$sql		=	"SELECT p.`fkMerchantsId`,count(p.id) as totcount FROM `products` as p
							left join merchants as m on (p.fkMerchantsId = m.id)
							WHERE 1 and p.`ItemType` in (1,3) and m.Status = 1 and p.`Status` in (1,2) group by p.`fkMerchantsId` HAVING totcount >= 20 ";
		//echo $sql;
		$producttot = 	R::getAll($sql);
		if($producttot) {
			$merchantsIdInArray = Array();
			foreach($producttot as $val) {
				$merchantsIdInArray[]		=	$val['fkMerchantsId'];
			}
			if(count($merchantsIdInArray) > 0)
				$merchantsIdIn = implode(',',$merchantsIdInArray);
		} else {
			/**
			* throwing error when no data found
			*/
			throw new ApiException("No Merchants Found", ErrorCodeType::NoResultFound);
		}
		if(!empty($merchantsIdIn))
			$merchantsIdIn = " and m.id in (".$merchantsIdIn.") ";

		if(!empty($categorytype) && $categorytype != 4){
			$temp_condition	=	$error_data = '';
			$error	=	0;
			if(isset($merchantsIdInArray) && !empty($merchantsIdInArray) && count($merchantsIdInArray) > 0)
				$temp_condition	=	" and fkMerchantsId in (".implode(',',$merchantsIdInArray).") ";
			if($categorytype == 1) {				
				$sql	=	"SELECT fkMerchantsId FROM products WHERE 1 and ItemType in (1,3) and Status in (1,2) and DiscountApplied = 0 ".$temp_condition." group by fkMerchantsId";
				$temp 	=	R::getAll($sql);
				if($temp) {
					$newIds		=	Array();
					foreach($temp as $val)
						$newIds[]	=	$val['fkMerchantsId'];
					$result			=	array_diff($merchantsIdInArray,$newIds);
					$merchantsIdIn 	= 	" and m.id in (".implode(',',$result).") ";
				} else {
					$error		=	1;
					$error_data	=	'No wholemenu discounted merchant';
				}
			} else if($categorytype == 2) {
				$sql	=	"SELECT m.id FROM merchants m WHERE 1 and m.Status = 1 ".$merchantsIdIn." and Date(m.DateCreated) between '".date('Y-m-d', strtotime('-30 days'))."' and '".date('Y-m-d')."'";
				$temp 	=	R::getAll($sql);
				if($temp) {
					$newIds		=	Array();
					foreach($temp as $val)
						$newIds[]	=	$val['id'];
					$merchantsIdIn = " and m.id in (".implode(',',$newIds).") ";
				} else {
					$error		=	1;
					$error_data	=	'No recently added merchant';
				}
			} else if($categorytype == 3) {
				if(!empty($userId)) {
					$sql	=	"SELECT distinct fkMerchantsId FROM favorites WHERE 1 and fkUsersId = ".$userId." ".$temp_condition." and FavouriteType=1";
					//echo $sql;
					$temp 	=	R::getAll($sql);
					if($temp) {
						$newIds		=	Array();
						foreach($temp as $val)
							$newIds[]	=	$val['fkMerchantsId'];
						$merchantsIdIn 	= 	" and m.id in (".implode(',',$newIds).") ";
					} else {
						$error		=	1;
						$error_data	=	'No Favorites merchant';
					}
				} else
					throw new ApiException("Please check the merchant search properties", ErrorCodeType::SomeFieldsRequired);
			}
			if($error == 1)
			{
				if(empty($error_data))
					$error_data	=	'No merchant found';
				throw new ApiException($error_data, ErrorCodeType::NoResultFound);
			}
		}		
		/**
		* Query to get merchant details
		*/
		if($bean->SearchKey != '')
			$searchCondition	.=	" and (m.CompanyName like '%".$bean->SearchKey."%' or  m.ShortDescription like '%".$bean->SearchKey."%')";
			
		if(!empty($category)){
			$fields				 =	" m.id,m.id as MerchantId,DiscountType,m.Icon,m.Image,m.DiscountTier,m.CompanyName,m.ShortDescription,mc.fkCategoriesId as Category,m.ItemsSold,m.Latitude,m.Longitude,m.Street,m.City,m.State,m.PostCode,m.Country,DiscountProductId";
			$join_condition 	 = 	" LEFT JOIN  merchantcategories as mc ON (mc.fkMerchantId = m.id) ";
			if($type == 2){
				$fields				.= 	",count(DISTINCT(o.fkUsersId)) as TotalUsersShopped ";
				$join_condition 	.= 	" left join  orders as o ON (o.fkMerchantsId = m.id) ";
			}
			$searchCondition	.=	" and mc.fkCategoriesId ='".$category."'";
		}
		else{
			$fields				=	"m.id,m.id as MerchantId,DiscountType,m.Icon,m.Image,m.DiscountTier,m.CompanyName,m.ShortDescription,m.ItemsSold,m.Latitude,m.Longitude,m.Street,m.City,m.State,m.PostCode,m.Country,DiscountType,DiscountProductId, group_concat(DISTINCT(mc.fkCategoriesId)) as Category,count(DISTINCT(o.fkUsersId)) as TotalUsersShopped";
			$join_condition 	= 	" LEFT JOIN  merchantcategories as mc ON (mc.fkMerchantId = m.id) ";
			$join_condition 	.= 	" left join  orders as o ON (o.fkMerchantsId = m.id) ";
		}
		if($type == 2){
			$fields				.=	",count(o.id) as OrderCount";
		}
		if($type == 2 )
			$orderby			=	"orderCount desc";
		else
			$orderby			=	"distance asc";
		
		if(!empty($merchantIds)) { 
			$condition 			= 	" and m.id in (".$merchantIds.")";
		}
		$group_condition	=  " group by m.id";
		$sql 				= 	"SELECT SQL_CALC_FOUND_ROWS ".$fields.",date(m.DateCreated) as DateCreated,(((acos(sin((".$latitude."*pi()/180)) * sin((m.`Latitude`*pi()/180))+cos((".$latitude."*pi()/180)) * cos((m.`Latitude`*pi()/180)) * cos(((".$longitude."- m.`Longitude`)*pi()/180))))*180/pi())*60*1.1515)*1.6093 as distance  from merchants as m ".$join_condition." 
								where  m.Status = 1 and m.UserType = 1 ".$searchCondition." ".$condition." and m.Image != '' ".$merchantsIdIn." ".$group_condition." 								
								 ORDER BY ".$orderby." limit $Start,10";	
		//echo $sql;
		$result 			= 	R::getAll($sql);
		$totalRec 			= 	R::getAll('SELECT FOUND_ROWS() as count ');
		$total 				= 	(integer)$totalRec[0]['count'];
		$listedCount		= 	count($result);
		$discountedProduct 	= 	0;
		
		if($result){
			foreach($result as $key=>$value)
			{
				$imagePath = $iconPath	= 	'' ;
				
				$value['Address'] = '';
				if(!empty($value['Street']))
					$value['Address']	.=	$value['Street'];
				if(!empty($value['City']))
					$value['Address']	.=	', '.$value['City'];
				
				if(!empty($value['State']) && !empty($value['PostCode']))
					$value['Address']	.=	', '.$value['State'].' - '.$value['PostCode'];
				else if(!empty($value['State']) && empty($value['PostCode']))
					$value['Address']	.=	', '.$value['State'];
				else if(empty($value['State']) && !empty($value['PostCode']))
					$value['Address']	.=	', '.$value['PostCode'];
				
				if(!empty($value['Country']))
					$value['Address']	.=	', '.$value['Country'];
				$value['Country']	=	$value['Country'];
				unset($value['Street']);
				unset($value['City']);
				unset($value['PostCode']);
				unset($value['State']);
				unset($value['Country']);
				
				$iconPath  				= 	MERCHANT_ICONS_IMAGE_PATH.$value['Icon'];
				$imagePath 				= 	MERCHANT_IMAGE_PATH.$value['Image'];
				$value['Image'] 		= 	$imagePath;
				$value['Icon'] 			= 	$iconPath;
				
				//new tag for merchant
				$before30days			=	date('Y-m-d', strtotime('-30 days'));
				$DateCreated			=	$value['DateCreated'];
				if($DateCreated != '0000-00-00' && $DateCreated >= $before30days)
					$value['NewTag'] 	= 	'1';
				else
					$value['NewTag'] 	= 	'0';
				unset($value['DateCreated']);
				
				if($value['DiscountTier']  != '')
					$value['DiscountTier'] 	= $discountTierArray[$value['DiscountTier']].'%';
				
				$value['TagType']			=	$this->getTagType($value['id']);
				$MerchantListArray[] 		= 	$value;
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
		
		$logStart						=	microtime(true);
		$mangopayDetails 				=   merchantRegister($merchantArray);
		
		//MangoPay Log
		$logArray				=	Array();	
		$logArray['MerchantId']	=	$merchantsId;
		$logArray['URL']		=	'merchantRegister';
		$logArray['Content']	=	$merchantArray;
		$logArray['Start']		=	$logStart;
		$logArray['End']		=	microtime(true);
		$logArray['Response']	=	$mangopayDetails;
		$log 	=	R::dispense('users');
		$log->storeMangoPayLog($logArray);

		if(isset($mangopayDetails) && count($mangopayDetails) > 0 && isset($mangopayDetails->Id)){
			$merchants 								= 	R::dispense('merchants');
			$uniqueId								=	$mangopayDetails->Id;
			
			$logStart								=	microtime(true);
			$walletId								=	createWallet($uniqueId,$merchantArray['Currency']);
			
			//MangoPay Log
			$logArray				=	Array();	
			$logArray['MerchantId']	=	$merchantsId;
			$logArray['URL']		=	'createWallet';
			$logArray['Content']	=	Array('uniqueId'=>$uniqueId, 'Currency'=>$merchantArray['Currency']);
			$logArray['Start']		=	$logStart;
			$logArray['End']		=	microtime(true);
			$logArray['Response']	=	$walletId;
			$log 	=	R::dispense('users');
			$log->storeMangoPayLog($logArray);
			
			$merchants->MangoPayUniqueId 			= 	$uniqueId;
			$merchants->WalletId 					= 	$walletId;
			$merchants->MangoPayTransactionDetails 	= 	serialize($mangopayDetails);
			$merchants->id 							= 	$merchantsId;
			$merchantsUpdate 						= 	R::store($merchants);
			$mangopayDetails						= 	R::dispense('mangopaydetails');
			$mangopayDetails->fkMerchantId			=	$merchantsId;
			$mangopayDetails->CompanyName			=	$merchantArray['CompanyName'];
			$mangopayDetails->Email 				= 	$merchantArray['Email'];
			$mangopayDetails->FirstName 			=   $merchantArray['FirstName'];
			$mangopayDetails->LastName 				= 	$merchantArray['LastName'];
			$mangopayDetails->DOB					=	changeDate_DBformat($merchantArray['Birthday']);
			$mangopayDetails->Address 				= 	$merchantArray['Address'];
			$mangopayDetails->Country				=	$merchantArray['Country'];
			$mangopayDetails->Currency				=	$merchantArray['Currency'];
			$mangopayDetailsAdd 					= 	R::store($mangopayDetails);
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
					
				$logStart	=	microtime(true);
				$result		=	GetTransactionsNew($inputarr);
				//MangoPay Log
				$logArray				=	Array();	
				$logArray['MerchantId']		=	$bean->merchantId;
				$logArray['URL']		=	'GetTransactionsNew';
				$logArray['Content']	=	$inputarr;
				$logArray['Start']		=	$logStart;
				$logArray['End']		=	microtime(true);
				$logArray['Response']	=	$result;
				$log 	=	R::dispense('users');
				$log->storeMangoPayLog($logArray);
				
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
		if(isset($bean->id))
      	  $existingAccount = R::findOne('merchants', 'Email = ? and id <> ? and Status <> ? order by DateModified desc', array($bean->Email,$bean->id,StatusType::DeleteStatus));
		else
		  $existingAccount = R::findOne('merchants', 'Email = ? and Status <> ? order by DateModified desc', array($bean->Email,StatusType::DeleteStatus));
        if ($existingAccount) {
            // an account with that email already exists in the system - don't create account
            throw new ApiException("This Email Address is already associated with another account", ErrorCodeType::EmailAlreadyExists);
		}
    }
	
	/*
	* get salesperson List
	*/
	public function getSalespersonList($merchantId,$type = '')
    {
		$salespersonlistarray	=	Array();
		/**
		* Get the bean
		* @var $bean merchants
		*/
        $bean 		= 	$this->bean;
		$sql 		=	"SELECT id,FirstName,LastName,Email,Image,DateCreated FROM `merchants` m WHERE 1 and m.UserType = 2 and m.Status = 1 and m.MainMerchantId ='".$merchantId."' order by id desc";					
		$result 	=	R::getAll($sql);
		if($result) {
			foreach($result as $key=>$val){
				$imagePath	=	'';
				$salespersonlist[$key]['id']			=	$val['id'];
				$salespersonlist[$key]['Name']			=	ucfirst($val['FirstName'].' '.$val['LastName']);
				$salespersonlist[$key]['Email']			=	$val['Email'];
				if($val["Image"] !=''){
					if(SERVER){
						$imagePath 		= SALESPEOPLE_IMAGE_PATH.$merchantId.'/'.$val["Image"];
					}
					else{
						if(file_exists(SALESPEOPLE_IMAGE_PATH_REL.$merchantId.'/'.$val["Image"]))
							$imagePath = SALESPEOPLE_IMAGE_PATH.$merchantId.'/'.$val["Image"];
					}
				}
				$salespersonlist[$key]['Image']			=	$imagePath;
				$salespersonlist[$key]['DateCreated']	=	$val['DateCreated'];
			}
			$salespersonlistarray['totalCount']		= 	count($salespersonlist);
			$salespersonlistarray['salesperson'] 	= 	$salespersonlist;
			return $salespersonlistarray;
		} else if($type == ''){		
			/**
			* throwing error when no salesperson found
			*/
			throw new ApiException("No salesperson found", ErrorCodeType::NoResultFound);
		} else {
			return $salespersonlistarray;
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
		$result 	=	R::getRow('select id,FirstName,LastName,Email,Image from merchants where id = ? and MainMerchantId = ? and UserType = ? and Status = ?', array($bean->userId,$bean->merchantId,2,1));
		if($result) {
			$image 		= 	$result['Image'];
			$imagePath	=	'';
			if($image !=''){
				if(SERVER){
					$imagePath 		=	SALESPEOPLE_IMAGE_PATH.$bean->merchantId.'/'.$image;
				}
				else{
					if(file_exists(SALESPEOPLE_IMAGE_PATH_REL.$bean->merchantId.'/'.$image));
						$imagePath 	= 	SALESPEOPLE_IMAGE_PATH.$bean->merchantId.'/'.$image;
				}
			}
			$result['Image']			=	$imagePath;
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
		//echo
		// validate the model
        $this->validateSalesPerson(1);
		 // validate the email
        $this->validateCreateSalesperson();
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
		$deals	=	$specials	= $regular	=	$applied	= $unapplied	=	$Totalproducts = 0;
		
		//getting product details
		$productDetail 					= 	R::dispense('products');
		$productDetail->merchantId		=	$merchantId;
		$countResult					= 	$productDetail->getProductsTotal();	
		if($countResult && is_array($countResult) && count($countResult) > 0) {
			
			//getting various counts of products 			
			foreach($countResult as $val) {
				if($val['DiscountApplied'] != '') {
					$Totalproducts++;
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
			}
			
			//getting total discounted products
			$disapplied		=	$deals + $specials + $applied;
			
			//getting how products to be discounted
			$discountMust		=	floor(((1/3) * $Totalproducts));
			$ProductMustBe1		=	$discountMust;
			$discountMust		=	floor(((2/3) * $Totalproducts));
			$ProductMustBe2		=	$discountMust;
			$discountMust		=	floor(((3/3) * $Totalproducts));
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
			return $tagType;
			
		} else
			return 0;
	}
	
	/*
	* get mangopay details
	*/
	public function getMangopayDetails()
    {
		/**
		* Get the bean
		* @var $bean merchants
		*/
        $bean = $this->bean;
		
		$sql 				=	"SELECT * FROM `mangopaydetails` WHERE 1 AND fkMerchantId ='".$bean->merchantId."'";					
		$mangopayDetails 	=	R::getAll($sql);
		if($mangopayDetails) {
			return $mangopayDetails[0];
		}		
	}
	/*
	* Edit MangoPay Details
	*/
	public function editMangoPayDetails($merchantsId)
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
		$merchantArray['MangoPayId']	=	$bean->MangoPayId;
		$logStart						=	microtime(true);
		$mangopayDetails 				=   merchantEdit($merchantArray);
		
		//MangoPay Log
		$logArray				=	Array();	
		$logArray['MerchantId']	=	$merchantsId;
		$logArray['URL']		=	'merchantEdit';
		$logArray['Content']	=	$merchantArray;
		$logArray['Start']		=	$logStart;
		$logArray['End']		=	microtime(true);
		$logArray['Response']	=	$mangopayDetails;
		$log 	=	R::dispense('users');
		$log->storeMangoPayLog($logArray);
		
		
		if(isset($mangopayDetails) && count($mangopayDetails) > 0 && isset($mangopayDetails->Id)){
			$mangopayDetails 				= 	R::dispense('mangopaydetails');
			$sql 					=	"SELECT id FROM `mangopaydetails` WHERE 1 AND fkMerchantId ='".$merchantsId."'";					
			$getMangopayDetails 	=	R::getAll($sql);
			if(!empty($getMangopayDetails )){
				$mangopayDetailsId	=	$getMangopayDetails[0]['id'];
				$mangopayDetails->id			=	$mangopayDetailsId;
			}
			$mangopayDetails->fkMerchantId	=	$merchantsId;
			$mangopayDetails->CompanyName	=	$merchantArray['CompanyName'];
			$mangopayDetails->Email 		= 	$merchantArray['Email'];
			$mangopayDetails->FirstName 	=   $merchantArray['FirstName'];
			$mangopayDetails->LastName 		= 	$merchantArray['LastName'];
			$mangopayDetails->DOB			=	changeDate_DBformat($merchantArray['Birthday']);
			$mangopayDetails->Address 		= 	$merchantArray['Address'];
			$mangopayDetails->Country		=	$merchantArray['Country'];
			$mangopayDetails->Currency		=	$merchantArray['Currency'];
			$mangopayDetailsEdit 			= 	R::store($mangopayDetails);
			return $mangopayDetails;
		} else {
			throw new ApiException("Error with mangopay/functions" ,  ErrorCodeType::SomeFieldsRequired);
		}
	}
	/*
	* Add MangoPay Bank Account
	*/
	public function addMangoPayBankAccount($merchantsId)
    {
		$bean 			=	 $this->bean;
		$merchantArray 	= 	 array();
		//Validate MangoPay params
		$this->validateMangoPayBank($bean->BankType);
		$merchantArray['UserName']			=	$bean->FirstName.' '.$bean->LastName;
		$merchantArray['Address']			=	$bean->Address;
		if(isset($bean->AccountNumber))
			$merchantArray['AccountNumber']		=	$bean->AccountNumber;
		if(isset($bean->SortCode))
			$merchantArray['SortCode']			=	$bean->SortCode;
		if(isset($bean->BankType))
			$merchantArray['BankType']			=	$bean->BankType;
		if(isset($bean->IBAN))
			$merchantArray['IBAN']				=	$bean->IBAN;
		if(isset($bean->BIC))
			$merchantArray['BIC']				=	$bean->BIC;
		if(isset($bean->ABA))
			$merchantArray['ABA']				=	$bean->ABA;
		if(isset($bean->BankName))
			$merchantArray['BankName']			=	$bean->BankName;
		if(isset($bean->InstitutionNumber))
			$merchantArray['InstitutionNumber']	=	$bean->InstitutionNumber;
		if(isset($bean->BranchCode))
			$merchantArray['BranchCode']		=	$bean->BranchCode;
		if(isset($bean->Country))
			$merchantArray['Country']			=	$bean->Country;
		$merchantArray['MangoPayId']			=	$bean->MangoPayId;
		
		if($merchantArray['BankType'] == 'Iban')
			$condition			=	' IBAN = "'.$merchantArray['IBAN'].'"';
		else
			$condition			=	' AccountNumber = "'.$merchantArray['AccountNumber'].'"';
		$sql 					=	"SELECT id FROM `bankaccountdetails` WHERE 1 AND fkMerchantsId ='".$merchantsId."' AND $condition and Status = 1";
		$getBankAccountDetails 	=	R::getAll($sql);
		if(empty($getBankAccountDetails)){
			$logStart				=	microtime(true);
			$mangopayDetails 		=   createBankAccount($merchantArray);
			//MangoPay Log
			$logArray				=	Array();	
			$logArray['MerchantId']	=	$merchantsId;
			$logArray['URL']		=	'createBankAccount';
			$logArray['Content']	=	$merchantArray;
			$logArray['Start']		=	$logStart;
			$logArray['End']		=	microtime(true);
			$logArray['Response']	=	$mangopayDetails;
			$log 	=	R::dispense('users');
			$log->storeMangoPayLog($logArray);
			
			if(isset($mangopayDetails) && count($mangopayDetails) > 0 && isset($mangopayDetails->Id)){
				$bankAccountDetails						= 	R::dispense('bankaccountdetails');
				$bankAccountDetails->fkMerchantsId		= 	$merchantsId;
				$bankAccountDetails->fkMangopayId		=	$mangopayDetails->UserId;
				$bankAccountDetails->BankType			=	$mangopayDetails->Type;
				$bankAccountDetails->OwnerName			=	$mangopayDetails->OwnerName;
				$bankAccountDetails->OwnerAddress		=	$mangopayDetails->OwnerAddress;
				if(isset($merchantArray['AccountNumber']))
					$bankAccountDetails->AccountNumber		=	$merchantArray['AccountNumber'];
				if(isset($merchantArray['SortCode']))
					$bankAccountDetails->SortCode			=	$merchantArray['SortCode'];
				if(isset($merchantArray['BankName']))
					$bankAccountDetails->BankName			=	$merchantArray['BankName'];
				if(isset($merchantArray['InstitutionNumber']))
					$bankAccountDetails->InstitutionNumber	=	$merchantArray['InstitutionNumber'];
				if(isset($merchantArray['BranchCode']))
					$bankAccountDetails->BranchCode			=	$merchantArray['BranchCode'];
				if(isset($merchantArray['ABA']))
					$bankAccountDetails->ABA				=	$merchantArray['ABA'];
				if(isset($merchantArray['IBAN']))
					$bankAccountDetails->IBAN				=	$merchantArray['IBAN'];
				if(isset($merchantArray['BIC']))
					$bankAccountDetails->BIC				=	$merchantArray['BIC'];
				if(isset($merchantArray['Country']))
					$bankAccountDetails->Country			=	$merchantArray['Country'];
				$bankAccountDetails->AccountId			=	$mangopayDetails->Id;
				$bankAccountDetails->Status				=	'1';
				$bankAccountDetails->DateCreated		=	date('Y-m-d h:i:s');
				$bankAccountDetails->DateModified		=	$bankAccountDetails->DateCreated;
				$merchantsUpdate						= 	R::store($bankAccountDetails);
				return $mangopayDetails;
			} else {
				throw new ApiException("Please provide a valid bank account details." ,  ErrorCodeType::SomeFieldsRequired);
			}
		}else {
				throw new ApiException("Account already exist in mangopay." ,  ErrorCodeType::SomeFieldsRequired);
			}
	}
		/**
    * Validate MangoPay params
    * @throws ApiException if the models fails to validate
    */
	public function validateMangoPayBank($type='')
    {
		$bean = $this->bean;
		if($type==1){
			$rules = [
	            'required' => [
	                 ['BankAccountId'],['WalletId'],['MangoPayId'],['Amount']
	            ],
				
	        ];
		}else if($type == 'Iban'){
			$rules = [
	            'required' => [
	                 ['FirstName'],['LastName'],['Address'],['IBAN'],['BIC'],['MangoPayId']
	            ],
				
	        ];
		}else if($type == 'Gb'){
			$rules = [
	            'required' => [
	                ['FirstName'],['LastName'],['Address'],['AccountNumber'],['SortCode'],['MangoPayId']
	            ],
				
	        ];
		}else if($type == 'Us'){
			$rules = [
	            'required' => [
	                ['FirstName'],['LastName'],['Address'],['AccountNumber'],['ABA'],['MangoPayId']
	            ],
				
	        ];
		}else if($type == 'Ca'){
			$rules = [
	            'required' => [
	                ['FirstName'],['LastName'],['Address'],['AccountNumber'],['BranchCode'],['BankName'],['InstitutionNumber'],['MangoPayId']
	            ],
				
	        ];
		}else if($type == 'Other'){
			$rules = [
	            'required' => [
	                ['FirstName'],['LastName'],['Address'],['AccountNumber'],['BIC'],['Country'],['MangoPayId']
	            ],
				
	        ];
		}
		else{
		  	$rules = [
	            'required' => [
	                 ['FirstName'],['LastName'],['Address'],['AccountNumber'],['SortCode'],['MangoPayId']
	            ],
				
	        ];
		}
        $v = new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
			// the action was not found
            throw new ApiException("Please check the mango pay bank properties. Fill the properties with correct values" ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
    }
	/**
    * get demographics
    */
    public function getDemographics($merchantId)
    {
		$condition 		= $field = $time_zone = '';
		$bean 			= 	$this->bean;
		$this->validateMerchants($merchantId,1);
		if($bean->TimeZone)
		$time_zone 		= 	$bean->TimeZone;
		$dataType 		= 	$bean->DateType;
		$curr_date 		= 	date('d-m-Y');
		$cur_month 		= 	date('m');
		$cur_year 		= 	date('Y');
		if($dataType=='day') {
			$condition .= 	" and date(DATE_ADD(OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE))='".date('Y-m-d',strtotime($curr_date))."'";
		}
		 else if($dataType=='7days') {
			$condition 		.= 	"and (DATE_FORMAT(OrderDate,'%Y-%m-%d') <= '".date('Y-m-d',strtotime($curr_date))."' and DATE_FORMAT(OrderDate,'%Y-%m-%d') > '".date('Y-m-d',strtotime("-7 days"))."')";
		}else if($dataType=='month') {
			$condition .= 	"and DATE_FORMAT(OrderDate,'%m') = ".$cur_month." and DATE_FORMAT(OrderDate,'%Y') = ".$cur_year."";
		}
		else if($dataType=='year') {
			$condition 	.=	 "  and DATE_FORMAT(OrderDate,'%Y') = ".$cur_year."";
		}
		$gender_sql 				= 	"Select CASE WHEN Gender = 0 THEN '0' WHEN Gender = 1 THEN '1' WHEN Gender = 2 THEN '2' END AS Gendergroup, count( Gender ) AS total 
								 from users as u 
								 left join `orders` as o ON (o.fkUsersId = u.id and o.OrderStatus IN (1)) 
								 left join merchants as m on (m.id = o.fkMerchantsId) where 1 and o.fkMerchantsId = ".$merchantId." ".$condition."
								 group by Gendergroup order by Gendergroup desc ";
	    $GenderList 		=	R::getAll($gender_sql);
		$age_sql					=	"Select CASE WHEN Age <= 18 THEN '1' WHEN Age BETWEEN 19 AND 24 THEN '2' WHEN Age BETWEEN 25 AND 34 THEN '3' 
		   							WHEN Age BETWEEN 35 AND 44 THEN '4' WHEN Age >44 THEN '5' END AS Agegroup, count( Age ) AS total 
									from users as u 
									left join `orders` as o ON (o.fkUsersId = u.id and o.OrderStatus IN (1)) 
									left join merchants as m on (m.id = o.fkMerchantsId) where 1 and o.fkMerchantsId =  ".$merchantId." ".$condition." group by Agegroup order by Agegroup asc ";
		$AgeList 		=	R::getAll($age_sql);
		if($GenderList ){
			$DemographicsList['GenderList'] = $GenderList;
		}
		if($AgeList ){
			$DemographicsList['AgeList']    = $AgeList;
		}
		if(!empty($DemographicsList)){
			return $DemographicsList;
		}
		else{
			/**
	        * throwing error when no data found
	        */
			throw new ApiException("No results Found", ErrorCodeType::NoResultFound);
		}
	}
	/**
    * get Product Customer List
    */
	public function getProductCustomerList($merchantId)
    {
		/**
        * Query to get customer list
        */
		$d 			=	 0;
		$condition 	=  	 $having_condition = $userName = $visitCount = $totalSpend =$time_zone =  '';
		$userIDs	=	$CustomerListArray	=	Array();
		$bean 		=	 $this->bean;
		$this->validateMerchants($merchantId,1);
		
		if(isset($bean->SearchText) && !empty($bean->SearchText)) {
			$condition .= " and (u.FirstName like '%".$bean->SearchText."%' or u.LastName like '%".$bean->SearchText."%')";
		}
		
		if($bean->Type == 1){
			$start 		= 	0;
			$limit		= 	5;
			$condition 	.= " and o.OrderStatus IN(1) ";
			$orderby = 'TotalOrders desc,o.OrderDate desc';
		}
		else{
			$orderby = 'LastVisit  desc';
			$condition 	.= " and o.OrderStatus IN(1)";
		}
		if($bean->Type != 1){
			if($bean->Start)					
				$start 			= 	$bean->Start;
			else
				$start = 0;
			if($bean->Limit)	
				$limit 			= 	$bean->Limit;
			else
				$limit = 10;
		}
		if($bean->TimeZone)
		$time_zone 		= 	$bean->TimeZone;
		$dataType 		= 	$bean->DataType;
		$curr_date 		= 	date('d-m-Y');
		$cur_month 		= 	date('m');
		$cur_year 		= 	date('Y');
		if($dataType=='day') {
			$having_condition 		.= 	" Having date(DATE_ADD(LastVisit,INTERVAL '".$time_zone."' HOUR_MINUTE))='".date('Y-m-d',strtotime($curr_date))."'";
		}else if($dataType=='7days') {
			$having_condition 		.= 	" Having date(DATE_ADD(LastVisit,INTERVAL '".$time_zone."' HOUR_MINUTE)) <= '".date('Y-m-d',strtotime($curr_date))."' and date(DATE_ADD(LastVisit,INTERVAL '".$time_zone."' HOUR_MINUTE)) > '".date('Y-m-d',strtotime("-7 days"))."'";
		}else if($dataType=='month') {
			$having_condition 		.= 	" Having month(DATE_ADD(LastVisit,INTERVAL '".$time_zone."' HOUR_MINUTE)) = ".$cur_month." and year(DATE_ADD(LastVisit,INTERVAL '".$time_zone."' HOUR_MINUTE)) = ".$cur_year."";
		}else if($dataType=='year') {
			$having_condition 		.=	"  Having year(DATE_ADD(LastVisit,INTERVAL '".$time_zone."' HOUR_MINUTE)) = ".$cur_year."";
		}
		$sql 			= 	"SELECT SQL_CALC_FOUND_ROWS u.id as userId,u.FirstName,u.LastName,u.Photo,MAX(o.OrderDate) as LastVisit,
							MIN(o.OrderDate) as FirstVisit,COUNT(o.id) as TotalOrders,SUM(TotalPrice) as TotalPrice from orders as o 
							LEFT JOIN users as u ON(u.id = o.fkUsersId)							
							where 1 ".$condition."  and u.Status =1 and o.TransactionId != '' and o.Status = 1 and o.fkMerchantsId = ".$merchantId." 
							GROUP BY u.id ".$having_condition." order by ".$orderby." limit $start,$limit";	
		//echo $sql;
		$CustomerList 	=	R::getAll($sql);
		$totalRec 		= 	R::getAll('SELECT FOUND_ROWS() as count ');
		
		$total 			= 	(integer)$totalRec[0]['count'];
		$listedCount	= 	count($CustomerList);
		if($CustomerList){
			foreach($CustomerList as $key=>$value){
				$imagePath 		= $originalPath = $comments = '';
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
				$CustomerListArray[$value["userId"]] 			= 	$value;
				$userIDs[]						=	$value["userId"];
			}
			if(count($userIDs) > 0 && count($CustomerListArray) > 0) {
				$sql = "SELECT id as CommentId,fkUsersId as UserId,count(id) as TotalCount FROM comments WHERE 1 and fkUsersId in (".implode(',',$userIDs).") and fkMerchantsId=".$merchantId." and Status=1 group by fkUsersId";
				$CommentsList 	=	R::getAll($sql);
				if($CommentsList) {
					foreach($CommentsList as $key=>$value){
						$CustomerListArray[$value["UserId"]]['TotalComment'] = $value["TotalCount"];
					}
				}
			}
			$CustomerListArray['result'] 		= 	array_values($CustomerListArray);
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
	
	/**
    * get Customer Overview
    */
	public function getCustomerOverview()
    {
		$bean 			=	$this->bean;
		$merchantId		=	$bean->MerchantId;
		
		$this->validateMerchants($merchantId,1);
		
		$DataType 		=	'day';
		$time_zone 		=	$condition = $groupby = $having = '';
		global $month_name;
		$totalOrders	=	$totalCustomers	=	$averagevisit	=	0;
		
		if(isset($bean->DataType) && !empty($bean->DataType))  		$DataType	=	$bean->DataType;
		if(isset($bean->TimeZone) && !empty($bean->TimeZone))		$time_zone 	= 	$bean->TimeZone;
		
		$curr_date 		= 	date('Y-m-d');
		$cur_month 		= 	date('m');
		$cur_year 		= 	date('Y');
		
		$fields			=	" o.fkUsersId as UserId, count( o.id ) AS TotalOrders,  sum(o.TotalPrice) as TotalAmount ";
		$leftjoin		=	" left join users as u on (o.fkUsersId = u.id) ";
		$chkcondition  	=	" and o.OrderStatus = 1 and o.TransactionId != '' and o.Status = 1 and u.Status = 1 and o.fkMerchantsId = ".$merchantId;
		$groupby		=	" group by date(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) ";
		if($DataType == 'day') {
			$fields		.=	" , max(date(o.OrderDate)) as OrderDate ";
			$condition  .= 	" and date(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) = '".$curr_date."'";
			$having  	.= 	"  and max(date(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE))) = '".$curr_date."'";
		} else if($DataType == '7days') {
			$fields		.=	" , max(date(o.OrderDate)) as OrderDate ";
			$condition  .= 	" and date(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) <= '".$curr_date."' and date(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) >= '".date('Y-m-d',strtotime("-7 days"))."' ";
			$having  	.= 	"  and max(date(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE))) <= '".$curr_date."' and max(date(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE))) >= '".date('Y-m-d',strtotime("-7 days"))."' ";
		}else if($DataType == 'month') {
			$fields		.=	" , max(date(o.OrderDate)) as OrderDate ";
			$condition  .= 	" and month(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) = '".$cur_month."' and year(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) = '".$cur_year."' ";
			$having  	.= 	"  and max(month(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE))) = '".$cur_month."' and max(year(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE))) = '".$cur_year."' ";
		}else if($DataType == 'year') {	
			$fields		.=	" , max(month(o.OrderDate)) as OrderDate ";
			$condition  .= 	" and year(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) = '".$cur_year."'";
			$having  	.= 	"  and max(year(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE))) = '".$cur_year."'";
			$groupby	=	" group by year(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)),month(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) ";
		}
		
		$newCustomerIds = $oldCustomerIds = $outArray = $response = Array();
		$newcount		=	$oldcount	=	$newTot = $oldTot = 0;
		$sql	=	"SELECT ".$fields." FROM orders as o ".$leftjoin." WHERE 1	".$chkcondition." group by o.fkUsersId  having TotalOrders = 1 ".$having."
					union all
					SELECT ".$fields." FROM orders as o ".$leftjoin." WHERE 1	".$chkcondition." group by o.fkUsersId  having TotalOrders > 1 ".$having;
		//echo $sql;
		$result	=	R::getAll($sql);
		if($result) {	
			foreach($result as $val) {
				if($val['TotalOrders'] == 1)					
					$newCustomerIds[]	=	$val['UserId'];	
				else
					$oldCustomerIds[]	=	$val['UserId'];	
			}
			$newcount	=	count($newCustomerIds);
			$oldcount	=	count($oldCustomerIds);
			$outArray['NewCustomers']['Visits']		=	$newcount;
			$outArray['NewCustomers']['Sales']		=	0;
			$outArray['RepeatCustomers']['Visits']	=	$oldcount;
			$outArray['RepeatCustomers']['Sales']	=	0;
			$totalCustomers		=	$newcount + $oldcount;
			if($newcount > 0) {
				$sql		=	"SELECT ".$fields." FROM orders as o ".$leftjoin." WHERE 1	".$chkcondition." ".$condition." and u.id in (".implode(',',$newCustomerIds).") ".$groupby.",u.id";
				$newresult	=	R::getAll($sql);
				$temp		=	$summary = Array();
				foreach($newresult as $val) {
					$totalOrders	=	$totalOrders	+ $val['TotalOrders'];
					$newTot	=	$newTot + round($val['TotalAmount'],2);
					$summary[$val['OrderDate']]['Date']			=	$val['OrderDate'];
					
					if($DataType != 'year') {
						$dateString 	= $val['OrderDate'];
						$myDate 		= new DateTime($dateString);
						$formattedDate 	= $myDate->format('d M Y');
						$summary[$val['OrderDate']]['DisplayDate']	=	$formattedDate;
					} else
						$summary[$val['OrderDate']]['DisplayDate']	=	$month_name[$val['OrderDate']];
					
					//calculating Visits
					if(isset($summary[$val['OrderDate']]['Visits']))
						$summary[$val['OrderDate']]['Visits']	=	$summary[$val['OrderDate']]['Visits'] + 1;
					else
						$summary[$val['OrderDate']]['Visits']	=	1;
					
					//calculating Sales
					if(isset($summary[$val['OrderDate']]['Sales']))
						$summary[$val['OrderDate']]['Sales']		=	round($summary[$val['OrderDate']]['Sales'] + $val['TotalAmount'],2);
					else
						$summary[$val['OrderDate']]['Sales']		=	round($val['TotalAmount'],2);
				}
				$outArray['NewCustomers']['Sales']				=	round($newTot,2);
				$outArray['NewCustomers']['Percentage']			=	round(($newcount/($newcount + $oldcount)) * 100,2);
				if($newTot > 0 && $newcount > 0)
					$outArray['NewCustomers']['AverageSales']	=	round($newTot/$newcount,2);
				else 
					$outArray['NewCustomers']['AverageSales']	=	0;
				
				if(isset($summary) && count($summary) > 0) {
					foreach($summary as $key=>$val) {
						$summary[$key]['AverageSales'] 		= 	round($val['Sales'] / $val['Visits'],2)	;
					}
				}
				$outArray['NewCustomers']['Summary']				=	array_values($summary);
			}
			
			if($oldcount > 0) {
				$sql		=	"SELECT ".$fields." FROM orders as o ".$leftjoin." WHERE 1	".$chkcondition." ".$condition." and u.id in (".implode(',',$oldCustomerIds).") ".$groupby.",u.id";
				$oldresult	=	R::getAll($sql);
				$summary 	= 	Array();
				foreach($oldresult as $val) {
					$totalOrders	=	$totalOrders	+ $val['TotalOrders'];
					$oldTot	=	$oldTot + round($val['TotalAmount'],2);
					$summary[$val['OrderDate']]['Date']			=	$val['OrderDate'];
					
					if($DataType != 'year') {
						$dateString 	= $val['OrderDate'];
						$myDate 		= new DateTime($dateString);
						$formattedDate 	= $myDate->format('d M Y');					
						$summary[$val['OrderDate']]['DisplayDate']	=	$formattedDate;					
					} else
						$summary[$val['OrderDate']]['DisplayDate']	=	$month_name[$val['OrderDate']];
					
					//calculating Visits
					if(isset($summary[$val['OrderDate']]['Visits']))
						$summary[$val['OrderDate']]['Visits']	=	$summary[$val['OrderDate']]['Visits'] + 1;
					else
						$summary[$val['OrderDate']]['Visits']	=	1;
					
					//calculating Sales
					if(isset($summary[$val['OrderDate']]['Sales']))
						$summary[$val['OrderDate']]['Sales']		=	round($summary[$val['OrderDate']]['Sales'] + $val['TotalAmount'],2);
					else
						$summary[$val['OrderDate']]['Sales']		=	round($val['TotalAmount'],2);
				}
				$outArray['RepeatCustomers']['Sales']				=	round($oldTot,2);
				$outArray['RepeatCustomers']['Percentage']			=	round(($oldcount/($newcount + $oldcount)) * 100,2);
				if($oldTot > 0 && $oldcount > 0)
					$outArray['RepeatCustomers']['AverageSales']	=	round($oldTot/$oldcount,2);
				else 
					$outArray['RepeatCustomers']['AverageSales']	=	0;
				
				if(isset($summary) && count($summary) > 0) {
					foreach($summary as $key=>$val) {
						$summary[$key]['AverageSales'] 				= 	round($val['Sales'] / $val['Visits'],2);
					}
				}
				
				$outArray['RepeatCustomers']['Summary']				=	array_values($summary);
			}

			$response['result'] 		= $outArray;
			$response['spentpervisit'] 	= round(($newTot+$oldTot)/($newcount + $oldcount),2);
			$response['averagevisit']	=	0;
			
			$condition  = 	" and date(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) <= '".$curr_date."' and date(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) >= '".date('Y-m-d',strtotime("-30 days"))."' ";
			$sql		=	"SELECT count(o.id) AS TotalOrders, count( DISTINCT fkUsersId) AS TotalUser FROM orders as o  WHERE o.fkMerchantsId =".$merchantId." AND o.TransactionId != '' AND o.OrderStatus =1 ".$condition;
			//echo $sql;
			$pervisit	=	R::getAll($sql);
			if(isset($pervisit[0]['TotalUser']) && $pervisit[0]['TotalUser'] > 0 && isset($pervisit[0]['TotalOrders']) && $pervisit[0]['TotalOrders'] > 0)
				$response['averagevisit'] 	= round(($pervisit[0]['TotalOrders']/$pervisit[0]['TotalUser']));
			return $response;
		}		
	}
	
	/**
    * get Customer performance 
    */
	public function getCustomerPerformance()
    {
		$bean 			=	$this->bean;
		$merchantId		=	$bean->MerchantId;
		$yrcondition	=	$mncondition	=	$condition = $time_zone = '';
		if(isset($bean->TimeZone) && !empty($bean->TimeZone))		$time_zone 	= 	$bean->TimeZone;
		$curr_date 		= 	date('Y-m-d');
		$cur_month 		= 	date('m');	
		$cur_year 		= 	$last_year	=	date('Y');
		$mncondition	.= 	" and month(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) in (".$cur_month;
		$yrcondition	.= 	" and year(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) in (".$cur_year;
		if($cur_month == 1) {
			$last_month = 	'12';
			$last_year	=	$cur_year - 1;			
			$yrcondition	.=	",".$last_year.") ";
		}
		else {
			$last_month = 	$cur_month - 1;
			$yrcondition	.=	") ";
		}		
		
		$mncondition	.=	",".$last_month.") ";
		$condition		.=	$mncondition.' '.$yrcondition;

		$sql	=	"SELECT  count(distinct u.id) as totalcustomers, date(o.OrderDate) as OrderDate, month( o.OrderDate ) AS OrderMonth  FROM orders as o  
					left join users as u on (o.fkUsersId = u.id)  
					WHERE 1 and o.OrderStatus in (1) and o.Status = 1 and u.Status = 1 and o.fkMerchantsId = ".$merchantId."
					".$condition." group by date(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE))";
		$result	=	R::getAll($sql);
		if($result) {
			$currentmonth 	= $lastmonth 	= 	$currentweek = $lastweek = $outArray = $last30 = Array();
			$currenttot		= $lasttot		=	$currentweektot		= $lastweektot	=	$last30tot = 0;
			
			//Calculating dates for processing
			$cur_week_start = date('Y-m-d',strtotime("-6 days"));
			$cur_week_end 	= $curr_date;			
			$last_week_start= date('Y-m-d',strtotime("-13 days"));
			$last_week_end 	= date('Y-m-d',strtotime("-7 days"));
			$last30_start 	= date('Y-m-d',strtotime("-29 days"));
			$last30_end 	= $curr_date;

			//Separating the data's to weeks,months
			foreach($result as $val) {
				if($last_month == $val['OrderMonth']) {
					$lastmonth[] = $val;
					$lasttot	=	$lasttot + $val['totalcustomers'];
				}
				if($cur_month == $val['OrderMonth']) {
					$currentmonth[] = $val;
					$currenttot	=	$currenttot + $val['totalcustomers'];
				}
				if($val['OrderDate'] >= $cur_week_start && $val['OrderDate'] <= $cur_week_end) {
					$currentweek[]	=	$val;
					$currentweektot	=	$currentweektot + $val['totalcustomers'];
				}
				if($val['OrderDate'] >= $last30_start && $val['OrderDate'] <= $last30_end) {
					$last30[]		=	$val;
					$last30tot		=	$last30tot + $val['totalcustomers'];
				}
				if($val['OrderDate'] >= $last_week_start && $val['OrderDate'] <= $last_week_end) {
					$lastweek[]	=	$val;
					$lastweektot	=	$lastweektot + $val['totalcustomers'];
				}
			}

			//Forming response data
			$outArray['PercentageGraphic']['Week']				=	Array();
			$outArray['PercentageGraphic']['Month']				=	Array();
			
			$outArray['Purchases']['Week']['CurrentPercentage'] 	= 	0;
			if($currentweektot > 0 && ($currentweektot + $lastweektot) > 0)
				$outArray['Purchases']['Week']['CurrentPercentage'] = 	round($currentweektot/($currentweektot + $lastweektot) * 100, 2);
				
			$outArray['Purchases']['Week']['LastPercentage'] 		= 	0;
			if($lastweektot > 0 && ($currentweektot + $lastweektot) > 0)
				$outArray['Purchases']['Week']['LastPercentage'] 	= 	round($lastweektot/($currentweektot + $lastweektot) * 100, 2);
				
			$outArray['Purchases']['Month']['CurrentPercentage'] 	= 	0;
			if($currenttot > 0 && ($currenttot + $lasttot) > 0)
				$outArray['Purchases']['Month']['CurrentPercentage']= 	round($currenttot/($currenttot + $lasttot) * 100, 2);
				
			$outArray['Purchases']['Month']['LastPercentage'] 		= 	0;
			if($lasttot > 0 && ($currenttot + $lasttot) > 0)
				$outArray['Purchases']['Month']['LastPercentage'] 	= 	round($lasttot/($currenttot + $lasttot) * 100, 2);
				
			$outArray['Listing']['Last24HoursCustomers']		=	0;
			$outArray['Listing']['Last7DaysCustomers']			=	$currentweektot;
			$outArray['Listing']['Last30DaysCustomers']			=	$last30tot;
			$outArray['Listing']['ThisYearCustomers']			=	0;
			$outArray['Listing']['Last24Hours']					=	Array();
			$outArray['Listing']['Last7Days']					=	Array();
			$outArray['Listing']['Last30Days']					=	Array();
			$outArray['Listing']['ThisYear']					=	Array();
			
			
			
			//processing the data's - WEEK(percentage and purchase)
			$percentage = $purchasecurrent = $purchaselast = Array();
			if(count($currentweek) > 0) {
				foreach($currentweek as $key=>$val) {
					$percentage[$val['OrderDate']]['OrderDate'] 		= 	$val['OrderDate'];
					$percentage[$val['OrderDate']]['DisplayDate']		= 	date('l', strtotime($val['OrderDate']));
					$percentage[$val['OrderDate']]['Percentage'] 		= 	round(($val['totalcustomers'] / $currentweektot) * 100,2);
					
					$purchasecurrent[$val['OrderDate']]['OrderDate'] 	= 	$val['OrderDate'];
					$purchasecurrent[$val['OrderDate']]['DisplayDate']	= 	date('D', strtotime($val['OrderDate']));
					$purchasecurrent[$val['OrderDate']]['Customers'] 	= 	$val['totalcustomers'];					
				}				
			}			
			$outArray['PercentageGraphic']['Week']		=	array_values($percentage);
			$outArray['Purchases']['Week']['Current']	=	array_values($purchasecurrent);
			$outArray['Listing']['Last7Days']			=	array_values($purchasecurrent);
			
			//processing the data's - MONTH(percentage and purchase)
			$percentage = $purchasecurrent = $purchaselast = Array();
			if(count($currentmonth) > 0) {				
				foreach($currentmonth as $key=>$val) {
					$myDate 	= new DateTime($val['OrderDate']);
					$percentage[$val['OrderDate']]['OrderDate'] 	= $val['OrderDate'];					
					$percentage[$val['OrderDate']]['DisplayDate'] 	= $myDate->format('d M');
					$percentage[$val['OrderDate']]['Percentage'] 	= round(($val['totalcustomers'] / $currenttot) * 100,2);
					
					$purchasecurrent[$val['OrderDate']]['OrderDate'] 	= $val['OrderDate'];
					$purchasecurrent[$val['OrderDate']]['DisplayDate']	= $myDate->format('d');
					$purchasecurrent[$val['OrderDate']]['Customers'] 	= $val['totalcustomers'];				
				}
			}
			$outArray['PercentageGraphic']['Month']		=	array_values($percentage);
			$outArray['Purchases']['Month']['Current']	=	array_values($purchasecurrent);
			
			//Processing the data's - LAST 30 DAYS
			$temp = Array();
			if(count($last30) > 0) {				
				foreach($last30 as $key=>$val) {
					$myDate 	= new DateTime($val['OrderDate']);
					$temp[$val['OrderDate']]['OrderDate'] 		= 	$val['OrderDate'];					
					$temp[$val['OrderDate']]['DisplayDate'] 	= 	$myDate->format('d M');
					$temp[$val['OrderDate']]['Customers'] 		= 	$val['totalcustomers'];				
				}
			}
			$outArray['Listing']['Last30Days']					=	array_values($temp);		
			
			//processing the data's - LAST WEEK
			$purchaselast = Array();
			if(count($lastweek) > 0) {				
				foreach($lastweek as $key=>$val) {
					$purchaselast[$val['OrderDate']]['OrderDate'] 	= 	$val['OrderDate'];
					$purchaselast[$val['OrderDate']]['DisplayDate']	= 	date('D', strtotime($val['OrderDate']));
					$purchaselast[$val['OrderDate']]['Customers'] 	= 	$val['totalcustomers'];
				}				
			}
			$outArray['Purchases']['Week']['Last']					=	array_values($purchaselast);
			
			//processing the data's - LAST MONTH
			$purchaselast = Array();
			if(count($lastmonth) > 0) {				
				foreach($lastmonth as $key=>$val) {
					$myDate 	= new DateTime($val['OrderDate']);
					$purchaselast[$val['OrderDate']]['OrderDate'] 	= 	$val['OrderDate'];					
					$purchaselast[$val['OrderDate']]['DisplayDate'] = 	$myDate->format('d');
					$purchaselast[$val['OrderDate']]['Customers'] 	= 	$val['totalcustomers'];
				}				
			}
			$outArray['Purchases']['Month']['Last']					=	array_values($purchaselast);			
			
			//processing - Last 24 hours
			$todaydata 	= $temp = Array();
			$total 		= 0;
			$sql = "SELECT  count(distinct u.id) as totalcustomers, o.OrderDate as OrderDate, hour( o.OrderDate ) AS OrderHour  FROM orders as o  
					left join users as u on (o.fkUsersId = u.id)  
					WHERE 1 and o.OrderStatus in (1) and o.Status = 1 and u.Status = 1 and o.fkMerchantsId = ".$merchantId."
					and date(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) >= NOW() - INTERVAL 1 DAY  group by hour(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE))";
			$temp	=	R::getAll($sql);
			if($temp) {				
				foreach($temp as $key=>$val) {
					$total =	$total + $val['totalcustomers'];
					$todaydata[$val['OrderHour']]['OrderDate'] 		= 	$val['OrderDate'];
					$todaydata[$val['OrderHour']]['Hour'] 		= 	$val['OrderHour'];
					$todaydata[$val['OrderHour']]['Customers'] 	= 	$val['totalcustomers'];					
				}
			}
			$outArray['Listing']['Last24HoursCustomers']		=	$total;
			$outArray['Listing']['Last24Hours']					=	array_values($todaydata);
			
			//processing - Current Year by month
			$yeardata 	= $temp = Array();
			$total 		= 0;
			$sql = "SELECT  count(distinct u.id) as totalcustomers, date(o.OrderDate) as OrderDate, month( o.OrderDate ) AS OrderMonth  FROM orders as o  
					left join users as u on (o.fkUsersId = u.id)  
					WHERE 1 and o.OrderStatus in (1) and o.Status = 1 and u.Status = 1 and o.fkMerchantsId = ".$merchantId."
					and year(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) = ".$cur_year."  group by month(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE))";
			//echo $sql;
			$temp	=	R::getAll($sql);
			if($temp) {				
				foreach($temp as $key=>$val) {
					$total =	$total + $val['totalcustomers'];
					$yeardata[$val['OrderMonth']]['OrderDate'] 		= 	$val['OrderDate'];
					$yeardata[$val['OrderMonth']]['Month'] 		= 	$val['OrderMonth'];
					$yeardata[$val['OrderMonth']]['Customers'] 	= 	$val['totalcustomers'];
				}
			}
			$outArray['Listing']['ThisYearCustomers']			=	$total;
			$outArray['Listing']['ThisYear']					=	array_values($yeardata);		
			return $outArray;
		}
	}
	/*
	* get mangopay bank details
	*/
	public function getBankDetails()
    {
		/**
		* Get the bean
		* @var $bean merchants
		*/
        $bean = $this->bean;
		
		$sql 				=	"SELECT id,AccountId,AccountNumber,OwnerName,IBAN FROM `bankaccountdetails` WHERE 1 AND fkMerchantsId ='".$bean->merchantId."' and Status = 1";					
		$mangopayDetails 	=	R::getAll($sql);
		if($mangopayDetails) {
			return $mangopayDetails;
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
		$specials	= $regular	=	$counts 	=	array();
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
				if($val['ItemType'] == 3)
					$specials[]		=	$val;
			}
			$counts['Totalproducts']				=	count($regular) + count($specials);
			$counts['Regular']						=	count($regular);
			$counts['Specials']						=	count($specials);
			
			//getting how products to be discounted
			$discountMust	=	floor(((1/3) * $counts['Totalproducts']));
			$counts['DiscountProductMustBe']		=	$discountMust;
			
			$counts['RegularDiscountApplied']		=	$applied;
			if(isset($bean->Discount) && $bean->Discount == 0)
				$counts['RegularDiscountApplied']	=	$applied	-	1;
			$counts['RegularDiscountNotApplied']	=	$unapplied;		
			
			//getting total discounted products
			$disapplied		=	$counts['Specials'] + $counts['RegularDiscountApplied'];
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
				if(isset($bean->Type) && $bean->Type == 3) { } else {				
					/**
					 * throwing error when Merchant not having 1/3 of products discounted
					 */
					 throw new ApiException("Sorry! 1/3 of your products should be with discount", ErrorCodeType::ProductDiscountMust);
				 }
			}
			return  $counts;				
		} else {		
				/**
				* throwing error when no Products found
				*/
				throw new ApiException("No Products found", ErrorCodeType::NoResultFound);
		}
	}
	/**
    * Check Balance
    */
	public function checkWalletBalance(){
	 	/**
        * Get the bean
        * @var $bean Users
        */
		$bean 					= 	$this->bean;
		$this->validateBalanceParams();
		
		$logStart				=	microtime(true);
		$walletDetails			=	getWalletDetails($bean->WalletId);
		//MangoPay Log
		$logArray				=	Array();	
		$logArray['MerchantId']	=	$bean->MerchantId;
		$logArray['URL']		=	'getWalletDetails';
		$logArray['Content']	=	Array('WalletId'=>$bean->WalletId);
		$logArray['Start']		=	$logStart;
		$logArray['End']		=	microtime(true);
		$logArray['Response']	=	$walletDetails;
		$log 	=	R::dispense('users');
		$log->storeMangoPayLog($logArray);
		
		if(!empty($walletDetails))
			return $walletDetails;					
		else
			throw new ApiException("Sorry, user not connected with banking account", ErrorCodeType::NoResultFound);
	 }
	 /**
	* Validate the fields (checkBalance)
	*/
    public function validateBalanceParams()
    {
		$bean 	= 	$this->bean;
	  	$rules 	= 	[
						'required' => [
							 ['WalletId']
						]
					];
		
        $v 		= 	new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
			// check UsersId and PaymentAmount field
            throw new ApiException("Please check WalletId." ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
	}
	/*
	* Transfer money from wallet to bank
	*/
	public function transferMoneyToBank($merchantsId)
    {
		$bean 			=	 $this->bean;
		$merchantArray 	= 	 array();
		
		//Validate MangoPay params
		$this->validateMangoPayBank(1);
		$admin	= 	R::findOne('admins');
		$merchantArray['FeeAmount']			=	$admin->MangoPayFees;
		$merchantArray['MangoPayId']		=	$bean->MangoPayId;
		$merchantArray['BankAccountId']		=	$bean->BankAccountId;
		$merchantArray['WalletId']			=	$bean->WalletId;
		$merchantArray['Amount']			=	$bean->Amount;
		$logStart							=	microtime(true);
		$mangopayDetails 					=   payAmountToBank($merchantArray);
		
		//MangoPay Log
		$logArray				=	Array();	
		$logArray['MerchantId']	=	$merchantsId;
		$logArray['URL']		=	'payAmountToBank';
		$logArray['Content']	=	$merchantArray;
		$logArray['Start']		=	$logStart;
		$logArray['End']		=	microtime(true);
		$logArray['Response']	=	$mangopayDetails;
		$log 	=	R::dispense('users');
		$log->storeMangoPayLog($logArray);
		
		if(isset($mangopayDetails) && count($mangopayDetails) > 0 && isset($mangopayDetails->Id) ){
			if($mangopayDetails->ResultMessage != '' && $mangopayDetails->Status =='FAILED' )
				throw new ApiException($mangopayDetails->ResultMessage ,  ErrorCodeType::SomeFieldsRequired);
			else
				return $mangopayDetails;
		} else {
			throw new ApiException("Error in transfer amount from wallet to bank account" ,  ErrorCodeType::SomeFieldsRequired);
		}
	}
}
