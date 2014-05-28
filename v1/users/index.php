<?php

/**
 * Users endpoint
 * /v1/users
 *
 * @author 
 */

/**
 * Load configuration
 */
require_once('../../config.php');

/**
 * Load models
 */
require_once '../../lib/ModelBaseInterface.php';            // base interface class for RedBean models
require_once '../../lib/Model_Users.php';                 	// user model

require_once "../../admin/includes/CommonFunctions.php";
require_once "../../admin/includes/phmagick.php";

/**
 * Library objects
 */
use RedBean_Facade as R;
use Helpers\RedBeanHelper as RedBeanHelper;
use Helpers\PasswordHelper as PasswordHelper;
use Enumerations\HttpStatusCode as HttpStatusCode;
use Exceptions\ApiException as ApiException;
use Enumerations\AccountType as AccountType;
use Enumerations\StatusType as StatusType;
use Enumerations\ErrorCodeType as ErrorCodeType;
/**
 * Initialize application
 */
tuplitApi::init(true);
$app = new \Slim\Slim();

/**
 * New user creation
 * POST /v1/users
 */
$app->post('/', function () use ($app) {

    try {

        // Create a http request
        $req = $app->request();

        /**
         * Get a new user account
         * @var Model_Users $user
         */
        $user = R::dispense('users');
       // $user->UserName 		= $req->params('UserName');
		$user->FirstName 		= $req->params('FirstName');
		$user->LastName 		= $req->params('LastName');
		$user->Email 			= $req->params('Email');
		$user->Password 		= $req->params('Password');
		if($req->params('CellNumber'))
			$user->CellNumber 		= $req->params('CellNumber');
		
		if($req->params('FBId'))
			$user->FBId = $req->params('FBId');
		if($req->params('GooglePlusId'))
			$user->GooglePlusId = $email = $req->params('GooglePlusId');
		if($req->params('PinCode'))
			$user->PinCode = $req->params('PinCode');
		if($req->params('ZipCode'))
			$user->ZipCode = $req->params('ZipCode');
		if($req->params('Country'))
			$user->Country = $req->params('Country');
		if($req->params('Platform')){
			$platformText = $req->params('Platform');
			if($platformText == 'ios')
				$platform = 1;
			else if($platformText == 'android')
				$platform = 2;
			else
				$platform = 0;
		}
		else{
			$platform = 0;
		}
		$user->Platform = $platform;
		$flag = $coverFlag = 0;
		if (isset($_FILES['Photo']['tmp_name']) && $_FILES['Photo']['tmp_name'] != '') {
			$flag = checkImage($_FILES['Photo'],1);
		}
		
	    $user->PhotoFlag 			= $flag;
	    /**
         * Create the account
         */
	    $userId = $user->create();
		/**
         * Saving user Photo
         */
		 if($userId){
				$user->Photo = '';
				if (isset($_FILES['Photo']['tmp_name']) && $_FILES['Photo']['tmp_name'] != '') {
					$imageName 				= $userId . '_' . time() . '.png';
					$imageOriginalPath 		= UPLOAD_USER_PATH_REL.$imageName;
					$imageThumbPath 		= UPLOAD_USER_THUMB_PATH_REL.$imageName;
					copy($_FILES['Photo']['tmp_name'],$imageOriginalPath);
					
					$phMagick = new phMagick($imageOriginalPath);
					$phMagick->setDestination($imageThumbPath)->resize(100,100);
					
					if(SERVER){
						uploadImageToS3($imageOriginalPath,1,$imageName);
						uploadImageToS3($imageThumbPath,2,$imageName);
						unlink($imageOriginalPath);
						unlink($imageThumbPath);
					}
					$user->Photo = $imageName;
					unlink($_FILES['Photo']['tmp_name']);
					$user->PhotoFlag = 5;//success
					$user->modify($userId);
				}
		 }
		/**
         * After successful registration email was sent to registered user
         */
		if($req->params('Email') && (!$req->params('FBId')) && (!$req->params('GooglePlusId')) ){
			$adminDetails 						=   R::findOne('admins', 'id=?', ['1']);
			$adminMail							=	$adminDetails->EmailAddress;
			$mailContentArray['fileName']		=	'registration.html';
			$mailContentArray['from']			=	$adminMail;
			$mailContentArray['toemail']		= 	$req->params('Email');
			$mailContentArray['subject']		= 	"Registration";
			$mailContentArray['email']	    	=	$req->params('Email');
			$mailContentArray['name']			=	ucfirst($req->params('FirstName').' '.$req->params('LastName'));
			$mailContentArray['password']		=	$req->params('Password');
			sendMail($mailContentArray,2); 
		}
        /**
         * New user creation was made success
         */
        $response = new tuplitApiResponse();
        $response->setStatus(HttpStatusCode::Created);
        $response->meta->dataPropertyName = 'user';
		/**
        * returning upon repsonse of new user creation
		*/
        $userDetails = RedBeanHelper::export($user->unbox(),['IpAddress','Status','DateModified','DateCreated','ActualPassword','Password','PhotoFlag','UniqueId','PinCode']);
		
		$userDetails['UserId'] = $userDetails['id'];
		if($userDetails['Photo'] !=''){
			$userDetails['Photo'] = USER_THUMB_IMAGE_PATH.$userDetails['Photo'];
		}
		$response->returnedObject = $userDetails;
        $response->addNotification('User has been created successfully');
        echo $response;

    }
    catch (ApiException $e){
        // If occurs any error message then goes here
        tuplitApi::showError(
            $e,
            $e->getHttpStatusCode(),
            $e->getErrors()
        );
    }
    catch (\Slim\Exception\Stop $e){
        // If occurs any error message for slim framework then goes here
    }
    catch (Exception $e) {
        // If occurs any error message then goes here
        tuplitApi::showError($e);
    }
});

