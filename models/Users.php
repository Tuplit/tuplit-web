<?php

/**
 * Description of Users
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
require_once	'Notifications.php';
require_once	'Orders.php';
require_once	'Comments.php';

class Users extends RedBean_SimpleModel implements ModelBaseInterface {

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
		* @var Users
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
				$user = new Users();
				$endpointARN = '';
				if($deviceToken !=''){
					$linkARN['Token'] 		= $deviceToken;
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
				return $result->id.'##1';
			}
        }
    }
	
	/**
    * @param Link with AWS to get endpointARN
    */
	public function createEndpointARN($device){
	
		global $applicationARN;
	 	$this->Platform 		= 	$platform = $device['Platform'];//UserData . Token
		$Token 					= 	$device['Token'];
		$CustomUserData 		= 	$device['UserData'] .' - '.$device['UserId'];
		
		// validate the modification
		$this->validateARN();
		
		$PlatformApplicationArn = 	$applicationARN[$platform];
		$endpointARN 			= 	createEndpointARNAWS($PlatformApplicationArn,$Token,$CustomUserData);
		if($endpointARN == ''){
			 $valueToken1 		= 	ltrim($device['DeviceToken'],'<');
			 $valueToken 		= 	Rtrim( $valueToken1,'>');
			 $tokenExists 		= 	R::findOne('devicetokens','DeviceToken = ?',[$valueToken]);
			 if($tokenExists)
			 {
			 	$endpointARN 	=  	$tokenExists['EndPointARN'];
			 }
		}
		return $endpointARN;
	 }
	 
	/**
    * Check Balance
    */
	public function checkBalance(){
	 	/**
        * Get the bean
        * @var $bean Users
        */
		$bean 		= 	$this->bean;
		
		//validate param
		$this->validatecheckBalanceParams();
		
		$result 	= 	R::findOne('users', 'id = ? ', array($bean->UsersId));
		if($result) {			
			if($bean->PaymentAmount <= $result->CurrentBalance) 
				$AllowPayment 	= 	1;
			else
				$AllowPayment 	= 	0;
				
			$AllowPaymentArray['PaymentAmount'] 	= 	$bean->PaymentAmount;		
			$AllowPaymentArray['CurrentBalance'] 	= 	$result->CurrentBalance;
			$AllowPaymentArray['AllowPayment'] 		= 	$AllowPayment;			
			return $AllowPaymentArray;
		}
		else {
			/**
			* throwing error when no data found
			*/
			throw new ApiException("No users Found", ErrorCodeType::NoResultFound);
		}
	 }
	 
	/**
	* Validate the fields (checkBalance)
	*/
    public function validatecheckBalanceParams()
    {
		$bean 	= 	$this->bean;
	  	$rules 	= 	[
						'required' => [
							 ['PaymentAmount'],['UsersId']
						]
					];
		
        $v 		= 	new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
			// check UsersId and PaymentAmount field
            throw new ApiException("Please check UsersId and PaymentAmount field." ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
	}
	
	/**
    * Validate the fields (Platform)
    * @throws ApiException if the models fails to validate
    */
    public function validateARN()
    {
		
		$rules 	= 	[	
						'required' => [
						   ['Platform']
						],
						'in' =>[
								['Platform',['ios']]
						]
					];
        $v 		= 	new Validator($this);
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
    public function create(){ 
		 
		/**
        * Get the bean
        * @var $bean Users
        */
        $bean 		= 	$this->bean;
		$flag 		= 	$bean->PhotoFlag;
		
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
		
        $bean->DateCreated 			= 	date('Y-m-d H:i:s');
        $bean->DateModified 		= 	$bean->DateCreated;
		
		// encrypt the password
		if($bean->Password){
        	$bean->Password 		= 	PasswordHelper::encrypt($bean->Password);
		}
		
		//save ip address
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
			$ip 	= 	$_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else{
			$ip 	= 	$_SERVER['REMOTE_ADDR'];
		}
		
		$bean->IpAddress 			= 	$ip;
		$bean->Status 				= 	StatusType::ActiveStatus;
		$bean->PushNotification 	= 	1;
		$bean->SendCredit		 	= 	1;
		$bean->RecieveCredit 		= 	1;
		$bean->BuySomething 		= 	1;
		
		// save the bean to the database
		$userId = R::store($this);
		if($userId != ''){
			$numeric       			= 	'1234567890';
			$numbers       			= 	substr(str_shuffle($numeric), 0, 3);
			$uniqueId 	   			= 	'tuplit'.$numbers.$userId;
			$bean->UniqueId			= 	$uniqueId;
			$bean->id				= 	$userId;
			$userId 				= 	R::store($this);
		}
	  	return $userId;
    }
	
    /**
    * Validate the model
    * @throws ApiException if the models fails to validate required fields
    */
    public function validate($type='')
    {
		$bean 		= 	$this->bean;
		if($bean->FBId == '' && $bean->GooglePlusId == '' ){
			$rules 	= 	[
							'required' => [
								 ['FirstName'],['LastName'],['Email'],['PinCode'],['Password']
							],
							'Email' => 'Email',
						];
		}		
		else{
			$rules = 	[
							'required' => [
								['FirstName'],['LastName'],['Email'],['PinCode']
							],
							'Email' => 'Email',
						];
		}
		
        $v 			= 	new Validator($this->bean);
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
        * @var $bean Users
        */
        $bean 					= 	$this->bean;
		
        /**
        * FacebookId must be unique
        */
		if($bean->FBId != ''){
	        $existingAccount 	= 	R::findOne('users', 'FBId = ? and Status <> ? order by DateModified desc', array($bean->FBId,StatusType::DeleteStatus));
	        if ($existingAccount) {
	            // an account with that FacebookId already exists in the system - don't create account
	            throw new ApiException("This facebookId is already associated with another account", ErrorCodeType::FbIdAlreadyExists);
			}
		}
		
		/**
        * GooglePlusId must be unique
        */
		if($bean->GooglePlusId != ''){
	        $existingAccount 	= 	R::findOne('users', 'GooglePlusId = ? and Status <> ? order by DateModified desc', array($bean->GooglePlusId,StatusType::DeleteStatus));
	        if ($existingAccount) {
	            // an account with that GooglePlusId already exists in the system - don't create account
	            throw new ApiException("This GooglePlusId is already associated with another account",ErrorCodeType::GooglePlusIdAlreadyExists);
			}
		}
		
		/**
        * Email Id must be unique
        */
        $existingAccount 		= 	R::findOne('users', 'Email = ? and Status <> ? order by DateModified desc', array($bean->Email,StatusType::DeleteStatus));
        if ($existingAccount) {
            // an account with that email already exists in the system - don't create account
            throw new ApiException("This Email Address is already associated with another account", ErrorCodeType::EmailAlreadyExists);
		}
		
		/**
        * Cell number must be unique
        */
		if($bean->CellNumber != ''){
	        $existingAccount 	= 	R::findOne('users', 'CellNumber = ? and Status <> ? order by DateModified desc', array($bean->PinCode,StatusType::DeleteStatus));
	        if ($existingAccount) {
	            // an account with that pincode already exists in the system - don't create account
	            throw new ApiException("You are not allow to use this CellNumber",ErrorCodeType::CellNumberAlreadyExists);
			}
		}
    }

	
	/**
    * @param Modify the user entity
    */
    public function modify($userId = null,$modify=''){

		/**
        * Get the bean
        * @var $bean Users
        */
		$bean 						= 	$this->bean;
		
		// encrypt the password
		if($modify == 1){
			if($bean->Password && $bean->Password != ''){
	        	$bean->Password 	= 	PasswordHelper::encrypt($bean->Password);
			}
			else if($bean->Password == ''){
				unset($bean->Password);
			}
		}

        $bean->DateModified 		= 	date('Y-m-d H:i:s');
		unset($bean->PhotoFlag);
		
        // modify the bean to the database
        R::store($this);
    }
	

	/**
    * Validate the modification
    */
	public function validateModify($userId)
    {
       	/**
        * Get the bean
        * @var $bean Users
        */
        $bean 					= 	$this->bean;

        /**
        * Get the identity of the person making the change
        * @var $modifiedBy Users
        */
        $modifiedBy 			= 	R::findOne('users', 'id = ? and Status = ?', [$userId,'1']);

        if (!$modifiedBy) {
            // the User was not found
            throw new ApiException("You have no access to edit the datas", ErrorCodeType::NotAccessToDoProcess);
        }
		
        /**
        * FacebookId must be unique
        */
		if($bean->FBId != ''){
	        $existingAccount 	= 	R::findOne('users', 'FBId = ? and id <> ? and Status <> ? order by DateModified desc ', array($bean->FBId,$userId,StatusType::DeleteStatus));
	        if ($existingAccount) {
	            // an account with that FacebookId already exists in the system - don't modify account
	            throw new ApiException("This facebookId is already associated with another account", ErrorCodeType::FbIdAlreadyExists);
			}
		}
	
		/**
        * GooglePlusId must be unique
        */
		if($bean->GooglePlusId != ''){
	        $existingAccount 	= 	R::findOne('users', 'GooglePlusId = ? and id <> ? and Status <> ? order by DateModified desc ', array($bean->GooglePlusId,$userId,StatusType::DeleteStatus));
	        if ($existingAccount) {
	            // an account with that GooglePlusId already exists in the system - don't modify account
	            throw new ApiException("This GooglePlusId is already associated with another account", ErrorCodeType::GooglePlusIdAlreadyExists);
			}
		}
		
		/**
        * Email Id must be unique
        */
		$existingAccount 		= 	R::findOne('users', 'Email = ? and id <> ? and Status <> ? order by DateModified desc', array($bean->Email,$userId,StatusType::DeleteStatus));
	    if ($existingAccount) {
	        // an account with that email already exists in the system - don't modify account
	        throw new ApiException("This Email Address is already associated with another account", ErrorCodeType::EmailAlreadyExists);
		}
		
		/**
        * CellNumber must be unique
        */
		if($bean->CellNumber != ''){
	        $existingAccount 	= 	R::findOne('users', 'CellNumber = ? and id <> ? and Status <> ? order by DateModified desc', array($bean->CellNumber,$userId,StatusType::DeleteStatus));
	        if ($existingAccount) {
	            // an account with that pincode already exists in the system - don't create account
	            throw new ApiException("You are not allow to use this CellNumber",ErrorCodeType::CellNumberAlreadyExists);
			}
		}
    }
	
	/**
    * @param Get user details
    */
    public function getUserDetails($userId){
		/**
        * Get the bean
        * @var $bean Users
        */
		
		$type = 0;
        $bean 					= 	$this->bean;
		 if($bean->Type)
		 	$type	=	$bean->Type;
			//echo"<br>===================>".$type;
		$sql 	= 	"SELECT id as UserId,UniqueId,FirstName,LastName,Email,Photo,CurrentBalance as AvailableBalance,ZipCode,Location,Country,CellNumber  
						FROM users where Status = 1 and id='".$userId."'";
   		$user 	= 	R::getAll($sql);//FBId,GooglePlusId,PushNotification,ZipCode,Location,Country,SendCredit,RecieveCredit,BuySomething 
        if (!$user) {
            // the User was not found
            throw new ApiException("Your status is not in active state", ErrorCodeType::UserNotInActiveStatus);
        }
		else
		{
			$orderArray	=	$commentsArray	 	= 	array();
			$imagePath 							= 	$originalPath = '';
			if($user[0]['Photo'] !=''){
				if(SERVER){
					$imagePath 	  				= 	USER_THUMB_IMAGE_PATH.$user[0]['Photo'];
					$originalPath 				= 	USER_IMAGE_PATH.$user[0]['Photo'];
				}
				else{
					if(file_exists(USER_THUMB_IMAGE_PATH_REL.$user[0]['Photo']))
						$imagePath 				= 	USER_THUMB_IMAGE_PATH.$user[0]['Photo'];
					if(file_exists(USER_IMAGE_PATH_REL.$user[0]['Photo']))
						$originalPath 			= 	USER_IMAGE_PATH.$user[0]['Photo'];
				}
			}
			$user[0]['Photo']		  			= 	$imagePath;
			$user[0]['OriginalPhoto'] 			= 	$originalPath;
			$userDetails['Details'] 			= 	$user[0];
			if($type == 'basic' ){
			//echo "==========".$type;
				$userData['userDetails'] 			= 	$userDetails;
				$userData['userMetaDetails']		= 	'';
				return  $userData;
				exit;
			}
			else{
				$userDetails['Orders']				= array();
				$userDetails['comments'] 			= array();
				$totalOrders	= $totalComments	=	0;
				//Recent Orders
				$orders 							= 	R::dispense('orders');
				$orders->Start 						= 	0;
				$orders->Limit 						= 	3;
				$orderArray 						= 	$orders->getUserOrderDetails($userId);
				if($orderArray){
					$userDetails['Orders'] 			= 	$orderArray['OrderDetails'];
					$totalOrders					=	$orderArray['Total'];
				}
				$userMetaDetails['TotalOrders'] 	= 	$totalOrders;
				//Comments
				$comments 							=	R::dispense('comments');
				$comments->UserId 					= 	$userId;
				$comments->Type 					= 	1;
				$comments->Start 					= 	0;
				$comments->Limit 					= 	3;
				$commentsArray 						= 	$comments->commentsLists();
				if($commentsArray){
					$userDetails['comments'] 		= 	$commentsArray['List'];
					$totalComments					=	$commentsArray['Total'];
				}
				$userMetaDetails['TotalComments'] 	= 	$totalComments;
				$userData['userDetails'] 			= 	$userDetails;
				$userData['userMetaDetails']		= 	$userMetaDetails;
				return  $userData;
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
		$userId 	= 	R::findOne('users', 'id = ? and Status = ?', [$userId,StatusType::ActiveStatus]);
        if (!$userId) {
            // the User was not found
            throw new ApiException("Your status is not in active state", ErrorCodeType::UserNotInActiveStatus);
        }
    }
	
	/**
    * Check Reset Password
    * @param checking reset password 
    */
	public function checkResetPassword($userId,$type=0){	 
			
		$sql 		 	= 	" select id,ResetPassword from users where id = '".$userId."' and Status = 1";
		$userDetails 	= 	R::getAll($sql);
		if($userDetails){
			if($userDetails[0]['ResetPassword'] == 0){
				if($type == 1)
					throw new ApiException("Sorry! You are not allowed to update your password" ,  ErrorCodeType::PasswordResetNotValid);
				else
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
		$sql 		 	 	= 	" select id,FirstName,LastName,ResetPassword,Status,Email from users where email = '".$email."' order by id desc";
		$userDetails 		= 	R::getAll($sql);
		if(!$userDetails) {
            /**
	        * No Email registered with email address
	        */
            throw new ApiException("No user registered with this Email", ErrorCodeType::NoEmailExists);
        }
        else if($userDetails[0]['Status'] != StatusType::ActiveStatus)
        {
            /**
	        * Only active user can request for request forgetpassword for this account  
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
		*/
		$bean 					= 	$this->bean;
		$userId 				= 	$bean->UserId;
		
		//validate param
		$this->validateUsersPassword(2);
		unset($bean->UserId);
		
		//validate password request
		$this->checkResetPassword($userId,1);
		
		// encrypt the password
		$bean->id				= 	$userId;
		$bean->Password			= 	PasswordHelper::encrypt($bean->Password);
		$bean->ResetPassword	= 	0;
		
		// save the bean to the user table
		$usersId 				= 	R::store($this);
		return $usersId;
	}
	
	/**
	* Update Password
	* @param updating password by using word table from database
	*/
	public function forgotPassword(){
		/**
        * Get the bean
        */
		$bean 						=	$this->bean;

		//validate param
		$this->validateUsersPassword(1);

		//validate Email
		$userDetails 				= 	$this->validateForgotEmail($bean->Email);
		if($userDetails){
			$userId		 			= 	$userDetails[0]['id'];
			$bean->id    			= 	$userId;
			$bean->ResetPassword 	= 	1;
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
			$rules	= 	[
							'required' => [
							   ['Email']
							]
						];
			$string = 	"forgot";
		}
		else{
			$rules 	= 	[
							'required' => [
							   ['UserId'],['Password']
							]
						];
			$string = 	"reset";
		}
        $v 			= 	new Validator($this->bean);
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
        * @var $bean Users
        */
		$bean 					= 	$this->bean;
		$settingsAllTypeArray 	= 	array("PushNotification","SendCredit","RecieveCredit","BuySomething","DealsOffers");
		
		//validate param
		$this->validateSettingsParams();
	
		// validate the modification
        $this->validateSettings($userId);
		
		$user 					= 	R::dispense('users');
		$user->id 				= 	$userId;
		if(trim($bean->Type) == 'All'){
			foreach($settingsAllTypeArray as $value){
				$user->$value 	= 	$bean->Action;
			}
		}else{
			$type 				= 	$bean->Type;
			$user->$type 		= 	$bean->Action;
		}
		$userUpdate 			= 	R::store($user);
		return $userUpdate;
	}

	/**
    * @throws ApiException if the Logined user not in active state 
    */
    public function validateSettings($userId)
    {
        /**
        * Get the bean
        * @var $bean Users
        */
        $bean 	= 	$this->bean;
		
		$user 	= 	R::findOne('users', 'id = ? and Status = ?', [$userId,StatusType::ActiveStatus]);
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
		$bean  	= 	$this->bean;
		$rules 	= 	[
						'required' => [
						   ['Type'],['Action']
						],
					];

        $v 		= 	new Validator($this->bean);
        $v->rules($rules);

        if (!$v->validate()) {
            $errors = $v->errors();
            throw new ApiException("Please Action" ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
			
		if($bean->Action != '1' && $bean->Action != '0'){
			// the action was not found
            throw new ApiException("Settings Action is not valid", ErrorCodeType::ErrorInSettingTypeOrSettingAction);
		}
		
		$settingsTypeArray = array("PushNotification","SendCredit","RecieveCredit","BuySomething","DealsOffers","Sounds","Passcode","PaymentPreference","RememberMe");
		if(!in_array($bean->Type,$settingsTypeArray)){
			// the action was not found
            throw new ApiException("Settings Type is not valid", ErrorCodeType::ErrorInSettingTypeOrSettingAction);
		}
    }

	/**
    * @param Post user device token saving process
	*/
    public function addTokens($post){

		if(isset($post['DeviceToken']) && trim($post['DeviceToken']) !='' && isset($post['EndPointARN']) && trim($post['EndPointARN']) =='')
		{
			$valueToken1 			= 	ltrim($post['DeviceToken'],'<');
			$valueToken 			= 	Rtrim( $valueToken1,'>');
			$tokenExists 			= 	R::findOne('devicetokens','Token = ?',[$valueToken]);
			if($tokenExists){
				$post['EndPointARN']=  	$tokenExists['EndPointARN'];
			}
		} 
		if(isset($post['DeviceToken']) && trim($post['DeviceToken']) !='' && isset($post['EndPointARN']) && trim($post['EndPointARN']) !='')
		{
			if($post['Platform'] == 'android')
				$platform 			= 	2;
			else
				$platform 			= 	1;

			$valueToken1 			= 	ltrim($post['DeviceToken'],'<');
			$valueToken 			= 	Rtrim( $valueToken1,'>');
			$token 					= 	R::dispense('devicetokens');
			$token->LoginedDate 	= 	date('Y-m-d H:i:s');
			$token->fkUserId 		= 	$post['UserId'];
			$token->Token 			= 	$valueToken;
			$token->EndPointARN 	= 	$post['EndPointARN'];
			$token->Platform 		= 	$platform;
			$token->Status 			= 	1;
			
			$sql 					= 	"update devicetokens set Status = 0 where Token = '".$valueToken."'";
			R::exec($sql);
			
			$tokenExists 			= 	R::findOne('devicetokens','Token = ? and fkUserId = ?',[$valueToken, $post['UserId']]);
			if($tokenExists)
				$token->id 			=  	$tokenExists['id'];
			
			R::store($token);
		}
	}
	
	/**
    * @param Modify the user Pincode
    */
    public function setNewPin(){

		/**
        * Get the bean
        * @var $bean Users
        */
		$bean 					= 	$this->bean;
		
		// validate the model
        $this->validatesetNewPinParams();

        $bean->DateModified 	= 	date('Y-m-d H:i:s');
				
        // modify the bean to the database
        $userUpdate 			= 	R::store($this);
		if($userUpdate)
			return $userUpdate;
		else {
			/**
	        * throwing error when pincode update fails
	        */
			throw new ApiException("Error occurred while updating pincode.", ErrorCodeType::UpdatePinError);
		}
    }
	
	/**
    * Validate the fields (setNewPin)
    */
    public function validatesetNewPinParams()
    {
		$bean 	= 	$this->bean;
	  	$rules 	= 	[
						'required' => [
							 ['PinCode']
						]
					];
		
        $v 		= 	new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
			// the action was not found
            throw new ApiException("Please check PinCode field." ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
		if(!is_numeric($bean->PinCode) || strlen($bean->PinCode) != 4) {
            throw new ApiException("Pincode must be numeric and length should be 4." ,  ErrorCodeType::SetPinValidateError);
		}
	}
	
	/**
    * pincode verification
    */
	public function verifyPin(){
	 	/**
        * Get the bean
        * @var $bean Users
        */
		$bean 		= 	$this->bean;
		
		//validate param
		$this->validateVerifyPinParams();
		
		//Getting users original Pincode
		$result 	=	R::findOne('users', 'id = ? ', array($bean->UsersId));
		if($result) {
			//verifying weather the VerificationPin matches with original PinCode
			if($bean->PinCode == $result->PinCode) 
				$PinVerify 	= 	1;
			else
				$PinVerify 	= 	0;
						
			$PinVerifyArray['PinVerify'] 	= 	$PinVerify;			
			return $PinVerifyArray;
		}
		else {
			/**
	        * throwing error when no data found
	        */
			throw new ApiException("No users Found", ErrorCodeType::NoResultFound);
		}
	 }
	 
	/**
    * Validate the fields of pincode verification
    */
    public function validateVerifyPinParams()
    {
		$bean 		= 	$this->bean;
	  	$rules 		= 	[
							'required' => [
								 ['PinCode']
							]
						];
		
        $v 			= 	new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
			// the action was not found
            throw new ApiException("Please check VerificationPin field." ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
	}
	
	/**
    * @param Get users list
    */
	public function getUserList($MerchantId)
    {	
		/**
        * Get the bean
        * @var $bean Users
        */
		$bean 			= 	$this->bean;
		
		//validate param
		$this->validategetUserListParams();
		$result1 		= 	R::findOne('admins', 'id = ? ', array('1'));
		$distanceLimit 	= 	$result1->LocationLimit;
		$limit			= 	'';
		global $userLoadMore;
		if(isset($bean->Start)) {
			$limit		=	" limit ".$bean->Start.",$userLoadMore";
			unset($bean->Start);
		}
		
		if(isset($bean->Search) && !empty($bean->Search))
			$condition 	= 	" and (u.FirstName like '%".$bean->Search."%' || u.LastName like '%".$bean->Search."%' || u.Email like '%".$bean->Search."%') ";
		else
			$condition	= 	'';
		
		$sql 	= "SELECT SQL_CALC_FOUND_ROWS DISTINCT u.id, u.FirstName, u.LastName, u.Photo,u.CurrentBalance,
					(((acos(sin((".$bean->Latitude."*pi()/180)) * sin((u.`Latitude`*pi()/180))+cos((".$bean->Latitude."*pi()/180)) * cos((u.`Latitude`*pi()/180)) * cos(((".$bean->Longitude."- u.`Longitude`)*pi()/180))))*180/pi())*60*1.1515) as distance
					FROM  users u 
					where 1".$condition." 
					and (((acos(sin((".$bean->Latitude."*pi()/180)) * sin((u.`Latitude`*pi()/180))+cos((".$bean->Latitude."*pi()/180)) * cos((u.`Latitude`*pi()/180)) * cos(((".$bean->Longitude."- u.`Longitude`)*pi()/180))))*180/pi())*60*1.1515) <= $distanceLimit
					and Status = 1 order by u.FirstName asc ".$limit;
		$user 			= 	R::getAll($sql);		
		$user1			=	R::getAll('SELECT FOUND_ROWS() as count ');
		$usertotal		=	$user1[0]['count'];
	    
		if (!$user) {
            // the User was not found
            throw new ApiException("No users Found", ErrorCodeType::NoResultFound);
        }
		else
		{
			$userSelectArray 			= 	array();
			foreach($user as $key=>$val) { 
				$imagePath 				= 	'';
				if($user[$key]['Photo'] !=''){
					if(SERVER)
						$imagePath 		= 	USER_THUMB_IMAGE_PATH.$user[$key]['Photo'];
					else{
						if(file_exists(USER_THUMB_IMAGE_PATH_REL.$user[$key]['Photo']))
							$imagePath 	= 	USER_THUMB_IMAGE_PATH.$user[$key]['Photo'];
						else
							$imagePath 	= 	MERCHANT_SITE_IMAGE_PATH.'no_user.jpeg';
					}
				}
				else
					$imagePath 			= 	MERCHANT_SITE_IMAGE_PATH.'no_user.jpeg';
				$user[$key]['Photo'] 	= 	$imagePath;
				$userSelectArray[]	 	= 	$user[$key];
			}
			$userDetails['TotalCount']	= 	$usertotal;
			$userDetails['result']		= 	$userSelectArray;
			return $userDetails;
		}
	}
	
	/**
    * Validate the fields of getUserList
    */
    public function validategetUserListParams()
    {
		$bean 	= 	$this->bean;
	  	$rules 	= 	[
						'required' => [
							['Latitude'],['Longitude']
						]
					];
		
        $v 		= 	new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
			// the action was not found
            throw new ApiException("Please check Latitude or Longitude field." ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
	}
	
	/**
    * Transfer amount between users
    */
    public function transferAmount(){

		/**
        * Get the bean
        */
		$bean 										= $this->bean;
		
		// validate the model
        $this->validateTransferAmountParams();
		
		//getTransferAmounts		
        $newBalance									=	$this->getTransferAmounts();
		if($newBalance) {
			
			//Storing transfer amount
			$transfer								= 	R::dispense('transfer');
			$transfer->fkUsersId					=	$bean->UserId;
			$transfer->fkTransferUsersId			=	$bean->ToUserId;
			$transfer->Amount						=	$bean->Amount;
			
			$transfer->Notes						=	$bean->Notes;
			$transfer->TransferDate					=	date('Y-m-d H:i:s');
			$transferId								=	R::store($transfer);
			if($transferId) {
			
				//updating from user current balance
				$fromsql = "update users set CurrentBalance = ".$newBalance['from']." where id = '".$bean->UserId."'";
				R::exec($fromsql);
				
				//updating to user current balance
				$tosql = "update users set CurrentBalance = ".$newBalance['to']." where id = '".$bean->ToUserId."'";
				R::exec($tosql);			
				$notification 						= new Notification();
				$notification->userId 				= $bean->UserId;
				$notification->toUserId 			= $bean->ToUserId;
				$notification->Amount	 			= $bean->Amount;
				$notification->sendNotification(1);
				return $transferId;
			}			
		}		
    }
	
	/**
    * Validate the fields of transferAmount
    */
    public function validateTransferAmountParams()
    {
		$bean 		= 	$this->bean;
	  	$rules 		= 	[
							'required' => [
								['ToUserId'],['Amount']
							]
						];
		
        $v 			= 	new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
			// the action was not found
            throw new ApiException("Please check transfer amount fields." ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
	}
	
	/**
    * Validate the transferAmount
    */
    public function getTransferAmounts()
    {
		$bean 		= 	$this->bean;
		$result 	= 	R::findOne('users', 'id = ? and Status=1', array($bean->ToUserId));
		if(!$result) {
			// the User was not found
            throw new ApiException("The user you are going to transfer amount was not found.", ErrorCodeType::NoResultFound);
		}
		 else {
			$result1 = R::findOne('users', 'id = ? and Status=1', array($bean->UserId));
			if(!$result1) {
				// the User was not found
				throw new ApiException("you are not in active state to transfer amount.", ErrorCodeType::NoResultFound);
			}
			else {
				if($result1->CurrentBalance < $bean->Amount) {
					// low balance
					throw new ApiException("You are not having enough balance to transfer the amount.", ErrorCodeType::CheckBalanceError);
				} else {
					$Newbalance['from']		=	$result1->CurrentBalance - $bean->Amount;
					$Newbalance['to']		=	$result->CurrentBalance + $bean->Amount;
					return $Newbalance;
				}
			}
		}
	}
	/*
	* Add MangoPay Details
	*/
	public function addMangoPayDetails($userId)
    {
		$bean 			=	 $this->bean;
		$usersArray 	= array();
		
		//Validate MangoPay params
		$this->validateMangoPay();
		
		$usersArray['FirstName']			=	$bean->FirstName;
		$usersArray['LastName']				=	$bean->LastName;
		$usersArray['Email']				=	$bean->Email;
		$usersArray['Address']				=	$bean->Address;
		$usersArray['Nationality']			=	$bean->Nationality;
		$usersArray['CountryOfResidence']	=	$bean->Country;
		$usersArray['Occupation']			=	$bean->Occupation;
		$usersArray['Currency']				=	$bean->Currency;
		$usersArray['Birthday']				=	$bean->Birthday;
		$usersArray['IncomeRange']				=	$bean->IncomeRange;
		
		$mangopayDetails 					=   userRegister($usersArray);
		if(isset($mangopayDetails) && count($mangopayDetails) >0){
			$uniqueId					=	$mangopayDetails->Id;
			$walletId					=	createWallet($uniqueId,$usersArray['Currency']);
			$users 						= 	R::dispense('users');
			$users->id 					= 	$userId;
			$users->MangoPayResponse 	= 	serialize($mangopayDetails);
			$users->MangoPayUniqueId 	= 	$uniqueId;
			$users->WalletId 			= 	$walletId;
			$usersUpdate 				= 	R::store($users);
			return $userId;
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
                ['FirstName'],['LastName'],['Email'],['Address'], ['Nationality'],['Country'],['Occupation'],['Currency'],['Birthday'],['IncomeRange']
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
