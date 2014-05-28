<?php

/**
 * Description of Model_Merchants
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
				return $result->id;
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
		// validate the model
        $this->validateModifyMerchant($iconExist,$merchantExist);
        // validate the modification
        $this->validateModify($merchantsId);

        $bean->DateModified = date('Y-m-d H:i:s');
		
		
		$latlong = $lat = $lng = '';
		//$latlong = getLatLngFromAddress($bean->Address) ;
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
     * @throws ApiException if the user being modifyng the account with already exists of email , facebook and linked ids in the database.
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
		$sql = "select id from merchants where id = ".$merchantsId." and Status = 1 ";
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
		$fields	= $userId = $joinCondition	 =   '';
		$productsArray = $friendsDetails = $friendsArray = $UserId = $FriendId = $FriendsList	 =array();
		$bean = $this->bean;
		if($bean->From)
			$from = $bean->From;
		else
			$from = 0;

		if($from == 1){
			//Friends List
			if($bean->UserId != ''){
				$userId = $bean->UserId;
				$this->validateUser($userId);
				//Already Favouried
				$fields			= ",FavouriteType as AlreadyFavourited";
				$joinCondition	= "LEFT JOIN favourites as f ON(m.id = f.fkMerchantsId and f.fkMerchantsId = ".$requestedBy." and f.fkUsersId = ".$userId." and FavouriteType = 1) ";
				//Friends List
				$sql 			= "SELECT  group_concat(`fkFriendsId`,',', fkUsersId) as friendsId  FROM friends where  (fkUsersId = ".$userId." or fkFriendsId = ".$userId.") and Status = 1 ";
	   			$friends		= R::getAll($sql);
				if(isset($friends)){
					if($friends[0]['friendsId'] != ''){
						$sql 	= "SELECT group_concat(`fkUsersId` separator ',') as  usersId FROM orders where fkUsersId IN(".$friends[0]['friendsId'].") and fkUsersId != ".$userId." and fkMerchantsId = ".$requestedBy." and OrderStatus = 1 and Status = 1 ";
						$friendsDetails		= R::getAll($sql);
						if($friendsDetails){
							if($friendsDetails[0]['usersId'] != ''){
								$sql 			= "SELECT id,FirstName,LastName,Photo FROM users where id IN (".$friendsDetails[0]['usersId'].") and Status = 1";
				   				$friendsArray 	= R::getAll($sql);
								if($friendsArray){
									foreach($friendsArray as $key => $value){
										$user_image_path = '';
										if(isset($value['Photo']) && $value['Photo'] != ''){
											$user_image_path = USER_IMAGE_PATH.$value['Photo'];
										}
										$value['Photo'] 				= $user_image_path;
										$friendsListArray[]				= $value;
									}
								}
							}
						}
					}
				}
			}
			//Total users shopped
		
			$orderSql 		= "SELECT count(distinct `fkUsersId`) as  TotalOrder FROM orders where fkMerchantsId=".$requestedBy." and OrderStatus = 1 and Status = 1 ";
   			$OrdersCount 	= R::getAll($orderSql);
			
			//merchant products
			$productSql 	= "SELECT id as ProductId,ItemName,Photo,Price,Quantity,ItemType,DiscountApplied,DiscountTier,DiscountPrice from  
								products as p where Status = 1 and p.fkMerchantsId=".$requestedBy." ";
			$products 	= R::getAll($productSql);
			if($products){
				foreach($products as $key => $value){
					$photo_image_path = '';
					if(isset($value['Photo']) && $value['Photo'] != ''){
						$photo_image_path = PRODUCT_IMAGE_PATH.$value['Photo'];
					}
					$value['Photo'] 		= $photo_image_path;
					 if($value['DiscountTier']  > 0)
					$value['DiscountTier'] 	= $discountTierArray[$value['DiscountTier']].'%';
					if($value['ItemType'] == 1)
					 	$productsArray['Regular'][] 		= $value;
					else if($value['ItemType'] == 2)
					 	$productsArray['Deal'][] 			= $value;
					else if($value['ItemType'] == 3){
					 	$productsArray['Special'][] 		= $value;
					}
				}
			}
			
		}
		// Merchant details
		$sql 	= 	  "SELECT m.id as MerchantId,m.FirstName,m.LastName,m.Email,m.CompanyName,m.Address,m.Location,m.Latitude,
					  m.Longitude,m.PhoneNumber,m.WebsiteUrl,m.Icon,m.Image,m.Description,m.ShortDescription,
                      m.DiscountTier,m.SpecialIcon,m.ItemsSold,m.OpeningHours,m.PriceRange,m.SpecialsSold".$fields." 
					  FROM merchants as m	".$joinCondition."	where m.Status = 1 and m.id=".$requestedBy." ";
   		$merchants 		= R::getAll($sql);
		// Merchant categories
		$sql 			= "SELECT group_concat(`fkCategoriesId` separator ',') as  catId FROM merchantcategories where fkMerchantId=".$requestedBy;
   		$categories 	= R::getAll($sql);
        if (!$merchants) {
            // the Merchants was not found
            throw new ApiException("Your status is not in active state", ErrorCodeType::MerchantsNotInActiveStatus);
        }
		else
		{
			if($userId != '' ){
		  		if($merchants[0]['AlreadyFavourited']  == '')
		   		 $merchants[0]['AlreadyFavourited']  = '0';
			}
			else
				 $merchants[0]['AlreadyFavourited']  = '0';
		   if($merchants[0]['DiscountTier']  > 0)
				$merchants[0]['DiscountTier'] 	= 	$discountTierArray[$merchants[0]['DiscountTier']].'%';
		   $merchantsDetails 				= 	$merchants[0];
		   $merchantsDetails['Category']	=	$categories;
		   if($from == 1){
				if($productsArray)
					$merchantsDetails['Products']	=	$productsArray;
				if($OrdersCount[0]['TotalOrder'] != 0)
					$merchantsDetails['OrderCount']	=	$OrdersCount;
				if($friendsArray)
					$merchantsDetails['FriendsList']	=	$friendsListArray;
			}
			return $merchantsDetails;
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
		$sql		 = "select id,FirstName,LastName,Email,Status from merchants where id = ".$merchantsId." and Status != 3";
		
		$merchants	 = R::getAll($sql);
		//echo '<pre>';print_r($merchants);echo'</pre>';
        if (!$merchants) {
            // the Merchants was not found
            throw new ApiException("Your status is not in active state", ErrorCodeType::MerchantsNotInActiveStatus);
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
			
			$sql 		 = " select id,ResetPassword from merchants where id = ".$merchantsId." and Status = 1";
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
	public function getMerchantList()
    {
		global $discountTierArray;
		$condition	= $searchCondition	= $category	 ='';
		/**
         * Get the bean
         * @var $bean Model_Merchantss
         */
		$bean = $this->bean;
		if($bean->Start)
			$start = $bean->Start;
		else
			$start = 0;
			
		//Validate latitude and longitude
		$this->validateLatLong();
		$latitude		=	$bean->Latitude;
		$longitude		=	$bean->Longitude;
		$category		=	$bean->Category;
		if($bean->Type)
			$type		=	$bean->Type;
		else
			$type = 0;
		
		if($bean->DiscountTier != ''){
			$tierVal	=	$bean->DiscountTier;
			//Validate discount tier
			$this->validateDiscountTier($tierVal);
			$key = array_search ($bean->DiscountTier, $discountTierArray);
			if($key > 0)
		 		$searchCondition	.=	" and m.DiscountTier = ".$key." ";
		}
		/**
         * Query to get merchant details
         */
		 if($bean->SearchKey != '')
		 	$searchCondition	.=	" and (m.CompanyName like '%".$bean->SearchKey."%' or  m.ShortDescription like '%".$bean->SearchKey."%')";
		if($category != ''){
			$fields				 =	'm.id,m.Icon,m.Image,m.DiscountTier,m.CompanyName,m.ShortDescription,m.Address,mc.fkCategoriesId,m.ItemsSold,m.Latitude,m.Longitude';
			$join_condition 	 = ' LEFT JOIN  merchantcategories as mc ON (mc.fkMerchantId = m.id) ';
			$searchCondition	.=	" and mc.fkCategoriesId = ".$category." ";
		}
		else{
			$fields			=	'm.id,m.Icon,m.Image,m.DiscountTier,m.CompanyName,m.ShortDescription,m.Address,m.ItemsSold,m.Latitude,m.Longitude';
			$join_condition	= '';
		}
		 if($type == 2 )
		 	$orderby	=	"m.ItemsSold desc";
		 else
			$orderby	=	"distance asc";
		$sql 			= "SELECT SQL_CALC_FOUND_ROWS ".$fields.",(((acos(sin((".$latitude."*pi()/180)) * sin((m.`Latitude`*pi()/180))+cos((".$latitude."*pi()/180)) * cos((m.`Latitude`*pi()/180)) * cos(((".$longitude."- m.`Longitude`)*pi()/180))))*180/pi())*60*1.1515) as distance  from merchants as m ".$join_condition." where m.Status = 1  ".$searchCondition." 	and m.Address != '' and m.Image != '' ORDER BY ".$orderby." limit $start,50";	
   		$result 		= R::getAll($sql);
		$totalRec 		= R::getAll('SELECT FOUND_ROWS() as count ');
		$total 			= (integer)$totalRec[0]['count'];
		$listedCount	= count($result);
		if($result){
			foreach($result as $key=>$value)
			{
				$imagePath = $iconPath	= '' ;
		
				$iconPath  				= MERCHANT_ICONS_IMAGE_PATH.$value['Icon'];
				$imagePath 				= MERCHANT_IMAGE_PATH.$value['Image'];
				$value['Image'] 		= $imagePath;
				$value['Icon'] 			= $iconPath;
				$value['IsSpecial'] 	= "0";
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
			  throw new ApiException("No merchants Found", ErrorCodeType::NoResultFound);
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
			$discountId .= $value.',';
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
}
