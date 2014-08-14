<?php

/**
 * Description of Model_Merchants
 *
 * @author 
 */
 
require_once '../../lib/Model_Products.php'; 

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
class Model_Merchants extends RedBean_SimpleModel implements ModelBaseInterface {

    /**
     * Identifier
     * @var int
     */
    public $id;

    /**
     * Merchants email
     * @var string
     */
    public $Email;
	
	/**
     * Merchants fbid
     * @var string
     */
    public $FBId;
	
	/**
     * Merchants fbid
     * @var string
     */
    public $Latitude;
	
	/**
     * Merchants fbid
     * @var string
     */
    public $Longitude;
	
	/**
     * Merchants fbid
     * @var string
     */
    public $merchantIds;
	/**
     * Merchants GooglePlusId
     * @var string
     */
    public $GooglePlusId;
	
    /**
     * Person first name
     * @var string
     */
    public $FirstName;

    /**
     * Person last name
     * @var string
     */
    public $LastName;

    /**
     * When the record was created
     * @var int
     */
    public $DateCreated;

    /**
     * When the record was last updated
     * @var int
     */
    public $DateModified;
	
	/**
     * Person platform
     * @var string
     */
    public $Platform;
	
	/**
     * Person platform
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
         * @var Model_Merchantss
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
    public function create(){ // Tuplit merchant creating

		 
		 /**
         * Get the bean
         * @var $bean Model_Merchantss
         */
        $bean 		= $this->bean;
		
		// validate the model
        $this->validate();

        // validate the creation
        $this->validateCreate();
		
        $bean->DateCreated 			= date('Y-m-d H:i:s');
        $bean->DateModified 		= $bean->DateCreated;
		