/**
 * CheckReset password
 * POST /v1/users/checkResetPassword/:userId
 */
$app->get('/checkResetPassword/:userId', function ($userId) use ($app) {

    try {
		// Create a http request
        $req = $app->request();
		
		// Create a json response object
       $response = new tuplitApiResponse();
		/**
         * Get a user table instance
         */
		$users = new Model_Users();
		/**
         * Call check reset password function
         */
		$userDetails = $users->checkResetPassword($userId);
		/**
         * Send mail to registered user
         */
		 if($userDetails){
			$response->setStatus(HttpStatusCode::Created);
        	$response->meta->dataPropertyName = 'user';
			$response->addNotification('You are allowed to reset password.');
			/*$content	=	array("status"	    =>	"Success",
						  	 	  "message"  	=>	"You are allowed to reset password");
			$response->returnedObject = $content;*/
			echo $response;
		}
		else{
			throw new ApiException("Sorry! You cannot use this link again" ,  ErrorCodeType::PasswordResetNotValid);
		}
    }
    catch (ApiException $e){
        // If occurs any error message then goes here
        tuplitApi::showError(
            $e,
            $e->getHttpStatusCode(),
            $e->getErrors()
        );
    }
    catch (\Slim\Exception\Stop $e){
        // If occurs any error message for slim framework then goes here
    }
    catch (Exception $e) {
        // If occurs any error message then goes here
        tuplitApi::showError($e);
    }

});

/**
 * Forgot password
 * POST /v1/users/password
 */
$app->post('/resetPassword', function () use ($app) {

    try {
		// Create a http request
        $req = $app->request();
		
		// Create a json response object
       $response = new tuplitApiResponse();
		/**
         * Get a merchant table instance
         */
        $users = R::dispense('users');
		$users->UserId		= $req->params('UserId');
		$users->Password 	= $req->params('Password');
		$userId		 		= $users->updatePassword();
		/**
         * Send mail to registered merchant
         */
		if($userId != ''){
     		$response->setStatus(HttpStatusCode::Created);
      		$response->meta->dataPropertyName = 'users';
			$response->addNotification('Password updated successfully.');
			/*$content	=	array("status"	    =>	"Success",
						  	 	  "message"  	=>	"Password updated successfully.");
			$response->returnedObject = $content;*/
			echo $response;
		}
		else{
			// Error occured while reseting password
			throw new ApiException("Error in reseting password" ,  ErrorCodeType::ErrorInUpdateForgetPassword);
		}
		

    }
    catch (ApiException $e){
        // If occurs any error message then goes here
        tuplitApi::showError(
            $e,
            $e->getHttpStatusCode(),
            $e->getErrors()
        );
    }
    catch (\Slim\Exception\Stop $e){
        // If occurs any error message for slim framework then goes here
    }
    catch (Exception $e) {
        // If occurs any error message then goes here
        tuplitApi::showError($e);
    }

});
/**
 * Forgot password
 * POST /v1/users/forgotpassword
 */
