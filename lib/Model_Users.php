<?php

/**
 * Description of Model_Users
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
class Model_Users extends RedBean_SimpleModel implements ModelBaseInterface {

    /**
     * Identifier
     * @var int
     */
    public $id;

    /**
     * User email
     * @var string
     */
    public $Email;
	
	/**
     * User fbid
     * @var string
     */
    public $FBId;
	
	/**
     * User GooglePlusId
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
    public static function checkLogin($email,$password,$facebookId,$googlePlusId,$deviceToken,$token,$userData,$platform){
        /**
         * @var Model_Users
         */		
		if($facebookId != ''){
			$result = R::findOne('users', 'FBId = ? and Status <> ? ', array($facebookId,StatusType::DeleteStatus));
		}	
		else if($googlePlusId != ''){
			$result = R::findOne('users', 'GooglePlusId = ? and Status <> ?', array($googlePlusId,StatusType::DeleteStatus));
		}
		else{
			$result = R::findOne('users', 'Email = ? and Password = ? and Status <> ? ', array($email,PasswordHelper::encrypt($password),StatusType::DeleteStatus));
		}
        if (!$result) {
            return false;
        }
        else {
			if($result->Status != 1){
				// the User was not found
				 throw new ApiException("User not in active state", ErrorCodeType::UserNotInActiveStatus);
			}
			else{
				$user = new Model_Users();
				$endpointARN = '';
				if($token !=''){
					$linkARN['Token'] 		= $token;
					$linkARN['Platform'] 	= $platform;
					$linkARN['UserData'] 	= $userData;
					$linkARN['UserId'] 		= $result->id;
					$linkARN['DeviceToken'] = $deviceToken;
					$endpointARN = $user->createEndpointARN($linkARN);
				}
				$tokenArray['UserId'] 		= $result->id;
				$tokenArray['DeviceToken'] 	= $deviceToken;
				$tokenArray['EndPointARN'] 	= $endpointARN;
				$tokenArray['Platform'] 	= $platform;
				
				$user->addTokens($tokenArray);
				$user 						= R::dispense('users');
				$user->id 					= $result->id;
				$user->LastLoginDate 		= date('Y-m-d H:i:s');
				$userUpdate 				= R::store($user);
				return $result->id;
			}
        }
    }
	/**
     * @param Link with AWS to get endpointARN
     */
	 public function createEndpointARN($device){
	 	$this->Platform = $platform = $device['Platform'];//UserData . Token
		$Token = $device['Token'];
		$CustomUserData = $device['UserData'] .' - '.$device['UserId'];
		// validate the modification
		$this->validateARN();
		global $applicationARN;
		$PlatformApplicationArn = $applicationARN[$platform];
		$endpointARN = createEndpointARNAWS($PlatformApplicationArn,$Token,$CustomUserData);
		if($endpointARN == ''){
			 $valueToken1 = ltrim($device['DeviceToken'],'<');
			 $valueToken = Rtrim( $valueToken1,'>');
			 $tokenExists = R::findOne('devicetokens','DeviceToken = ?',[$valueToken]);
			 if($tokenExists)
			 {
			 	$endpointARN =  $tokenExists['EndPointARN'];
			 }
		}
		return $endpointARN;
	 }
	
	 /**
     * Validate the fields (Platform)
     * @throws ApiException if the models fails to validate
     */
    public function validateARN()
    {
		$rules = [
            'required' => [
               ['Platform']
            ],
			'in' =>[
					['Platform',['ios','android']]
			]
        ];
        $v = new Validator($this);
        $v->rules($rules);

        if (!$v->validate()) {
            $errors = $v->errors();
            throw new ApiException("Please enter valid platform" ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
	}
     /**
     * Create an user account
	 * Validation for email,fbId,GooglePlusId
     */
    public function create(){ // Tuplit user creating

		 
		 /**
         * Get the bean
         * @var $bean Model_Users
         */
        $bean 		= $this->bean;
		$flag 		= $bean->PhotoFlag;
		// validate the model
        $this->validate();

        // validate the creation
        $this->validateCreate();
		
		if($flag != 0){
			//error throw for image error
			if($flag == 1)
				throw new ApiException("Problem in Image - Type", ErrorCodeType::ProblemInImage);
			else if($flag == 2)
				throw new ApiException("Problem in Image",ErrorCodeType::ProblemInImage);
			else if($flag == 3)
				throw new ApiException("Problem in Image - Size",ErrorCodeType::ProblemInImage);
			else if($flag == 4)
				throw new ApiException("Problem in Image - Dimension.Minimum should be (100X100)",ErrorCodeType::ProblemInImage);
		}
		unset($bean->PhotoFlag);
		
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
		$bean->Status 				= StatusType::ActiveStatus;
		$bean->PushNotification 	= 1;
		$bean->SendCredit		 	= 1;
		$bean->RecieveCredit 		= 1;
		$bean->BuySomething 		= 1;
		// save the bean to the database
		$userId = R::store($this);
		if($userId != ''){
			$numeric       		= '1234567890';
			$numbers       		= substr(str_shuffle($numeric), 0, 3);
			$uniqueId 	   		= 'tuplit'.$numbers.$userId;
			$bean->UniqueId		= $uniqueId;
			$bean->id			= $userId;
			$userId 			= R::store($this);
		}
	  	return $userId;
    }

	
    /**
     * Validate the model
     * @throws ApiException if the models fails to validate required fields
     */
    public function validate($type='')
    {
		$bean = $this->bean;
		if($bean->FBId == '' && $bean->GooglePlusId == '' ){
			$rules = [
	            'required' => [
	                 ['FirstName'],['LastName'],['Email'],['PinCode'],['Password']
	            ],
				'Email' => 'Email',
	        ];
		}
		else if($type == ''){
		  $rules = [
	            'required' => [
	                 ['FirstName'],['LastName'],['Email'],['PinCode']
	            ],
				'Email' => 'Email',
	        ];
		}
		else{
			$rules = [
	            'required' => [
	                ['FirstName'],['LastName'],['Email'],['PinCode']
	            ],
				'Email' => 'Email',
	        ];
		}
		
        $v = new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
            throw new ApiException("Please check the user's properties" ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
    }

    /**
     * Validate the creation of an account
     * @throws ApiException if the user being creating the account with already exists of email , facebook and linked ids in the database.
     */
    public function validateCreate(){

        /**
         * Get the bean
         * @var $bean Model_Users
         */
        $bean = $this->bean;
		
        /**
         * FacebookId must be unique
         */
		if($bean->FBId != ''){
	        $existingAccount = R::findOne('users', 'FBId = ? and Status <> ? order by DateModified desc', array($bean->FBId,StatusType::DeleteStatus));
	        if ($existingAccount) {
	            // an account with that FacebookId already exists in the system - don't create account
	            throw new ApiException("This facebookId is already associated with another account", ErrorCodeType::FbIdAlreadyExists);
			}
		}
		/**
         * GooglePlusId must be unique
         */
		if($bean->GooglePlusId != ''){
	        $existingAccount = R::findOne('users', 'GooglePlusId = ? and Status <> ? order by DateModified desc', array($bean->GooglePlusId,StatusType::DeleteStatus));
	        if ($existingAccount) {
	            // an account with that GooglePlusId already exists in the system - don't create account
	            throw new ApiException("This GooglePlusId is already associated with another account",ErrorCodeType::GooglePlusIdAlreadyExists);
			}
		}
		/**
         * Email Id must be unique
         */
        $existingAccount = R::findOne('users', 'Email = ? and Status <> ? order by DateModified desc', array($bean->Email,StatusType::DeleteStatus));
        if ($existingAccount) {
            // an account with that email already exists in the system - don't create account
            throw new ApiException("This Email Address is already associated with another account", ErrorCodeType::EmailAlreadyExists);
		}
		
		/**
         * Pincode must be unique
         */
		if($bean->PinCode != ''){
	        $existingAccount = R::findOne('users', 'PinCode = ? and Status <> ? order by DateModified desc', array($bean->PinCode,StatusType::DeleteStatus));
	        if ($existingAccount) {
	            // an account with that pincode already exists in the system - don't create account
	            throw new ApiException("You are not allow to use this pincode",ErrorCodeType::PincodeAlreadyExists);
			}
		}
		/**
         * Cell number must be unique
         */
		if($bean->CellNumber != ''){
	        $existingAccount = R::findOne('users', 'CellNumber = ? and Status <> ? order by DateModified desc', array($bean->PinCode,StatusType::DeleteStatus));
	        if ($existingAccount) {
	            // an account with that pincode already exists in the system - don't create account
	            throw new ApiException("You are not allow to use this CellNumber",ErrorCodeType::CellNumberAlreadyExists);
			}
		}
    }

	
	/**
     * @param Modify the user entity
	 * Throws exception for email , facebook , googlePlusId already exists condition
     */
    public function modify($userId = null,$modify=''){

		/**
         * Get the bean
         * @var $bean Model_Users
         */
		$bean = $this->bean;
		 // encrypt the password
		if($modify == 1){
			if($bean->Password && $bean->Password != ''){
	        	$bean->Password = PasswordHelper::encrypt($bean->Password);
			}
			else if($bean->Password == ''){
				unset($bean->Password);
			}
		}
		// validate the model
        $this->validate(1);

        // validate the modification
        $this->validateModify($userId);

        $bean->DateModified = date('Y-m-d H:i:s');
		unset($bean->PhotoFlag);
		
		$bean->DateModified 		= date('Y-m-d H:i:s');
		
		
        // modify the bean to the database
        R::store($this);
    }
	

	/**
     * Validate the modification of an account
     * @throws ApiException if the user being modifyng the account with already exists of email , facebook and linked ids in the database.
     */
	public function validateModify($userId)
    {
       	/**
         * Get the bean
         * @var $bean Model_Users
         */
        $bean = $this->bean;

        /**
         * Get the identity of the person making the change
         * @var $modifiedBy Model_Users
         */
        $modifiedBy = R::findOne('users', 'id = ? and Status = ?', [$userId,'1']);

        if (!$modifiedBy) {
            // the User was not found
            throw new ApiException("You have no access to edit the datas", ErrorCodeType::NotAccessToDoProcess);
        }
		
        /**
         * FacebookId must be unique
         */
		if($bean->FBId != ''){
	        $existingAccount = R::findOne('users', 'FBId = ? and id <> ? and Status <> ? order by DateModified desc ', array($bean->FBId,$userId,StatusType::DeleteStatus));
	        if ($existingAccount) {
	            // an account with that FacebookId already exists in the system - don't modify account
	            throw new ApiException("This facebookId is already associated with another account", ErrorCodeType::FbIdAlreadyExists);
			}
		}
		/**
         * GooglePlusId must be unique
         */
		if($bean->GooglePlusId != ''){
	        $existingAccount = R::findOne('users', 'GooglePlusId = ? and id <> ? and Status <> ? order by DateModified desc ', array($bean->GooglePlusId,$userId,StatusType::DeleteStatus));
	        if ($existingAccount) {
	            // an account with that GooglePlusId already exists in the system - don't modify account
	            throw new ApiException("This GooglePlusId is already associated with another account", ErrorCodeType::GooglePlusIdAlreadyExists);
			}
		}
		
		/**
         * Email Id must be unique
         */
	         //$existingAccount = R::findOne('users', 'Email = ? and id <> ? and Status <> ? ', array($bean->Email,$userId,StatusType::DeleteStatus));
			 $existingAccount = R::findOne('users', 'Email = ? and id <> ? and Status <> ? order by DateModified desc', array($bean->Email,$userId,StatusType::DeleteStatus));
	        if ($existingAccount) {
	            // an account with that email already exists in the system - don't modify account
	            throw new ApiException("This Email Address is already associated with another account", ErrorCodeType::EmailAlreadyExists);
			}
		
		/**
         * Pincode must be unique
         */
		if($bean->PinCode != ''){
	        $existingAccount = R::findOne('users', 'PinCode = ? and id <> ? and Status <> ? order by DateModified desc ', array($bean->PinCode,$userId,StatusType::DeleteStatus));
	        if ($existingAccount) {
	            // an account with that pincode already exists in the system - don't create account
	            throw new ApiException("You are not allow to use this pincode",ErrorCodeType::PincodeAlreadyExists);
			}
		}
		/**
         * CellNumber must be unique
         */
		if($bean->CellNumber != ''){
	        $existingAccount = R::findOne('users', 'CellNumber = ? and id <> ? and Status <> ? order by DateModified desc ', array($bean->CellNumber,$userId,StatusType::DeleteStatus));
	        if ($existingAccount) {
	            // an account with that pincode already exists in the system - don't create account
	            throw new ApiException("You are not allow to use this CellNumber",ErrorCodeType::CellNumberAlreadyExists);
			}
		}
    }
	

	/**
     * @param Get user details
	 * validate User who requesting to see his details
     */
    public function getUserDetails($requestedBy){
		$sql 	= "SELECT id as UserId,FirstName,LastName,Email,Photo,CurrentBalance as AvailableBalance FROM users where Status = 1 and id=".$requestedBy;
   		$user 	= R::getAll($sql);//FBId,GooglePlusId,PushNotification,ZipCode,Location,Country,SendCredit,RecieveCredit,BuySomething 
        if (!$user) {
            // the User was not found
            throw new ApiException("Your status is not in active state", ErrorCodeType::UserNotInActiveStatus);
        }
		else
		{
			$imagePath 		= '';
			if($user[0]['Photo'] !=''){
				if(SERVER)
					$imagePath = USER_THUMB_IMAGE_PATH.$user[0]['Photo'];
				else{
					if(file_exists(USER_THUMB_IMAGE_PATH_REL.$user[0]['Photo']))
						$imagePath = USER_THUMB_IMAGE_PATH.$user[0]['Photo'];
				}
			}
			$user[0]['Photo'] = $imagePath;
			$userDetails = $user[0];
			return $userDetails;
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
     * Check Reset Password
     * @param checking reset password 
     */
	public function checkResetPassword($userId){	 
			
			$sql 		 = " select id,ResetPassword from users where id = ".$userId." and Status = 1";
			$userDetails = R::getAll($sql);
			if($userDetails){
				if($userDetails[0]['ResetPassword'] == 0){
					throw new ApiException("Sorry! You cannot use this link again" ,  ErrorCodeType::PasswordResetNotValid);
				}
			}
			else {
            	// the User was not found
            	throw new ApiException("Your status is not in active state", ErrorCodeType::UserNotInActiveStatus);
       		}
			return $userDetails;
	 }
	 /**
     * Check forgot Password Email
     * @param checking email
     */
	public function validateForgotEmail($email){	 
		$sql 		 	 = " select id,FirstName,LastName,ResetPassword,Status,Email from users where email = '".$email."' order by id desc";
		$userDetails = R::getAll($sql);
		if(!$userDetails) {
            /**
	         * No Emial registered with email address
	         */
            throw new ApiException("No user registered with this Email", ErrorCodeType::NoEmailExists);
        }
        else if($userDetails[0]['Status'] != StatusType::ActiveStatus)
        {
             /**
	         * Only active user can request for request forgetpassord for this account  
	         */
			  throw new ApiException("You are not authorized to request forgot password for this account", ErrorCodeType::NoAuthoriseToRequestForPassword);
		}
		return $userDetails;
	 }
	/**
     * Update Password
     * @param updating password by using word table from database
     */
	public function updatePassword(){
			
			/**
	         * Get the bean
	         * @var $bean Model_Users
	         */
			$bean = $this->bean;
			$userId = $bean->UserId;
			//validate param
			$this->validateUsersPassword(2);
			
			//validate merchant
			//$this->checkResetPassword($userId);
			unset($bean->UserId);
			// encrypt the password
			$bean->id				= $userId;
        	$bean->Password			= PasswordHelper::encrypt($bean->Password);
			$bean->ResetPassword	= 0;
      		// save the bean to the user table
       		$usersId 				= R::store($this);
			
			return $usersId;
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
		$this->validateUsersPassword(1);
		//validate Email
		$userDetails = $this->validateForgotEmail($bean->Email);
		$userId = '';
		if($userDetails){
			$userId		 = $userDetails[0]['id'];
			$bean->id    = $userId;
			$bean->ResetPassword = 1;
			R::store($this);
			return $userDetails;
		}
	}
	 /**
     * Validate the fields  for ResetPassword
     * @throws ApiException if the models fails to validate
     */
    public function validateUsersPassword($type = '')
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
		               ['UserId'],['Password']
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
    public function updateSettings($userId){

		/**
         * Get the bean
         * @var $bean Model_Users
         */
		$bean = $this->bean;
		
		//validate param
		$this->validateSettingsParams();
		
		// validate the modification
        $this->validateSettings($userId);
		$user 						= R::dispense('users');
		$user->id 					= $userId;
		$user->PushNotification 	= $bean->Action;
		$userUpdate 				= R::store($user);
	}
	

	/**
     * @throws ApiException if the Logined user not in active state 
     */
    public function validateSettings($userId)
    {
        /**
         * Get the bean
         * @var $bean Model_Users
         */
        $bean = $this->bean;
		
		$user = R::findOne('users', 'id = ? and Status = ?', [$userId,StatusType::ActiveStatus]);
        if (!$user) {
            // the User was not found
            throw new ApiException("Your status is not in active state", ErrorCodeType::UserNotInActiveStatus);
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
			 $token->fkUserId 		= $post['UserId'];
			 $token->Token 			= $valueToken;
			 $token->EndPointARN 	= $post['EndPointARN'];
			 $token->Platform 		= $platform;
			 $token->Status 		= 1;
			 $sql = "update devicetokens set Status = 0 where Token = '".$valueToken."'";
			 R::exec($sql);
			 $tokenExists = R::findOne('devicetokens','Token = ? and fkUserId = ?',[$valueToken, $post['UserId']]);
			 if($tokenExists)
			 {
			 	$token->id =  $tokenExists['id'];
			 }
			 R::store($token);
		 }
	}
}