		// encrypt the password
		if($bean->Password){
        	$bean->Password 		= PasswordHelper::encrypt($bean->Password);
		}
		//save ip address
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else{
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		$bean->IpAddress 			= $ip;
		$bean->Status 				= 0;
		
		// save the bean to the database
		$merchantsId = R::store($this);
	  	return $merchantsId;
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
	                 ['FirstName'],['LastName'],['Email'],['Password'],['CompanyName']
	            ],
				'Email' => 'Email',
	        ];
		}
		else{
			$rules = [
	            'required' => [
	                ['FirstName'],['LastName'],['Email'],['Password'],['CompanyName']
	            ],
				'Email' => 'Email',
	        ];
		}
		
        $v = new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
            throw new ApiException("Please check the merchant's properties. Fill FirstName,LastName,Email,Password,CompanyName with correct values" ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
    }
	/**
     * Validate the model
     * @throws ApiException if the models fails to validate required fields
     */
    public function validateModifyMerchant($iconExist,$merchantExist)
    {
		
		$bean = $this->bean;
		if($iconExist == '' && $merchantExist == '' ){
		    $rules = [
	            'required' => [
	                 ['CompanyName'],['Address'],['PhoneNumber'],['WebsiteUrl'],['DiscountTier'],['PriceRange'],['IconPhoto'],['MerchantPhoto']
	            ],
				'Url' => 'WebsiteUrl',
				'in' =>[
						['DiscountTier',['1','2','3','4','5','6']]
					]
	        ];
		}
		else if($iconExist == ''){
		    $rules = [
	            'required' => [
	                 ['CompanyName'],['Address'],['PhoneNumber'],['WebsiteUrl'],['DiscountTier'],['PriceRange'],['IconPhoto']
	            ],
				'Url' => 'WebsiteUrl',
				'in' =>[
						['DiscountTier',['1','2','3','4','5','6']]
					]
	        ];
		}
		else if($merchantExist == ''){
		    $rules = [
	            'required' => [
	                 ['CompanyName'],['Address'],['PhoneNumber'],['WebsiteUrl'],['DiscountTier'],['PriceRange'],['MerchantPhoto']
	            ],
				'Url' => 'WebsiteUrl',
				'in' =>[
						['DiscountTier',['1','2','3','4','5','6']]
					]
	        ];
		}
		else{
			$rules = [
	            'required' => [
	                 ['CompanyName'],['Address'],['PhoneNumber'],['WebsiteUrl'],['DiscountTier'],['PriceRange']
	            ],
				'Url' => 'WebsiteUrl',
				'in' =>[
						['DiscountTier',['1','2','3','4','5','6']]
					]
	        ];
		}
        $v = new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
            throw new ApiException("Please check the merchant's properties. Fill Company Name,Address,Phone Number,WebsiteUrl,Discount Tier,Price Range,Icon Photo,Merchant Photo with correct values" ,  ErrorCodeType::SomeFieldsRequired, $errors);//Email,
        }
    }
    /**
     * Validate the creation of an account
     * @throws ApiException if the user being creating the account with already exists of email , facebook and linked ids in the database.
     */
    public function validateCreate(){

        /**
         * Get the bean
         * @var $bean Model_Merchantss
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
	 * Throws exception for email , facebook , googlePlusId already exists condition
     */
    public function modify($merchantsId = null,$modify='',$iconExist='',$merchantExist=''){

		/**
         * Get the bean
         * @var $bean Model_Merchantss
         */
		$bean = $this->bean;
		//echo "<pre>"; print_r( $bean); echo "</pre>";
		//validate the model
        $this->validateMerchantParams();
		
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
     * Validate the modification of an account
     * @throws ApiException if the user being modifyng the account
     */
	public function validateMerchantParams(){
	
	}
	
	
	/**
     * Validate the modification of an account
     * @throws ApiException if the user being modifyng the account 
     */
	public function validateModify($merchantsId)
    {
       	/**
         * Get the bean
         * @var $bean Model_Merchantss
         */
        $bean = $this->bean;

        /**
         * Get the identity of the person making the change
         * @var $modifiedBy Model_Merchantss
         */
		$sql = "select id from merchants where id = '".$merchantsId."' and Status = 1 ";
        $modifiedBy = R::getAll($sql);
        if (!$modifiedBy) {
            // the Merchants was not found
            throw new ApiException("You have no access to edit the datas", ErrorCodeType::NotAccessToDoProcess);
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
     * @param Get user details
	 * validate Merchants who requesting to see his details
     */
    public function getMerchantsDetails($requestedBy){
		global	$discountTierArray;
		$fields	= $userId = $joinCondition	=  $latitude = $longitude ='';
		$productsArray = $friendsDetails = $friendsArray = $UserId = $FriendId = $FriendsList = $friendsListArray = $commentsArray = $productList =$categoriesArray = array();
		$bean = $this->bean;
		if($bean->From != '')
			$from = $bean->From;
		else
			$from = 1;	

		if($from == 1){
			//Validate latitude and longitude	
			$this->validateLatLong();
		
			$latitude		=	$bean->Latitude;			
			$longitude		=	$bean->Longitude;
		
			//validate merchant
			$this->validateMerchants($requestedBy,3);
			
			//Friends List
			if($bean->UserId != ''){
				$userId = $bean->UserId;
				$this->validateUser($userId);
				//Already Favouried
				$fields			= ",FavouriteType as AlreadyFavourited";
				$joinCondition	= "LEFT JOIN favorites as f ON(m.id = f.fkMerchantsId and f.fkMerchantsId = ".$requestedBy." and f.fkUsersId = ".$userId." and FavouriteType = 1) ";
 			}
			//Total users shopped		
			$totalOrders = 0;
			$orderedFriendsCount = 0;
			$friendsListArray = $friendsShopped = $userShopped = array();
			$orderSql 		= "SELECT fkUsersId FROM orders where fkMerchantsId=".$requestedBy." and OrderStatus = 1 and Status = 1 group by fkUsersId";
   			$OrdersCount 	= R::getAll($orderSql);
			if($OrdersCount){
				$totalOrders = count($OrdersCount);
				if($bean->UserId != ''){
					foreach($OrdersCount as $key=>$val){
						$userShopped[] = $val['fkUsersId'];
					}
					$userId = $bean->UserId;
					$this->validateUser($userId);
					$sql 			= "SELECT  group_concat(`fkFriendsId`,',', fkUsersId) as friendsId  FROM friends where  (fkUsersId = ".$userId." or fkFriendsId = ".$userId.") and Status = 1 ";
		   			$friends		= R::getAll($sql);
					if($friends){
						$friendsArray = array_unique(explode(',',$friends[0]['friendsId']));
						$result = array_intersect($userShopped, $friendsArray);
						if(is_array($result) && count($result) > 0){
							foreach($result as $kk=>$vv){
								$friendsShopped[] = $vv; 
							}
							$orderedFriendsCount = 0;
							$friendsIds = implode(',',$friendsShopped);
							$sql 			= "SELECT id,FirstName,LastName,Photo FROM users where id IN (".$friendsIds.") and Status = 1";
			   				$friendsArray 	= R::getAll($sql);
							
							if($friendsArray){
								$orderedFriendsCount  = count($friendsArray);
								foreach($friendsArray as $key => $value){
									$user_image_path = '';
									if(isset($value['Photo']) && $value['Photo'] != ''){
										$user_image_path = USER_IMAGE_PATH.$value['Photo'];
									}
									$value['Photo'] 				= $user_image_path;
									$friendsListArray[]				= $value;
									if($key >=2)
										break;
								}
							}
						}
					}
				}
			}
			//merchant Comments
			$commentFields 	= ' c.id as CommentId,c.fkUsersId as UsersId,c.CommentsText,c.CommentDate,c.Platform,u.FirstName,u.LastName,u.Photo ';
			$commentJoin 	= ' LEFT JOIN users u ON c.fkUsersId = u.id '; 			
			$commentSql 	= "SELECT ".$commentFields." from comments c ".$commentJoin." where c.Status = 1 and c.fkMerchantsId=".$requestedBy." order by c.CommentDate desc limit 0 , 3";
			$comments 		= R::getAll($commentSql);
			if($comments){
				foreach($comments as $key => $value){
					$commentsArray[$key]['UserId'] 			= $value['UsersId'];
					$commentsArray[$key]['FirstName']		= $value['FirstName'];
					$commentsArray[$key]['LastName'] 		= $value['LastName'];
					$commentsArray[$key]['Photo'] 			= USER_IMAGE_PATH.$value['Photo'];
					$commentsArray[$key]['CommentsText'] 	= getCommentTextEmoji($bean->Platform,$value['CommentsText'],$value['Platform']);
					$commentsArray[$key]['CommentDate'] 	= $value['CommentDate'];
				}
			}
			$productsArray = array();
			$productDetail 	= new Model_Products();
			//$productList 	=  $productDetail->getProductList($requestedBy,1);//1-mobile
			$productList 	=  $productDetail->getProductListWithCategory($requestedBy,null,1);//1-mobile
		}
		// Merchant details
		if(!empty($latitude) && !empty($longitude)) {
			$sql 	= "SELECT m.id,m.id as MerchantId,m.FirstName,m.LastName,m.Email,m.CompanyName,m.Address,m.Location,m.Latitude,
					  m.Longitude,m.PhoneNumber,m.WebsiteUrl,m.Icon,m.Image,m.Description,m.ShortDescription,
					  (((acos(sin((".$latitude."*pi()/180)) * sin((m.`Latitude`*pi()/180))+cos((".$latitude."*pi()/180)) * cos((m.`Latitude`*pi()/180)) * cos(((".$longitude."- m.`Longitude`)*pi()/180))))*180/pi())*60*1.1515)*1.6093 as distance,
                      m.DiscountTier, m.DiscountType, m.DiscountProductId,m.SpecialIcon,m.ItemsSold,m.OpeningHours,m.PriceRange,m.SpecialsSold".$fields." 
					  FROM merchants as m	".$joinCondition."	where m.Status = 1 and m.id=".$requestedBy." ";
		}
		else {
			$sql 	= "SELECT m.id,m.id as MerchantId,m.FirstName,m.LastName,m.Email,m.CompanyName,m.Address,m.Location,m.Latitude,
					  m.Longitude,m.PhoneNumber,m.WebsiteUrl,m.Icon,m.Image,m.Description,m.ShortDescription,
                      m.DiscountTier, m.DiscountType, m.DiscountProductId,m.SpecialIcon,m.ItemsSold,m.OpeningHours,m.PriceRange,m.SpecialsSold".$fields." 
					  FROM merchants as m	".$joinCondition."	where m.Status = 1 and m.id=".$requestedBy." ";
		}		
		$merchants 		= R::getAll($sql);
		// Merchant categories
		$sql 			= "select group_concat(`fkCategoriesId` separator ',') as  catId FROM merchantcategories where fkMerchantId=".$requestedBy;
   		$categories 	= R::getAll($sql);
		
		// Merchant OpeningHours
		$sql1 			= "select * from merchantshoppinghours where fkMerchantId=".$requestedBy;
   		$openingHours 	= R::getAll($sql1);
		
		if($from == 1) {			
			$openingHours = openingHoursStringupdated($openingHours);
		}
        if (!$merchants) {
            // the Merchants was not found
            throw new ApiException("Your status is not in active state", ErrorCodeType::MerchantsNotInActiveStatus);
        }
		else
		{			
			if(isset($merchants[0]['distance']) && !empty($merchants[0]['distance'])) {					
				$result = R::findOne('admins', 'id = ? ', array('1'));
				$distanceLimit = $result->LocationLimit;
				if($merchants[0]['distance'] > $distanceLimit) 
					$AllowCart = 0;
				else
					$AllowCart = 1;
			}
			if($userId != '' ){
		  		if($merchants[0]['AlreadyFavourited']  == '')
		   		 $merchants[0]['AlreadyFavourited']  = '0';
			}
			else
				 $merchants[0]['AlreadyFavourited']  = '0';
		   if($merchants[0]['DiscountTier']  > 0)
				$merchants[0]['DiscountTier'] 	= 	$discountTierArray[$merchants[0]['DiscountTier']].'%';
		   $merchantsDetails 				= 	$merchants[0];
		   $merchantsDetails['Category']	=	$categories[0]['catId'];
		   
		   $merchantsDetails['OpeningHours']=	$openingHours;
		   if($from == 1){
		   		// Merchant categories for mobile
				if($categories[0]['catId'] != ''){
					$sql 			= "select c.id as CategoryId,CategoryName,CategoryIcon from categories as c where id in (".$categories[0]['catId'].") and Status = 1";
			   		$categoriesList = R::getAll($sql);
					if($categoriesList){
						foreach($categoriesList as $key => $value){
							$value['CategoryIcon']	= CATEGORY_IMAGE_PATH.$value['CategoryIcon'];
							$categoriesArray[]		= $value;
						}
					}
				}
				$merchantsDetails['OrderCount']				=	$totalOrders;
				$merchantsDetails['OrderedFriendsCount']	=	$orderedFriendsCount;
				$merchantsDetails['OrderedFriendsList']		=	$friendsListArray;
				$merchantsDetails['CategoryList']			=	$categoriesArray;
				$merchantsDetails['Comments']				=	$commentsArray;
				$merchantsDetails['ProductList']			=	$productList;
			}
			
			$merchantsDetailsOut['merchantDetails'] = $merchantsDetails;
			if(isset($AllowCart))
				$merchantsDetailsOut['AllowCart'] = $AllowCart;				
			return $merchantsDetailsOut;
		}
    }

	/**
     * Validate the modification of an account
     * @throws ApiException if the user being modifyng the account with already exists of email , facebook and linked ids in the database.
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
			
			$sql 		 = " select id,ResetPassword from merchants where id = '".$merchantsId."' and Status = 1";
			//echo $sql;
			$merchantDetails = R::getAll($sql);
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
	         * No Emial registered with email address
	         */
            throw new ApiException("No Merchant registered with this Email", ErrorCodeType::NoEmailExists);
        }
        else if($merchantDetails[0]['Status'] != StatusType::ActiveStatus)
        {
             /**
	         * Only active user can request for request forgetpassord for this account  
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
	         * @var $bean Model_Merchantss
	         */
			$bean = $this->bean;
			$merchantId = $bean->MerchantId;
			
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
       		$merchantsId 	= R::store($this);
			
			return $merchantsId;
	 }
	/**
	  * Update Password
	  * @param updating password by using word table from database
	  */
	public function forgotPassword(){
		/**
         * Get the bean
         * @var $bean Model_Merchantss
         */
		$bean = $this->bean;
		//validate param
		$this->validateMerchantsPassword(1);
		//validate Email
		$merchantDetails = $this->validateForgotEmail($bean->Email);
		$merchantsId = '';
		if($merchantDetails){
			$merchantsId = $merchantDetails[0]['id'];
			$bean->id    = $merchantsId;
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
         * @var $bean Model_Merchantss
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
     * @throws ApiException if the Logined user not in active state 
     */
    public function validateSettings($merchantsId)
    {
        /**
         * Get the bean
         * @var $bean Model_Merchantss
         */
        $bean = $this->bean;
		
		$merchants = R::findOne('merchants', 'id = ? and Status = ?', [$merchantsId,StatusType::ActiveStatus]);
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
     * @param Post user device token saving process
     */
    public function addTokens($post){

		 if(isset($post['DeviceToken']) && trim($post['DeviceToken']) !='' && isset($post['EndPointARN']) && trim($post['EndPointARN']) =='')
		 {
		 	 $valueToken1 = ltrim($post['DeviceToken'],'<');
			 $valueToken = Rtrim( $valueToken1,'>');
			 $tokenExists = R::findOne('devicetokens','Token = ?',[$valueToken]);
			 if($tokenExists){
			 	$post['EndPointARN'] =  $tokenExists['EndPointARN'];
			 }
		 } 
		 if(isset($post['DeviceToken']) && trim($post['DeviceToken']) !='' && isset($post['EndPointARN']) && trim($post['EndPointARN']) !='')
		 {
			 if($post['Platform'] == 'android'){
			 	$platform = 2;
			 }
			 else
			 	$platform = 1;
				
			 $valueToken1 = ltrim($post['DeviceToken'],'<');
			 $valueToken = Rtrim( $valueToken1,'>');
			 $token 				= R::dispense('devicetokens');
			 $token->LoginedDate 	= date('Y-m-d H:i:s');
			 $token->fkMerchantsId 		= $post['MerchantsId'];
			 $token->Token 			= $valueToken;
			 $token->EndPointARN 	= $post['EndPointARN'];
			 $token->Platform 		= $platform;
			 $token->Status 		= 1;
			 $sql = "update devicetokens set Status = 0 where Token = '".$valueToken."'";
			 R::exec($sql);
			 $tokenExists = R::findOne('devicetokens','Token = ? and fkMerchantsId = ?',[$valueToken, $post['MerchantsId']]);
			 if($tokenExists)
			 {
			 	$token->id =  $tokenExists['id'];
			 }
			 R::store($token);
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
		 * @var $bean Model_Merchantss
		 */
		$bean = $this->bean;
		if($bean->Start)
			$Start = $bean->Start;
		else
			$Start = 0;
		//Validate latitude and longitude
		$this->validateLatLong();
					
		$latitude		=	$bean->Latitude;			
		$longitude		=	$bean->Longitude;
		if(isset($bean->Category))
			$category		=	$bean->Category;
		if($bean->Type)
			$type		=	$bean->Type;
		else
			$type = 0;
		if(isset($bean->DiscountTier) && $bean->DiscountTier != ''){
			$tierVal	=	$bean->DiscountTier;
			//Validate discount tier
			$this->validateDiscountTier($tierVal);
			/*$key = array_search($bean->DiscountTier, $discountTierArray);
			if($key > 0)
				$searchCondition	.=	" and m.DiscountTier = ".$key." ";*/
			if($tierVal > 0)
				$searchCondition	.=	" and m.DiscountTier = ".$tierVal." ";
		}
		/**
		 * Query to get merchant details
		 */
		 if($bean->SearchKey != '')
			$searchCondition	.=	" and (m.CompanyName like '%".$bean->SearchKey."%' or  m.ShortDescription like '%".$bean->SearchKey."%')";
		if($category != ''){
			$fields				 =	'm.id,m.id as MerchantId,DiscountType,m.Icon,m.Image,m.DiscountTier,m.CompanyName,m.ShortDescription,m.Address,mc.fkCategoriesId,m.ItemsSold,m.Latitude,m.Longitude,DiscountProductId';
			$join_condition 	 = ' LEFT JOIN  merchantcategories as mc ON (mc.fkMerchantId = m.id) ';
			$searchCondition	.=	" and mc.fkCategoriesId = ".$category." ";
		}
		else{
			$fields			=	'm.id,m.id as MerchantId,DiscountType,m.Icon,m.Image,m.DiscountTier,m.CompanyName,m.ShortDescription,m.Address,m.ItemsSold,m.Latitude,m.Longitude,DiscountType,DiscountProductId';
			$join_condition	= '';
		}
		 if($type == 2 )
			$orderby	=	"m.ItemsSold desc";
		 else
			$orderby	=	"distance asc";
		
		if(!empty($merchantIds)) { //if merchant !=''
			$condition = ' and m.id in ('.$merchantIds.')';// m.id in ()
		}
		
		$sql 	= "SELECT SQL_CALC_FOUND_ROWS ".$fields.",(((acos(sin((".$latitude."*pi()/180)) * sin((m.`Latitude`*pi()/180))+cos((".$latitude."*pi()/180)) * cos((m.`Latitude`*pi()/180)) * cos(((".$longitude."- m.`Longitude`)*pi()/180))))*180/pi())*60*1.1515)*1.6093 as distance  from merchants as m ".$join_condition." where m.Status = 1 ".$searchCondition." ".$condition." and m.Address != '' and m.Image != '' ORDER BY ".$orderby." limit $Start,10";	
		//echo $sql;
		$result 		= R::getAll($sql);
		$totalRec 		= R::getAll('SELECT FOUND_ROWS() as count ');
		$total 			= (integer)$totalRec[0]['count'];
		$listedCount	= count($result);
		$countProducts = array();
		$discountedProduct = 0;
		
		if($result){
			foreach($result as $key=>$value)
			{
				$merchantsIds[] = $value['MerchantId'];
				$merchantIdValues = implode(',',$merchantsIds);
				$sql = "select count(id) as productCount,DiscountApplied from products where Status = 1 and fkMerchantsId  = ".$value['MerchantId']." group by DiscountApplied";
				$countResult = R::getAll($sql);
				$totalProduct = 0;
				//$countProducts = array();
				foreach($countResult as $kk=>$vv)
				{
					if($vv['DiscountApplied'] == 1){
						$discountedProduct = $vv['productCount'];
						$totalProduct = $totalProduct + $vv['productCount'];
					}
					else{
						$totalProduct = $totalProduct + $vv['productCount'];
					}
					$countProducts[$value['MerchantId']]['TotalCount'] = $totalProduct;
					$countProducts[$value['MerchantId']]['DiscountApplied'] = $discountedProduct;
				}
			}
			foreach($result as $key=>$value)
			{
				$imagePath = $iconPath	= '' ;
				$iconPath  				= MERCHANT_ICONS_IMAGE_PATH.$value['Icon'];
				$imagePath 				= MERCHANT_IMAGE_PATH.$value['Image'];
				
				//$value['distance'] 		= miles2kms($value['distance']);
				$value['Image'] 		= $imagePath;
				$value['Icon'] 			= $iconPath;
				$value['IsSpecial'] 	= "0";
				$value['IsGoldenTag'] 	= "0";
				$discountFlag = 0;
				if($value['DiscountType'] == 1)
				{
					if(isset($value['DiscountProductId']) && $value['DiscountProductId'] == 'all')
						$discountFlag = 1;
					else{
						$tot_product_ids = $this->validateMerchantProduct($value['DiscountProductId']);
						if(isset($countProducts[$value['MerchantId']]) && is_array($countProducts[$value['MerchantId']]) && count($countProducts[$value['MerchantId']]) > 0 ){
							if($tot_product_ids >= $countProducts[$value['MerchantId']]['DiscountApplied'] ){
								$totalProduct = $tot_product_ids;
								$disapplied	  = $countProducts[$value['MerchantId']]['DiscountApplied'];
								$totaldiscount = ($disapplied/$totalProduct)*100;
								if($totaldiscount >= 75)
									$value['IsSpecial'] 	= "1";
								if($disapplied == $totalProduct)
									$value['IsGoldenTag'] 	= "1";
							}
						}
					}
				}
				else
					$discountFlag = 1;
				if($discountFlag == 1){
					if(isset($countProducts[$value['MerchantId']]) && is_array($countProducts[$value['MerchantId']]) && count($countProducts[$value['MerchantId']]) > 0 ){
				  		$totalProduct = $countProducts[$value['MerchantId']]['TotalCount'];
						$disapplied	  = $countProducts[$value['MerchantId']]['DiscountApplied'];
						$totaldiscount = ($disapplied/$totalProduct)*100;
						if($totaldiscount >= 75)
							$value['IsSpecial'] 	= "1";
						if($disapplied == $totalProduct)
							$value['IsGoldenTag'] 	= "1";
				     }
			    }
				//echo"<br>===================>".$value['DiscountProductId'];
				//echo"<br>===================>".$value['DiscountType'];
				if($value['DiscountTier']  != '')
					$value['DiscountTier'] 	= $discountTierArray[$value['DiscountTier']].'%';
				$MerchantListArray[] 	= $value;
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
		$discountArray = explode(',',$tierVal);
		global $discountTierArray;
		$discountId  = '';
		foreach($discountTierArray as $key=>$value){
			$discountId .= $key.',';
		}
		if($discountId != ''){
			$discountId 	= trim($discountId,',');
			$globalDiscount = explode(',',$discountId);
			foreach($discountArray as $dkey){
				if(!in_array($dkey,$globalDiscount)){
					// the action was not found
					throw new ApiException("Invalid DiscountTier" ,  ErrorCodeType::SomeFieldsRequired);
				}
			}
		}
    }
	/**
     * Validate the modification of an account
     * @throws ApiException if the user being modifyng the account with already exists of email , facebook and linked ids in the database.
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
         * @var $bean Model_Merchants
         */
		$bean = $this->bean;
		
		//validate param
		$this->validatecheckLocationParams();
		
		$result = R::findOne('merchants', 'id = ? ', array($bean->MerchantId));	
		if($result) {
			// User Distance from latitude & longitude
			$distance = (((acos(sin(($bean->Latitude*pi()/180)) * sin(($result->Latitude*pi()/180))+cos(($bean->Latitude*pi()/180)) * cos(($result->Latitude*pi()/180)) * cos((($bean->Longitude- $result->Longitude)*pi()/180))))*180/pi())*60*1.1515)*1.6093;
			
			//Admin Distance limit			
			$adminResult = R::findOne('admins', 'id = ? ', array('1'));	
			$distanceLimit = $adminResult->LocationLimit;
			
			//check for user in the location
			if($distance > $distanceLimit) 
				$AllowCart = 0;
			else
				$AllowCart = 1;
				
			//$AllowCartArray['UserId'] = $bean->UsersId;
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
     * get Cusomer list
     */
    
	public function getCustomerList($merchantId)
    {
	
		/**
         * Query to get customer list
         */
		 $d = 0;
		 $condition =  $having_condition = $userName = $visitCount = $totalSpend ='';
		 $bean = $this->bean;
		
		$this->validateMerchants($merchantId,1);
		if($bean->Type == 1){
			$start = 0;
			$limit	= 5;
			$orderby = 'TotalOrders desc,o.OrderDate desc';
		}
		else{
			$orderby = 'LastVisit  desc';
			if(isset($bean->UserName))
				$userName		=	$bean->UserName;
			if(isset($bean->TotalOrders))
				$visitCount		=	$bean->TotalOrders;
			if(isset($bean->TotalPrice))
				$totalSpend		=	$bean->TotalPrice;
			if($bean->Start)
				$start = $bean->Start;
			else
				$start = 0;
			if($bean->Limit)
				$limit = $bean->Limit;
			else
				$limit = 10;
		}
		
		
		if($userName != '')
			$condition  .= ' and (u.FirstName LIKE "%'.$userName.'%" or u.LastName LIKE "%'.$userName.'%")';
		if($visitCount != '' && $totalSpend != '')
			$having_condition  .= ' Having TotalOrders = '.$visitCount.'  and  TotalPrice = '.$totalSpend.' ';
		else if($visitCount)
			$having_condition  .= ' Having TotalOrders = '.$visitCount.' ';
		else if($totalSpend)
			$having_condition  .= ' Having TotalPrice = '.$totalSpend.' ';	
		$sql 			= 	"SELECT SQL_CALC_FOUND_ROWS u.id as userId,concat(u.FirstName,' ',u.LastName)as UserName,u.Photo,MAX(o.OrderDate) as LastVisit,
							MIN(o.OrderDate) as FirstVisit,COUNT(o.id) as TotalOrders,SUM(TotalPrice) as TotalPrice from orders as o 
							LEFT JOIN users as u ON(u.id = o.fkUsersId) where 1 ".$condition." and o.OrderStatus IN(0,1,2) and u.Status =1  and o.fkMerchantsId = ".$merchantId." 
							GROUP BY u.id ".$having_condition." order by ".$orderby." limit $start,$limit";	
		$CustomerList 	=	R::getAll($sql);
		$totalRec 		= 	R::getAll('SELECT FOUND_ROWS() as count ');
		$total 			= (integer)$totalRec[0]['count'];
		$listedCount	= count($CustomerList);
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
				$totalOrders			=	$value["TotalOrders"];
				$totalPrice				=	$value["TotalPrice"];
				$averagePrice			=	$totalPrice/$totalOrders;
				$value["AverageSpend"]	=	round($averagePrice,2);
				//$date_diff				=	date_diff(strtotime($value["LastVisit"]),strtotime($value["FirstVisit"]));
				$diff 					= 	abs(strtotime($value["LastVisit"])-strtotime($value["FirstVisit"]));
				$year					=	round($diff/(365*24*60*60));
				$month					=	round(($diff-($year*365*24*60*60))/(30*24*60*60));
				$dates					=	round(($diff-($year*365*24*60*60)-($month*30*24*60*60))/(24*60*60));
				$d						=	abs($dates);
				$value["DayDifference"]	=	$d;//$date_diff->format('%d');
				$value["Photo"]			=	$imagePath;
				$value["OriginalPhoto"]	=	$originalPath;
				$CustomerListArray[] 	= 	$value;
			}
			
			$CustomerListArray['result'] 		= $CustomerListArray;
			$CustomerListArray['totalCount']	= $total;
			$CustomerListArray['listedCount']	= $listedCount;
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
     * get Cusomer list
     */
    
	public function getTransactionList($merchantId)
    {
		$condition = $field = '';
		/**
         * Query to get transaction list
         */
		
		$bean = $this->bean;
		
		$this->validateMerchants($merchantId,1);
		if(!isset($_SESSION['tuplit_ses_from_timeZone']) || $_SESSION['tuplit_ses_from_timeZone'] == ''){
			$time_zone = getTimeZone();
			$_SESSION['tuplit_ses_from_timeZone'] = strval($time_zone);
		} else {
			$time_zone = $_SESSION['tuplit_ses_from_timeZone'];
		}
		$dataType 	= $bean->DataType;
		$startDate 	= $bean->StartDate;
		$endDate   	= $bean->EndDate;
		$curr_date 	= date('d-m-Y');
		$cur_month 	= date('m');
		$cur_year 	= date('Y');
		if($dataType=='year') {
			$field = " , MONTH(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE)) AS month";
			$condition .= "  and DATE_FORMAT(OrderDate,'%Y') = ".$cur_year." group by month";
		} else if($dataType=='month') {
			$field = " , DATE_FORMAT(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE), '%m/%d/%Y') AS day";
			$condition .= "and DATE_FORMAT(OrderDate,'%m') = ".$cur_month." and DATE_FORMAT(OrderDate,'%Y') = ".$cur_year." group by day";
		} else if($dataType=='day') {
			$field = " , HOUR(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE)) AS hour";
			$condition .= " and date(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE))='".date('Y-m-d',strtotime($curr_date))."' group by hour";
		} else if($dataType=='between') {
			$field = " , DATE_FORMAT(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE), '%m/%d/%Y') AS day";
			if(isset($startDate) && $startDate!='' && isset($endDate) && $endDate !='')
			{
				$condition .= " and DATE(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE)) between '".date('Y-m-d',strtotime($startDate))."' and '".date('Y-m-d',strtotime($endDate))."'";
			} else if(isset($startDate) && $startDate!='') {
				$condition .= " and DATE(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE)) >= '".date('Y-m-d',strtotime($startDate))."'";
			} else if(isset($endDate) && $endDate!='') {
				$condition .= " and DATE(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE)) <= '".date('Y-m-d',strtotime($endDate))."'";
			} 
			$condition .= ' group by day';
		} else if($dataType=='7days') {
			$field = " , DATE_FORMAT(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE), '%m/%d/%Y') AS day";
			$condition .= "and (DATE_FORMAT(OrderDate,'%Y-%m-%d') <= '".date('Y-m-d',strtotime($curr_date))."' and DATE_FORMAT(OrderDate,'%Y-%m-%d') > '".date('Y-m-d',strtotime("-7 days"))."')  group by day";
		}
		else if($dataType=='timeofday') {
			$field = " , HOUR(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE)) AS hour";
			$condition .= " and date(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE))='".date('Y-m-d',strtotime($curr_date))."' group by hour";
		}
		$sql 			= 	"SELECT  count(id) as TotalOrders,SUM(TotalPrice) as TotalPrice ".$field." from orders as o 
							where 1 and  o.OrderStatus IN (0,1,2) and o.fkMerchantsId = ".$merchantId."  ".$condition."";	
							//echo '-->'. $sql.'<br>';
		$TransactionList 	=	R::getAll($sql);
		if($TransactionList ){
			foreach($TransactionList as $key=>$value){
				$totalOrders						=	$value["TotalOrders"];
				$totalPrice							=	$value["TotalPrice"];
				$averageTransaction					=	$totalPrice/$totalOrders;
				$value["TotalPrice"]				=	number_format((float)$totalPrice, 2, '.', '');
				$value["Average"]					=	number_format((float)$averageTransaction, 2, '.', '');
				$TransactionListArray[] 			= 	$value;
			}
			
			$TransactionArray['result']            = $TransactionListArray;
			return $TransactionArray;
		}
		else{
			 /**
	         * throwing error when no data found
	         */
			  throw new ApiException("No results Found", ErrorCodeType::NoResultFound);
		}
	}
}