$app->post('/forgetPassword', function () use ($app) {

    try {
		// Create a http request
        $req = $app->request();
		
		// Create a json response object
       $response = new tuplitApiResponse();
		/**
         * Get a merchant table instance
         */
        $users = R::dispense('users');
		
		$users->Email = $req->params('Email');
		$usersDetails = $users->forgotPassword();
		
		/**
         * Send mail to registered merchant
         */
		 if($usersDetails){
		 	$usersDetails = $usersDetails[0];
			$adminDetails 					=   R::findOne('admins', 'id=?', ['1']);
			$adminMail						=	$adminDetails->EmailAddress;
			$mailContentArray['fileName']	=	'userForgotPasswordMail.html';
			$mailContentArray['from']		=	$adminMail;
			$mailContentArray['toemail']	= 	trim($usersDetails['Email']);
			$mailContentArray['subject']	= 	"Forgot Password";
			$mailContentArray['name']		=	ucfirst($usersDetails['FirstName'].' '.$usersDetails['LastName']);
			$mailContentArray['link']		=	'/ResetPassword.php?UID='.encode($usersDetails['id']).'&Type=1';
			sendMail($mailContentArray,1); //Send mail - Updated password Details
     		
			$content	=	array("status"	    =>	"Success",
						  	 	  "message"  	=>	"An email has been sent to you with link to reset your password.");
			$response->returnedObject = $content;
			$response->setStatus(HttpStatusCode::Created);
        	$response->meta->dataPropertyName = 'users';
       		echo $response;
		}
		else{
			// Error occured while reseting password
			throw new ApiException("Error in updating password" ,  ErrorCodeType::ErrorInUpdateForgetPassword);
		}
    }
    catch (ApiException $e){
        // If occurs any error message then goes here
        tuplitApi::showError(
            $e,
            $e->getHttpStatusCode(),
            $e->getErrors()
        );
    }
    catch (\Slim\Exception\Stop $e){
        // If occurs any error message for slim framework then goes here
    }
    catch (Exception $e) {
        // If occurs any error message then goes here
        tuplitApi::showError($e);
    }

});


/**
 * Get user Details
 * GET /v1/users
 */
$app->get('/',tuplitApi::checkToken(), function () use ($app) {

    try {
		// Create a http request
        $req = $app->request();
		$requestedById = tuplitApi::$resourceServer->getOwnerId();
		$ownerType = tuplitApi::$resourceServer->getOwnerType();
        /**
         * Get a user account details
         * @var Model_Users $user
         */
        $user = R::dispense('users');
		$details = array();
		
        $userDetails 	= $user->getUserDetails($requestedById);	
		if(  $userDetails ){
	        $response = new tuplitApiResponse();
	        $response->setStatus(HttpStatusCode::Ok);
	        $response->meta->dataPropertyName = 'userDetails';
	
			/**
	        * returning upon repsonse of user details 
			*/
			
			$response->returnedObject = $userDetails;
	        $response->addNotification('User details has been retrieved successfully');
	        echo $response;
		}
		else {
			/** 
			* Some error has occurred while getting user details
			*/
			throw new ApiException("User not found", ErrorCodeType::NoResultFound);
		}

    }
    catch (ApiException $e){
        // If occurs any error message then goes here
        tuplitApi::showError(
            $e,
            $e->getHttpStatusCode(),
            $e->getErrors()
        );
    }
    catch (\Slim\Exception\Stop $e){
        // If occurs any error message for slim framework then goes here
    }
    catch (Exception $e) {
        // If occurs any error message then goes here
        tuplitApi::showError($e);
    }
});

/**
 * Edit User Details
 * PUT /v1/users
 */
$app->put('/',tuplitApi::checkToken(), function () use ($app) {

    try {

        // Create a http request
        $request = $app->request();
    	$body = $request->getBody();
		
    	$input = json_decode($body); 
		$requestedById = $userId = tuplitApi::$resourceServer->getOwnerId();
        /**
         * Get a new user account
         * @var Model_Users $user
         */
        $user = R::dispense('users');
        $user->id = $requestedById;
		
		$platform = 0;
		if(isset($input->Platform)){
			$platformText = $input->Platform;
			if($platformText == 'ios')
				$platform = 1;
			else
				$platform = 2;
		}
		else{
			$platformText = 'web';
		}
		
		//if(isset($input->UserName)) 	$user->UserName 		= $input->UserName;
		if(isset($input->FirstName)) 	$user->FirstName 		= $input->FirstName;
		if(isset($input->LastName)) 	$user->LastName 		= $input->LastName;
		if(isset($input->Email)) 		$user->Email 			= $input->Email;
		if(isset($input->Password)) 	$user->Password 		= $input->Password;
		//if(isset($input->FBId)) 		$user->FBId 			= $input->FBId;
		//if(isset($input->TwitterId)) 	$user->TwitterId 		= $input->TwitterId;
		//if(isset($input->GooglePlusId)) $user->GooglePlusId 	= $input->GooglePlusId;
		if(isset($input->Gender)) 		$user->Gender 			= $input->Gender;
		if(isset($input->AgeRange)) 	$user->AgeRange 		= $input->AgeRange;
		if(isset($input->ZipCode)) 		$user->ZipCode 			= $input->ZipCode;
		if(isset($input->Country)) 		$user->Country 			= $input->Country;
		if(isset($input->AgeRange)) 	$user->AgeRange 		= $input->AgeRange;
		if(isset($input->PassCode)) 	$user->PassCode 		= $input->PassCode;
		
		$userListResult  = R::getAll("select Photo from users where id = ".$requestedById);
		if(isset($input->Photo) && $input->Photo !=''){
			
			$image_base64 = $input->Photo;
			$decode_img = base64_decode($image_base64);
			$typecheck = getImageMimeType($decode_img);
				if($typecheck != ''){
					$img = imagecreatefromstring($decode_img);
					if($img != false)
				    {
				    	$imageName = $userId . '_' . time() . '.png';
						$imageOriginalPath 		= UPLOAD_USER_PATH_REL.$imageName;
						$imageThumbPath 		= UPLOAD_USER_THUMB_PATH_REL.$imageName;
					
						imagepng($img, $imageOriginalPath);
						
						$phMagick = new phMagick($imageOriginalPath);
						$phMagick->setDestination($imageThumbPath)->resize(100,100);
						
						$userImage = $userListResult[0]['Photo'];	
						if(!SERVER){
							if($userImage != ''){
								$imageOriginalPath 		= UPLOAD_USER_PATH_REL.$userImage;
								$imageThumbPath 		= UPLOAD_USER_THUMB_PATH_REL.$userImage;
								if(file_exists($imageThumbPath))
									unlink($imageThumbPath);
								if(file_exists($imageOriginalPath))
									unlink($imageOriginalPath);
							}
						}
						
						if(SERVER){
							deleteImages(1,$userImage);
							deleteImages(2,$userImage);
							
							uploadImageToS3($imageThumbPath,2,$imageName);
							uploadImageToS3($imageOriginalPath,1,$imageName);
							
							unlink($imageThumbPath);
							unlink($imageOriginalPath);
						}
						$user->Photo = $imageName;
				    }
			   }
			   else{
			   		/**
			        * Error in photo creation
					*/
					throw new ApiException("Please check the user's properties (Photo)" ,ErrorCodeType::ProblemInImage);
			   }
		}
       
		$user->modify($userId,1);
        /**
         * New user creation was made success
         */
        $response = new tuplitApiResponse();
        $response->setStatus(HttpStatusCode::Created);
        $response->meta->dataPropertyName = 'user';
		/**
        * returning upon repsonse 
		*/
        $response->addNotification('User Details has been updated successfully');
        echo $response;

    }
    catch (ApiException $e){
        // If occurs any error message then goes here
        tuplitApi::showError(
            $e,
            $e->getHttpStatusCode(),
            $e->getErrors()
        );
    }
    catch (\Slim\Exception\Stop $e){
        // If occurs any error message for slim framework then goes here
    }
    catch (Exception $e) {
        // If occurs any error message then goes here
        tuplitApi::showError($e);
    }
});
/**
 * Start the Slim Application
 */

$app->run();